<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to posts
Route::get('/', fn () => redirect('/posts'));

// Privacy policy (public, required for Facebook App Review)
Route::get('/privacy', fn () => view('privacy'))->name('privacy');

// External cron trigger (cron-job.org, uptimerobot, etc.)
// Usage: GET /cron/run?token=XXX
Route::get('/cron/run', function (\Illuminate\Http\Request $request) {
    $expectedToken = env('CRON_TOKEN', '95d9c9022831d9d05458bc0bbe4343ce');
    if ($request->get('token') !== $expectedToken) {
        return response('Unauthorized', 401);
    }

    // Fermer la connexion au client tout de suite, lancer la synchro en fond
    $response = response()->json(['ok' => true, 'at' => now()->toDateTimeString()]);
    $response->send();
    if (function_exists('fastcgi_finish_request')) { fastcgi_finish_request(); }
    @set_time_limit(0);
    @ignore_user_abort(true);

    try {
        \Illuminate\Support\Facades\Artisan::call('import:facebook', ['--incremental' => true]);
        \Illuminate\Support\Facades\Artisan::call('import:messenger', ['--incremental' => true]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('[CRON-HTTP] ' . $e->getMessage());
    }

    return $response;
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes (sync gere par cron, plus besoin du middleware AutoImportFacebook)
Route::middleware(['auth'])->group(function () {
    // Posts & Comments
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{postId}', [PostController::class, 'show'])->name('posts.show');

    // AI - Generation de reponses
    Route::post('/ai/suggest-reply', [AiController::class, 'suggestReply'])->name('ai.suggest');
    Route::post('/ai/suggest-multiple', [AiController::class, 'suggestMultipleReplies'])->name('ai.suggest-multiple');
    Route::post('/ai/suggest-messenger', [AiController::class, 'suggestMessengerReply'])->name('ai.suggest-messenger');

    // Imports manuels (trigger en arriere-plan)
    Route::post('/import/facebook', [ImportController::class, 'facebook'])->name('import.facebook');
    Route::post('/import/messenger', [ImportController::class, 'messenger'])->name('import.messenger');
    Route::get('/import/status/{type}', [ImportController::class, 'status'])->name('import.status');
    Route::get('/import/freshness/{type}', [ImportController::class, 'freshness'])->name('import.freshness');

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
