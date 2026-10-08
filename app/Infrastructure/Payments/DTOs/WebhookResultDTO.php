<?php

namespace App\Infrastructure\Payments\DTOs;

readonly class WebhookResultDTO
{
    public function __construct(
        public bool $verified,
        public ?string $transactionId,
        public string $status, // 'successful', 'failed', 'refunded', 'ignored'
        public ?float $amount = null,
        public ?string $currency = null,
        public array $rawPayload = [],
        public ?string $errorMessage = null
    ) {}
}
