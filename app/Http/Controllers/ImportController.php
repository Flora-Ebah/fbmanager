<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function facebook()
    {
        return $this->launch('facebook');
    }

    public function messenger()
    {
        return $this->launch('messenger');
    }

    protected function launch(string $type)
    {
        Log::channel('single')->info("[MANUAL-IMPORT] Demande {$type}");

        // Marquer le debut
        Cache::put("{$type}_running", true, 3600);
        Cache::put("{$type}_started_at", now()->toDateTimeString(), 3600);

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
            Log::channel('single')->info("[MANUAL-IMPORT] Lancement artisan import:{$type}");
            Artisan::call("import:{$type}");
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
