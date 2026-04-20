<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AutoImportFacebook
{
    /**
     * Lance l'import Facebook dans un processus separe
     * sans bloquer la requete de l'utilisateur.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $cacheKey = 'facebook_last_auto_import';

        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, now(), 300); // cooldown 5 minutes
            Log::channel('single')->info('[AUTO-IMPORT] Declenchement import Facebook en arriere-plan');

            // Lancer en arriere-plan sans bloquer la requete
            $php = env('PHP_BINARY_PATH', PHP_BINARY ?: 'php');
            $artisan = base_path('artisan');

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows : start /B lance en arriere-plan
                pclose(popen("start /B \"\" \"{$php}\" \"{$artisan}\" import:facebook 2>&1", 'r'));
            } else {
                // Linux/Mac : & + nohup en arriere-plan
                exec("\"{$php}\" \"{$artisan}\" import:facebook > /dev/null 2>&1 &");
            }
        }

        return $response;
    }
}
