<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PastikanFitur;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'tenant' => ResolveTenant::class,
            'fitur' => PastikanFitur::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Rute sinkronisasi offline PWA hidup di grup web (sesi + CSRF) tapi
        // dipanggil oleh fetch — perlu error JSON (401/422), bukan redirect,
        // agar syncEngine bisa membedakan konflik stok (422) dari kegagalan lain.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->is('kasir/transaksi/sync'),
        );
    })->create();
