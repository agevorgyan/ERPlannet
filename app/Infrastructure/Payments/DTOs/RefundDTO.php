<?php

namespace App\Infrastructure\Payments\DTOs;

readonly class RefundDTO
{
    public function __construct(
        public string $transactionId,
        public float $amount,
        public string $currency,
        public ?string $reason = null
    ) {}
}
