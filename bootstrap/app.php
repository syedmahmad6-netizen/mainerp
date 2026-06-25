<?php

use App\Http\Middleware\IdentifyTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(base_path('routes/portals.php'));
    },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register IdentifyTenant as a named middleware alias
        // Use 'tenant' in route groups: Route::middleware(['tenant'])->group(...)
        $middleware->alias([
    'tenant'      => \App\Http\Middleware\IdentifyTenant::class,
    'portal.role' => \App\Http\Middleware\EnsurePortalRole::class,
]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
