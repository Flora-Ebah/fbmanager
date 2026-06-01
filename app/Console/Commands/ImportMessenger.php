<?php

namespace App\Console\Commands;

use App\Models\MessengerConversation;
use App\Models\MessengerMessage;
use App\Services\FacebookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMessenger extends Command
{
    protected $signature = 'import:messenger
                            {--fresh : Vider les tables messenger avant import}
                            {--limit=100 : Nombre max de conversations a recuperer}';

    protected $description = 'Importer les conversations et messages Messenger depuis Facebook';

    public function handle()
    {
        $fb = new FacebookService();
        $limit = (int) $this->option('limit');

        if ($this->option('fresh')) {
            $this->warn('Vidage des tables messenger...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            MessengerMessage::truncate();
            MessengerConversation::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info("Recuperation des conversations (max {$limit})...");
        $conversations = $fb->getConversations($limit);

        if (empty($conversations)) {
            $this->warn('Aucune conversation recuperee.');
            return 1;
        }

        $this->info(count($conversations) . ' conversations recuperees.');

        $convosCreated = 0;
        $convosUpdated = 0;
        $messagesCreated = 0;
        $messagesTotal = 0;
        $errors = 0;
        $totalConvos = count($conversations);
        $currentIndex = 0;

        $bar = $this->output->createProgressBar($totalConvos);
        $bar->start();

        Cache::put('messenger_progress', [
            'total' => $totalConvos,
            'current' => 0,
            'convos_created' => 0,
            'convos_updated' => 0,
            'messages_total' => 0,
            'messages_created' => 0,
            'errors' => 0,
            'current_message' => 'Démarrage...',
            'finished' => false,
        ], 3600);

        foreach ($conversations as $conv) {
            $currentIndex++;
            $convId = $conv['id'] ?? null;
            if (!$convId) {
                $bar->advance();
                continue;
            }

            // Determiner le nom de l'expediteur (le participant qui n'est pas la page)
            $pageId = config('fbmanager.facebook_page_id');
            $nomExpediteur = 'Utilisateur Facebook';
            if (!empty($conv['participants']['data'])) {
                foreach ($conv['participants']['data'] as $p) {
                    if (($p['id'] ?? '') !== $pageId) {
                        $nomExpediteur = $p['name'] ?? 'Utilisateur Facebook';
                        break;
                    }
                }
            }

            $convo = MessengerConversation::updateOrCreate(
                ['conversation_id' => $convId],
                [
                    'nom_expediteur' => $nomExpediteur,
                    'dernier_message' => $conv['snippet'] ?? null,
                    'temps_dernier_message' => $conv['updated_time'] ?? null,
                    'nombre_messages' => $conv['message_count'] ?? 0,
                    'statut' => ($conv['unread_count'] ?? 0) > 0 ? 'non_lu' : 'lu',
                ]
            );

            if ($convo->wasRecentlyCreated) {
                $convosCreated++;
            } else {
                $convosUpdated++;
            }

            // Recuperer les messages
            $messages = $fb->getConversationMessages($convId);
            $messagesTotal += count($messages);

            foreach ($messages as $msg) {
                $msgId = $msg['id'] ?? null;
                if (!$msgId) continue;

                $fromName = $msg['from']['name'] ?? $nomExpediteur;
                $fromId = $msg['from']['id'] ?? null;
                $statut = ($fromId === $pageId) ? 'lu' : 'non_lu';

                $message = MessengerMessage::updateOrCreate(
                    ['fb_message_id' => $msgId],
                    [
                        'conversation_id' => $convId,
                        'nom_expediteur' => $fromName,
                        'message' => $msg['message'] ?? '',
                        'temps_envoi' => $msg['created_time'] ?? null,
                        'type_message' => 'text',
                        'statut' => $statut,
                    ]
                );

                if ($message->wasRecentlyCreated) {
                    $messagesCreated++;
                }
            }

            Cache::put('messenger_progress', [
                'total' => $totalConvos,
                'current' => $currentIndex,
                'convos_created' => $convosCreated,
                'convos_updated' => $convosUpdated,
                'messages_total' => $messagesTotal,
                'messages_created' => $messagesCreated,
                'errors' => $errors,
                'current_message' => "Conversation {$currentIndex}/{$totalConvos} traitée",
                'finished' => false,
            ], 3600);

            $bar->advance();
        }

        Cache::put('messenger_progress', [
            'total' => $totalConvos,
            'current' => $totalConvos,
            'convos_created' => $convosCreated,
            'convos_updated' => $convosUpdated,
            'messages_total' => $messagesTotal,
            'messages_created' => $messagesCreated,
            'errors' => $errors,
            'current_message' => 'Import terminé !',
            'finished' => true,
        ], 3600);

        $bar->finish();
        $this->newLine(2);

        $this->info('Import Messenger termine !');
        $this->table(
            ['Donnee', 'Nombre'],
            [
                ['Conversations recuperees', count($conversations)],
                ['Nouvelles conversations', $convosCreated],
                ['Conversations mises a jour', $convosUpdated],
                ['Messages recuperes', $messagesTotal],
                ['Nouveaux messages', $messagesCreated],
                ['Total conversations en BDD', MessengerConversation::count()],
                ['Total messages en BDD', MessengerMessage::count()],
            ]
        );

        Log::channel('single')->info("[IMPORT-MESSENGER] {$convosCreated} nouvelles convos, {$messagesCreated} nouveaux messages");

        return 0;
    }
}
