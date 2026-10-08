<?php

namespace App\Infrastructure\Payments\DTOs;

readonly class PaymentIntentDTO
{
    public function __construct(
        public string $tenantId,
        public ?string $invoiceId,
        public float $amount,
        public string $currency,
        public string $description,
        public string $returnUrl,
        public string $cancelUrl,
        public array $customerMetadata = []
    ) {}
}
