<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class BankTransferGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'bank_transfer';
    }

    public function createPayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        return $this->initiatePayment($intent);
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $refNumber = 'BT-'.strtoupper(Str::random(8));

        return new PaymentResultDTO(
            status: 'pending',
            transactionId: $refNumber,
            gatewayResponse: [
                'reference_number' => $refNumber,
                'instructions' => 'Please transfer the payment to the specified bank account and mention the reference number.',
                'amount' => $intent->amount,
                'currency' => $intent->currency,
            ]
        );
    }

    public function authorize(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $refNumber = 'BT-AUTH-'.strtoupper(Str::random(8));

        return new PaymentResultDTO(
            status: 'authorized',
            transactionId: $refNumber,
            gatewayResponse: ['reference_number' => $refNumber, 'amount' => $intent->amount]
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
            status: 'pending',
            transactionId: $transactionId,
            gatewayResponse: ['transaction_id' => $transactionId, 'gateway' => 'bank_transfer', 'status' => 'pending']
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        $confirmed = $payload['confirmed'] ?? false;

        return new PaymentResultDTO(
            status: $confirmed ? 'successful' : 'pending',
            transactionId: $transactionId,
            gatewayResponse: $payload
        );
    }

    public function refund(RefundDTO $dto): RefundResultDTO
    {
        return new RefundResultDTO(
            success: true,
            refundId: 'BT_REF_'.strtoupper(Str::random(8)),
            gatewayResponse: ['refunded_amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookResultDTO
    {
        return new WebhookResultDTO(
            verified: true,
            transactionId: $payload['reference_number'] ?? $payload['transaction_id'] ?? null,
            status: ($payload['status'] ?? '') === 'confirmed' ? 'successful' : 'pending',
            amount: isset($payload['amount']) ? (float) $payload['amount'] : null,
            currency: $payload['currency'] ?? 'AMD',
            rawPayload: $payload
        );
    }
}
