<?php

namespace App\Providers;

use App\Support\TenantManager;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register TenantManager as a singleton so the same instance
        // is shared across all parts of the application for one request.
        $this->app->singleton(TenantManager::class, function ($app) {
            return new TenantManager();
        });

        // Short alias: app('tenant') === app(TenantManager::class)
        $this->app->alias(TenantManager::class, 'tenant');
    }

    public function boot(): void
    {
        //
    }
}
