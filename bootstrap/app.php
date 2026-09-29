<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | CSRF
        |--------------------------------------------------------------------------
        |
        | Mercado Pago envía las notificaciones desde sus servidores,
        | por lo que no dispone de un token CSRF de Laravel.
        |
        | La autenticidad del webhook se valida mediante x-signature.
        |
        */

        $middleware->validateCsrfTokens(
            except: [
                'webhooks/mercadopago',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Middleware aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([

            'company' =>
                \App\Http\Middleware\EnsureCompanySelected::class,

            'financial.day' =>
                \App\Http\Middleware\EnsureFinancialDayOpen::class,

            'platform.admin' =>
                \App\Http\Middleware\PlatformAdmin::class,

            'company.access' =>
                \App\Http\Middleware\EnsureCompanyHasAccess::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) =>
                $request->is('api/*')
                || $request->expectsJson(),
        );

    })
    ->create();