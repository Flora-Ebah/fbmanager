<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Commentaire;
use App\Models\MessengerConversation;
use App\Models\MessengerMessage;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportGoogleSheets extends Command
{
    protected $signature = 'import:sheets {--fresh : Vider les tables avant import}';
    protected $description = 'Importer les donnees depuis Google Sheets (Posts, Commentaires, Messenger)';

    public function handle()
    {
        $credentialsPath = base_path(config('fbmanager.google_credentials_path'));
        $spreadsheetId = config('fbmanager.spreadsheet_id');

        if (!file_exists($credentialsPath)) {
            $this->error("Fichier credentials introuvable : {$credentialsPath}");
            return 1;
        }

        $this->info('Connexion a Google Sheets...');

        $client = new Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Sheets::SPREADSHEETS_READONLY);

        $service = new Sheets($client);

        // Option --fresh : vider les tables
        if ($this->option('fresh')) {
            $this->warn('Vidage des tables...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            MessengerMessage::truncate();
            MessengerConversation::truncate();
            Commentaire::truncate();
            Post::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // ─── Import Feuille 1 : Posts & Commentaires (A2:I) ───
        $this->info('Import des Posts & Commentaires (Feuille 1)...');
        try {
            // Recuperer le nom de la premiere feuille
            $spreadsheet = $service->spreadsheets->get($spreadsheetId);
            $allSheets = $spreadsheet->getSheets();
            $firstSheetName = $allSheets[0]->getProperties()->getTitle();
            $this->info("  -> Lecture de la feuille : {$firstSheetName}");

            $response = $service->spreadsheets_values->get($spreadsheetId, "{$firstSheetName}!A2:I");
            $rows = $response->getValues() ?? [];

            $postsCount = 0;
            $commentsCount = 0;

            foreach ($rows as $row) {
                $postId = $row[0] ?? null;
                $messagePost = $row[1] ?? null;
                $imageUrl = $row[2] ?? null;
                $tempsCreerPost = $row[3] ?? null;
                $lien = $row[4] ?? null;
                $idCommentaire = $row[5] ?? null;
                $messageCommentaire = $row[6] ?? null;
                $nomAuteur = $row[7] ?? null;
                $tempsCreer = $row[8] ?? null;

                if (!$postId) continue;

                // Upsert post
                $post = Post::updateOrCreate(
                    ['post_id' => $postId],
                    [
                        'message_post' => $messagePost,
                        'image_url' => $imageUrl,
                        'temps_creer_post' => $tempsCreerPost,
                        'lien' => $lien,
                    ]
                );

                if ($post->wasRecentlyCreated) {
                    $postsCount++;
                }

                // Upsert commentaire si present
                if ($idCommentaire) {
                    $comment = Commentaire::updateOrCreate(
                        ['id_commentaire' => $idCommentaire],
                        [
                            'post_id' => $postId,
                            'message_commentaire' => $messageCommentaire,
                            'nom_auteur' => $nomAuteur,
                            'temps_creer' => $tempsCreer,
                        ]
                    );

                    if ($comment->wasRecentlyCreated) {
                        $commentsCount++;
                    }
                }
            }

            $this->info("  -> {$postsCount} posts importes, {$commentsCount} commentaires importes");
            $this->info("  -> Total lignes lues : " . count($rows));
        } catch (\Exception $e) {
            $this->error('Erreur Feuille 1 : ' . $e->getMessage());
        }

        // ─── Import Feuille 3 : Messenger (index 2, colonnes A2:F) ───
        $this->info('Import des Messages Messenger (Feuille 3)...');
        try {
            // On recupere le nom de la 3eme feuille
            $spreadsheet = $service->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheet->getSheets();
            $sheetName = isset($sheets[2]) ? $sheets[2]->getProperties()->getTitle() : null;

            if (!$sheetName) {
                $this->warn('  -> Feuille 3 introuvable');
                // Lister les feuilles disponibles
                foreach ($sheets as $i => $s) {
                    $this->line("    Feuille {$i} : " . $s->getProperties()->getTitle());
                }
                // Tenter la feuille 2 (index 1) si pas de 3
                $sheetName = isset($sheets[1]) ? $sheets[1]->getProperties()->getTitle() : 'Feuil3';
                $this->info("  -> Tentative avec : {$sheetName}");
            }

            $this->info("  -> Lecture de la feuille : {$sheetName}");

            $response = $service->spreadsheets_values->get($spreadsheetId, "{$sheetName}!A2:F");
            $rows = $response->getValues() ?? [];

            $convosCount = 0;
            $msgsCount = 0;

            foreach ($rows as $row) {
                $conversationId = $row[0] ?? null;
                $nomExpediteur = $row[1] ?? null;
                $message = $row[2] ?? null;
                $tempsEnvoi = $row[3] ?? null;
                $typeMessage = $row[4] ?? 'text';
                $statut = $row[5] ?? 'non_lu';

                if (!$conversationId || !$message) continue;

                // Upsert conversation
                $convo = MessengerConversation::updateOrCreate(
                    ['conversation_id' => $conversationId],
                    [
                        'nom_expediteur' => $nomExpediteur,
                        'dernier_message' => $message,
                        'temps_dernier_message' => $tempsEnvoi,
                        'statut' => in_array($statut, ['lu', 'non_lu']) ? $statut : 'non_lu',
                    ]
                );

                if ($convo->wasRecentlyCreated) {
                    $convosCount++;
                }

                // Creer le message
                MessengerMessage::updateOrCreate(
                    [
                        'conversation_id' => $conversationId,
                        'nom_expediteur' => $nomExpediteur,
                        'temps_envoi' => $tempsEnvoi,
                    ],
                    [
                        'message' => $message,
                        'type_message' => $typeMessage ?: 'text',
                        'statut' => in_array($statut, ['lu', 'non_lu']) ? $statut : 'non_lu',
                    ]
                );

                $msgsCount++;
            }

            // Mettre a jour le nombre de messages par conversation
            $conversations = MessengerConversation::all();
            foreach ($conversations as $convo) {
                $lastMsg = $convo->messages()->orderBy('temps_envoi', 'desc')->first();
                $convo->update([
                    'nombre_messages' => $convo->messages()->count(),
                    'dernier_message' => $lastMsg?->message,
                    'temps_dernier_message' => $lastMsg?->temps_envoi,
                    'nom_expediteur' => $lastMsg?->nom_expediteur ?? $convo->nom_expediteur,
                ]);
            }

            $this->info("  -> {$convosCount} conversations, {$msgsCount} messages importes");
            $this->info("  -> Total lignes lues : " . count($rows));
        } catch (\Exception $e) {
            $this->error('Erreur Feuille 3 : ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('Import termine !');
        $this->table(
            ['Table', 'Total'],
            [
                ['posts', Post::count()],
                ['commentaires', Commentaire::count()],
                ['messenger_conversations', MessengerConversation::count()],
                ['messenger_messages', MessengerMessage::count()],
            ]
        );

        return 0;
    }
}
