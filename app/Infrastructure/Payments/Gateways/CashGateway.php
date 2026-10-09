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

    public function createPayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        return $this->initiatePayment($intent);
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $receiptId = 'CASH-'.strtoupper(Str::random(8));

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

    public function authorize(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $receiptId = 'CASH_HOLD_'.strtoupper(Str::random(8));

        return new PaymentResultDTO(
            status: 'authorized',
            transactionId: $receiptId,
            gatewayResponse: ['receipt_id' => $receiptId, 'amount' => $intent->amount]
        );
    }

    public function capture(string $transactionId, float $amount, array $options = []): PaymentResultDTO
    {
        return new PaymentResultDTO(
            status: 'successful',
            transactionId: $transactionId,
            gatewayResponse: ['captured_amount' => $amount, 'captured_at' => now()->toIso8601String()]
        );
    }

    public function cancel(string $transactionId, array $options = []): PaymentResultDTO
    {
        return new PaymentResultDTO(
            status: 'cancelled',
            transactionId: $transactionId,
            gatewayResponse: ['cancelled_at' => now()->toIso8601String()]
        );
    }

    public function getStatus(string $transactionId): PaymentResultDTO
    {
        return new PaymentResultDTO(
            status: 'successful',
            transactionId: $transactionId,
            gatewayResponse: ['transaction_id' => $transactionId, 'gateway' => 'cash', 'status' => 'successful']
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
            refundId: 'CASH_REF_'.strtoupper(Str::random(8)),
            gatewayResponse: ['refunded_amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookResultDTO
    {
        return new WebhookResultDTO(
            verified: true,
            transactionId: $payload['receipt_id'] ?? $payload['transaction_id'] ?? null,
            status: 'successful',
            amount: isset($payload['amount']) ? (float) $payload['amount'] : null,
            currency: $payload['currency'] ?? 'AMD',
            rawPayload: $payload
        );
    }
}
