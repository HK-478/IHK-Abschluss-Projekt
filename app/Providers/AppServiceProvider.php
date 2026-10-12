<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Contracts\StatusServiceInterface::class, \App\Services\StatusService::class);
        $this->app->bind(\App\Contracts\IdentityProviderInterface::class, \App\Services\IdentityProviderAdapter::class);
        // ... andere Interfaces
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

}
