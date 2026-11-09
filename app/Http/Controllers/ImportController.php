<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function facebook(\Illuminate\Http\Request $request)
    {
        return $this->launch('facebook', $request->boolean('full'));
    }

    public function messenger(\Illuminate\Http\Request $request)
    {
        return $this->launch('messenger', $request->boolean('full'));
    }

    public function status(string $type)
    {
        if (!in_array($type, ['facebook', 'messenger'])) {
            return response()->json(['error' => 'Type invalide'], 400);
        }

        $progress = Cache::get("{$type}_progress");
        $running = Cache::has("{$type}_running");
        $startedAt = Cache::get("{$type}_started_at");
        $finishedAt = Cache::get("{$type}_last_finished_at");

        // Dernier record en BDD pour "temps reel"
        $lastRecord = null;
        if ($type === 'facebook') {
            $lastRecord = \App\Models\Commentaire::max('created_at')
                ?? \App\Models\Post::max('created_at');
        } else {
            $lastRecord = \App\Models\MessengerMessage::max('created_at')
                ?? \App\Models\MessengerConversation::max('updated_at');
        }

        return response()->json([
            'running' => $running,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'progress' => $progress,
            'last_record_at' => $lastRecord,
        ]);
    }

    /**
     * Indicateur discret pour la page : "Mis a jour il y a X sec"
     */
    public function freshness(string $type)
    {
        if (!in_array($type, ['facebook', 'messenger'])) {
            return response()->json(['error' => 'Type invalide'], 400);
        }

        $finishedAt = Cache::get("{$type}_last_finished_at");
        $running = Cache::has("{$type}_running");

        // Compter les nouveaux records depuis la derniere visite de l'utilisateur
        $since = request()->get('since');
        $newCount = 0;
        if ($since) {
            try {
                $sinceDate = \Carbon\Carbon::parse($since);
                if ($type === 'facebook') {
                    $newCount = \App\Models\Commentaire::where('created_at', '>', $sinceDate)->count();
                } else {
                    $newCount = \App\Models\MessengerMessage::where('created_at', '>', $sinceDate)->count();
                }
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'running' => $running,
            'last_sync' => $finishedAt,
            'new_count' => $newCount,
            'now' => now()->toDateTimeString(),
        ]);
    }

    protected function launch(string $type, bool $full = false)
    {
        Log::channel('single')->info("[MANUAL-IMPORT] Demande {$type} (full=" . ($full ? 'oui' : 'non') . ")");

        // Reset ancienne progression
        Cache::forget("{$type}_progress");
        Cache::forget("{$type}_last_finished_at");

        // Marquer le debut
        Cache::put("{$type}_running", true, 3600);
        Cache::put("{$type}_started_at", now()->toDateTimeString(), 3600);

        // Init progress vide pour eviter les false-positives
        Cache::put("{$type}_progress", [
            'total' => 0,
            'current' => 0,
            'current_message' => 'Initialisation...',
            'finished' => false,
        ], 3600);

        // Renvoyer la reponse au client AVANT de lancer l'import
        // pour que le navigateur ne reste pas en attente
        $response = response()->json([
            'success' => true,
            'message' => "Import {$type} démarré ! Cela peut prendre quelques minutes.",
        ]);

        // Envoyer la reponse maintenant
        $response->send();

        // Si fastcgi est dispo, fermer la connexion proprement
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // Pas de timeout, ignorer la deconnexion du client
        @set_time_limit(0);
        @ignore_user_abort(true);

        try {
            $options = $full ? [] : ['--incremental' => true];
            Log::channel('single')->info("[MANUAL-IMPORT] Lancement artisan import:{$type}");
            Artisan::call("import:{$type}", $options);
            Log::channel('single')->info("[MANUAL-IMPORT] Termine {$type}: " . trim(Artisan::output()));
        } catch (\Throwable $e) {
            Log::channel('single')->error("[MANUAL-IMPORT] Erreur {$type}: " . $e->getMessage());
        }

        Cache::forget("{$type}_running");
        Cache::put("{$type}_last_finished_at", now()->toDateTimeString(), 86400);

        // On a deja envoye la reponse, on retourne juste pour forme
        return $response;
    }
}
