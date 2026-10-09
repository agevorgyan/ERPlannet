<?php

namespace App\Providers;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Billing\Services\EntitlementManager;
use App\Infrastructure\MultiTenancy\TenantContext;
use App\Infrastructure\Payments\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(PaymentGatewayManager::class);

        $this->app->bind(
            EntitlementManagerInterface::class,
            EntitlementManager::class
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
