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
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Cloudflare (and any reverse proxy) so Laravel detects HTTPS correctly.
        // Without this, Livewire generates http:// URLs → Mixed Content error.
        $middleware->trustProxies(at: '*');

        // Initialize tenancy BEFORE StartSession so sessions read/write the correct
        // tenant DB. Using prepend puts it at the front of the web middleware group.
        // InitializeTenancyByDomain is a no-op on central domains (procurethai.uk).
        $middleware->web(prepend: [
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
