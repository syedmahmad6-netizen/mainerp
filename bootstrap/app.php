<?php

use App\Http\Middleware\EnsurePortalRole;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Route::middleware('web')->group(base_path('routes/portals.php'));
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant'      => IdentifyTenant::class,
            'portal.role' => EnsurePortalRole::class,
        ]);

        // This line is the fix — it makes sure every request
        // (including the background "create/save" requests)
        // knows which school it belongs to.
        $middleware->web(append: [
            IdentifyTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();