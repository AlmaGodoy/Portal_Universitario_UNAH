<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// ✅ IMPORTA TU MIDDLEWARE
use App\Http\Middleware\RoleIdMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Alias para usarlo en rutas como: ->middleware('roleid:2')
        $middleware->alias([
            'roleid' => RoleIdMiddleware::class,
        ]);

        // ✅ Tu configuración CSRF (igual que la tenías)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
