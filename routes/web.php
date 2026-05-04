<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
