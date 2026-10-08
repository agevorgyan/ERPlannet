<?php

declare(strict_types=1);

namespace App\Domain\Integration\Services;

use App\Domain\Integration\Contracts\IntegrationDriverInterface;
use App\Domain\Integration\Drivers\ArmenianSoftwareExportDriver;
use App\Domain\Integration\Drivers\NikitaSmsDriver;
use App\Domain\Integration\Drivers\TelegramBotDriver;
use App\Domain\Integration\Drivers\WooCommerceDriver;
use App\Domain\Integration\Models\TenantIntegration;

class IntegrationManager
{
    /**
     * Map provider identifiers to their driver implementation classes.
     *
     * @var array<string, class-string<IntegrationDriverInterface>>
     */
    protected array $drivers = [
        'woocommerce' => WooCommerceDriver::class,
        'telegram' => TelegramBotDriver::class,
        'nikita_sms' => NikitaSmsDriver::class,
        'mobipace_sms' => NikitaSmsDriver::class, // Shares same SMS contract
        'armenian_software' => ArmenianSoftwareExportDriver::class,
    ];

    /**
     * Resolve the driver instance for a given tenant integration.
     */
    public function driverFor(TenantIntegration $integration): IntegrationDriverInterface
    {
        $provider = $integration->provider;

        if (!isset($this->drivers[$provider])) {
            throw new \InvalidArgumentException("Unsupported integration provider: [{$provider}]");
        }

        $driverClass = $this->drivers[$provider];
        return app($driverClass);
    }
}
