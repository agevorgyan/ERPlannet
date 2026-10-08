<?php

declare(strict_types=1);

namespace App\Domain\Integration\Contracts;

use App\Domain\Integration\Models\TenantIntegration;

interface NotificationProviderInterface extends IntegrationDriverInterface
{
    /**
     * Send a notification message through the channel (SMS, Telegram, Email).
     *
     * @param array<string, mixed> $options
     * @return array{success: bool, message_id?: string, error?: string}
     */
    public function sendNotification(TenantIntegration $integration, string $recipient, string $message, array $options = []): array;
}
