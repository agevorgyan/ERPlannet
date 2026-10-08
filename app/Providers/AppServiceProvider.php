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
        $this->app->singleton(\App\Infrastructure\MultiTenancy\TenantContext::class);
        $this->app->singleton(\App\Infrastructure\Payments\PaymentGatewayManager::class);

        $this->app->bind(
            \App\Domain\Billing\Contracts\EntitlementManagerInterface::class,
            \App\Domain\Billing\Services\EntitlementManager::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
