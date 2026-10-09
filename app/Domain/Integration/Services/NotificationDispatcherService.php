<?php

declare(strict_types=1);

namespace App\Domain\Integration\Services;

use App\Domain\Integration\Contracts\NotificationProviderInterface;
use App\Domain\Integration\Models\TenantIntegration;
use App\Domain\Integration\Models\TenantNotificationTemplate;

class NotificationDispatcherService
{
    public function __construct(
        protected IntegrationManager $integrationManager
    ) {}

    /**
     * Send an event notification using tenant template and active provider.
     *
     * @param  array<string, string|int|float>  $variables
     * @return array{success: bool, channel?: string, message?: string, error?: string}
     */
    public function send(string $tenantId, string $event, string $recipient, array $variables = [], ?string $channel = null): array
    {
        $templateQuery = TenantNotificationTemplate::where('tenant_id', $tenantId)
            ->where('event', $event)
            ->where('is_active', true);

        if ($channel) {
            $templateQuery->where('channel', $channel);
        }

        $template = $templateQuery->first();
        $targetChannel = $template?->channel ?? $channel ?? 'sms';
        $message = $template ? $template->render($variables) : ($variables['message'] ?? "Notification for event: {$event}");

        // Find active integration for this channel
        $providerName = match ($targetChannel) {
            'telegram' => 'telegram',
            'sms' => 'nikita_sms',
            default => 'nikita_sms',
        };

        $integration = TenantIntegration::where('tenant_id', $tenantId)
            ->where('provider', $providerName)
            ->where('status', 'active')
            ->first();

        if (! $integration) {
            return [
                'success' => false,
                'error' => "No active integration configured for notification channel [{$targetChannel}].",
            ];
        }

        /** @var NotificationProviderInterface $driver */
        $driver = $this->integrationManager->driverFor($integration);
        $result = $driver->sendNotification($integration, $recipient, $message);

        return [
            'success' => $result['success'] ?? false,
            'channel' => $targetChannel,
            'message' => $message,
            'error' => $result['error'] ?? null,
        ];
    }
}
