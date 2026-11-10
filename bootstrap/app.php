<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Filet de sécurité: déclenche la sync Facebook/Messenger en arrière-plan
        // à chaque visite si la dernière date de > 60 sec (en cas de panne cron)
        $middleware->appendToGroup('web', \App\Http\Middleware\TriggerBackgroundSync::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
