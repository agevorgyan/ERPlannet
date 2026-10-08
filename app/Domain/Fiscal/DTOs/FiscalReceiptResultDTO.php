<?php

namespace App\Domain\Fiscal\DTOs;

class FiscalReceiptResultDTO
{
    public function __construct(
        public bool $success,
        public ?string $fiscalReceiptId,
        public ?string $fiscalNumber,
        public ?string $crn,
        public ?string $taxId,
        public ?string $qrPayload,
        public string $status, // registered, cancelled, refunded, failed
        public array $rawResponse = [],
        public ?string $errorMessage = null
    ) {}
}
