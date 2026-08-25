<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CORS must run before everything else (including auth)
        $middleware->prepend(\App\Http\Middleware\CorsMiddleware::class);

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->alias([
            'auth.token' => \App\Http\Middleware\AuthenticateToken::class,
            'admin' => \App\Http\Middleware\RequireAdmin::class,
            'system_admin' => \App\Http\Middleware\RequireSystemAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
