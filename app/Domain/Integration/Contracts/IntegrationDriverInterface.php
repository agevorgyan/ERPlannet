<?php

declare(strict_types=1);

namespace App\Domain\Integration\Contracts;

use App\Domain\Integration\Models\TenantIntegration;

interface IntegrationDriverInterface
{
    /**
     * Test connection and credentials validity with the third-party provider.
     *
     * @return array{success: bool, message: string, details?: array}
     */
    public function testConnection(TenantIntegration $integration): array;

    /**
     * Get the provider identifier (e.g. 'woocommerce', 'telegram', 'nikita_sms').
     */
    public function getProviderName(): string;
}
