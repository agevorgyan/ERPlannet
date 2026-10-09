<?php

namespace App\Infrastructure\Payments\DTOs;

readonly class PaymentResultDTO
{
    public function __construct(
        public string $status, // 'pending', 'redirect', 'successful', 'failed'
        public ?string $transactionId,
        public ?string $redirectUrl = null,
        public array $gatewayResponse = [],
        public ?string $errorMessage = null
    ) {}

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    public function requiresRedirect(): bool
    {
        return $this->status === 'redirect' && ! empty($this->redirectUrl);
    }
}
