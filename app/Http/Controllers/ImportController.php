<?php

namespace App\Http\Controllers;

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
        $php = env('PHP_BINARY_PATH', PHP_BINARY ?: 'php');
        $artisan = base_path('artisan');
        $command = "{$php} {$artisan} import:{$type}";

        Log::channel('single')->info("[MANUAL-IMPORT] Tentative {$type} | php={$php} | exec=" . (function_exists('exec') ? 'oui' : 'non'));

        $launched = false;

        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                if (function_exists('popen')) {
                    pclose(popen("start /B \"\" \"{$php}\" \"{$artisan}\" import:{$type} 2>&1", 'r'));
                    $launched = true;
                }
            } else {
                if (function_exists('exec')) {
                    $output = [];
                    $returnCode = 0;
                    exec("nohup {$command} > /dev/null 2>&1 &", $output, $returnCode);
                    $launched = $returnCode === 0;
                    Log::channel('single')->info("[MANUAL-IMPORT] exec retour={$returnCode}");
                }
                if (!$launched && function_exists('popen')) {
                    $h = popen("nohup {$command} > /dev/null 2>&1 &", 'r');
                    if ($h) { pclose($h); $launched = true; }
                }
                if (!$launched && function_exists('shell_exec')) {
                    shell_exec("nohup {$command} > /dev/null 2>&1 &");
                    $launched = true;
                }
            }
        } catch (\Throwable $e) {
            Log::channel('single')->error("[MANUAL-IMPORT] Exception: " . $e->getMessage());
        }

        Cache::put("{$type}_last_manual_import", now(), 60);
        Log::channel('single')->info("[MANUAL-IMPORT] {$type} launched=" . ($launched ? 'oui' : 'NON'));

        return response()->json([
            'success' => $launched,
            'message' => $launched ? "Import {$type} lancé en arrière-plan" : "Échec lancement (consultez les logs)",
        ]);
    }
}
