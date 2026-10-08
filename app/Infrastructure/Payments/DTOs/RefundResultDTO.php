<?php

namespace App\Infrastructure\Payments\DTOs;

readonly class RefundResultDTO
{
    public function __construct(
        public bool $success,
        public ?string $refundId,
        public array $gatewayResponse = [],
        public ?string $errorMessage = null
    ) {}
}
