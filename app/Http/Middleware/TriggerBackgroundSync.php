<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Déclenche une synchro Facebook/Messenger en arrière-plan à chaque visite
 * si le dernier passage date de plus de 60 secondes.
 *
 * Fonctionne en complément du cron hosting.fr : si le cron est en panne,
 * dès qu'un utilisateur ouvre une page, la synchro se lance.
 *
 * - Zéro impact perceptible pour l'utilisateur (fastcgi_finish_request).
 * - Ne s'exécute pas pour les requêtes AJAX/polling (évite les boucles).
 */
class TriggerBackgroundSync
{
    protected int $cooldownSeconds = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip pour AJAX, API, endpoints de live-updates/freshness/status
        if (
            $request->ajax() ||
            $request->wantsJson() ||
            $request->is('import/*') ||
            $request->is('*/live-list') ||
            $request->is('*/live-messages') ||
            $request->is('*/live-comments') ||
            $request->is('ai/*') ||
            $request->is('cron/*')
        ) {
            return $response;
        }

        // Cooldown: ne déclencher que si plus de 60s depuis le dernier
        if (Cache::has('visit_sync_cooldown')) {
            return $response;
        }

        Cache::put('visit_sync_cooldown', true, $this->cooldownSeconds);

        // Déclencher la sync APRÈS avoir envoyé la réponse HTML au client
        $response->send();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        @set_time_limit(0);
        @ignore_user_abort(true);

        try {
            Artisan::call('import:facebook', ['--incremental' => true]);
            Artisan::call('import:messenger', ['--incremental' => true]);
        } catch (\Throwable $e) {
            Log::warning('[VISIT-SYNC] ' . $e->getMessage());
        }

        return $response;
    }
}
