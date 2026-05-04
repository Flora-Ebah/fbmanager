<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

function launchImport(string $type) {
    $php = env('PHP_BINARY_PATH', PHP_BINARY ?: 'php');
    $artisan = base_path('artisan');
    $command = "{$php} {$artisan} import:{$type}";

    Log::channel('single')->info("[MANUAL-IMPORT] Tentative {$type} | php={$php} | execEnabled=" . (function_exists('exec') ? 'oui' : 'non') . " | popenEnabled=" . (function_exists('popen') ? 'oui' : 'non'));

    $launched = false;

    try {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (function_exists('popen')) {
                pclose(popen("start /B \"\" \"{$php}\" \"{$artisan}\" import:{$type} 2>&1", 'r'));
                $launched = true;
            }
        } else {
            // Try several approaches
            if (function_exists('exec')) {
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

// Redirect root to posts
Route::get('/', fn () => redirect('/posts'));

// Privacy policy (public, required for Facebook App Review)
Route::get('/privacy', fn () => view('privacy'))->name('privacy');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth', \App\Http\Middleware\AutoImportFacebook::class])->group(function () {
    // Posts & Comments
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{postId}', [PostController::class, 'show'])->name('posts.show');

    // AI - Generation de reponses
    Route::post('/ai/suggest-reply', [AiController::class, 'suggestReply'])->name('ai.suggest');
    Route::post('/ai/suggest-multiple', [AiController::class, 'suggestMultipleReplies'])->name('ai.suggest-multiple');
    Route::post('/ai/suggest-messenger', [AiController::class, 'suggestMessengerReply'])->name('ai.suggest-messenger');

    // Imports manuels (trigger en arriere-plan)
    Route::post('/import/facebook', function () {
        return launchImport('facebook');
    })->name('import.facebook');

    Route::post('/import/messenger', function () {
        return launchImport('messenger');
    })->name('import.messenger');

    // Messenger
    Route::get('/messenger', [MessengerController::class, 'index'])->name('messenger.index');
    Route::get('/messenger/{conversationId}', [MessengerController::class, 'show'])->name('messenger.show');

    // Admin: Users management
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
