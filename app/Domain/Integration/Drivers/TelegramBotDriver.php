<?php

declare(strict_types=1);

namespace App\Domain\Integration\Drivers;

use App\Domain\Integration\Contracts\NotificationProviderInterface;
use App\Domain\Integration\Models\TenantIntegration;
use Illuminate\Support\Facades\Http;

class TelegramBotDriver implements NotificationProviderInterface
{
    public function getProviderName(): string
    {
        return 'telegram';
    }

    public function testConnection(TenantIntegration $integration): array
    {
        $creds = $integration->getCredentials();
        $token = $creds['bot_token'] ?? '';

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Missing Telegram bot token.',
            ];
        }

        try {
            $res = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");

            if ($res->successful() && ($res->json()['ok'] ?? false)) {
                $user = $res->json()['result'] ?? [];

                return [
                    'success' => true,
                    'message' => 'Telegram Bot connected successfully: @'.($user['username'] ?? 'bot'),
                    'details' => $user,
                ];
            }

            return [
                'success' => false,
                'message' => 'Telegram API error: '.($res->json()['description'] ?? 'Unauthorized'),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Telegram connection failed: '.$e->getMessage(),
            ];
        }
    }

    public function sendNotification(TenantIntegration $integration, string $recipient, string $message, array $options = []): array
    {
        $creds = $integration->getCredentials();
        $token = $creds['bot_token'] ?? '';
        $defaultChatId = $creds['default_chat_id'] ?? null;
        $chatId = ! empty($recipient) ? $recipient : $defaultChatId;

        if (empty($token) || empty($chatId)) {
            return [
                'success' => false,
                'error' => 'Missing bot token or destination chat ID.',
            ];
        }

        try {
            $res = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $options['parse_mode'] ?? 'HTML',
            ]);

            if ($res->successful() && ($res->json()['ok'] ?? false)) {
                $msgId = (string) ($res->json()['result']['message_id'] ?? '');

                return [
                    'success' => true,
                    'message_id' => $msgId,
                ];
            }

            return [
                'success' => false,
                'error' => $res->json()['description'] ?? 'Failed to send Telegram message',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
