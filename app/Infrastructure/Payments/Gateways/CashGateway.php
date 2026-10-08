<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class CashGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'cash';
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $receiptId = 'CASH-' . strtoupper(Str::random(8));

        return new PaymentResultDTO(
            status: 'successful',
            transactionId: $receiptId,
            gatewayResponse: [
                'receipt_id' => $receiptId,
                'method' => 'cash',
                'amount' => $intent->amount,
                'currency' => $intent->currency,
            ]
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        return new PaymentResultDTO(
            status: 'successful',
            transactionId: $transactionId,
            gatewayResponse: $payload
        );
    }

    public function refund(RefundDTO $dto): RefundResultDTO
    {
        return new RefundResultDTO(
            success: true,
            refundId: 'CASH_REF_' . strtoupper(Str::random(8)),
            gatewayResponse: ['refunded_amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload): WebhookResultDTO
    {
        return new WebhookResultDTO(
            verified: true,
            transactionId: $payload['receipt_id'] ?? null,
            status: 'successful',
            amount: isset($payload['amount']) ? (float) $payload['amount'] : null,
            currency: $payload['currency'] ?? 'AMD',
            rawPayload: $payload
        );
    }
}
