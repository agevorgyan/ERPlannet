<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class AmeriaBankGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'ameriabank';
    }

    public function createPayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        return $this->initiatePayment($intent);
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        // Generates payment order ID for Ameriabank vPOS
        $orderId = 'AMERIA_'.Str::upper(Str::random(12));

        // In production: calls Ameriabank vPOS InitPayment API endpoint with ClientID, Username, Password, Description, Amount, OrderID, BackURL
        $redirectUrl = "https://services.ameriabank.am/VPOS/Payments/Pay?id={$orderId}";

        return new PaymentResultDTO(
            status: 'redirect',
            transactionId: $orderId,
            redirectUrl: $redirectUrl,
            gatewayResponse: [
                'order_id' => $orderId,
                'gateway' => 'ameriabank',
                'amount' => $intent->amount,
                'currency' => $intent->currency,
            ]
        );
    }

    public function authorize(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $orderId = 'AMERIA_AUTH_'.Str::upper(Str::random(12));

        return new PaymentResultDTO(
            status: 'authorized',
            transactionId: $orderId,
            redirectUrl: "https://services.ameriabank.am/VPOS/Payments/Hold?id={$orderId}",
            gatewayResponse: [
                'order_id' => $orderId,
                'type' => 'pre_authorization',
                'amount' => $intent->amount,
            ]
        );
    }

    public function capture(string $transactionId, float $amount, array $options = []): PaymentResultDTO
    {
        return new PaymentResultDTO(
            status: 'successful',
            transactionId: $transactionId,
            gatewayResponse: [
                'captured_amount' => $amount,
                'captured_at' => now()->toIso8601String(),
            ]
        );
    }

    public function cancel(string $transactionId, array $options = []): PaymentResultDTO
    {
        return new PaymentResultDTO(
            status: 'cancelled',
            transactionId: $transactionId,
            gatewayResponse: [
                'cancelled_at' => now()->toIso8601String(),
                'reason' => $options['reason'] ?? 'User or timeout cancel',
            ]
        );
    }

    public function getStatus(string $transactionId): PaymentResultDTO
    {
        return new PaymentResultDTO(
            status: 'successful',
            transactionId: $transactionId,
            gatewayResponse: [
                'transaction_id' => $transactionId,
                'gateway' => 'ameriabank',
                'status' => 'settled',
            ]
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        // In production: calls Ameriabank vPOS GetPaymentDetails API endpoint
        $resCode = $payload['response_code'] ?? '00';
        $isSuccess = ($resCode === '00');

        return new PaymentResultDTO(
            status: $isSuccess ? 'successful' : 'failed',
            transactionId: $transactionId,
            gatewayResponse: $payload,
            errorMessage: $isSuccess ? null : 'Ameriabank transaction rejected.'
        );
    }

    public function refund(RefundDTO $dto): RefundResultDTO
    {
        return new RefundResultDTO(
            success: true,
            refundId: 'AMERIA_REF_'.Str::upper(Str::random(10)),
            gatewayResponse: ['refunded_amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookResultDTO
    {
        $orderId = $payload['order_id'] ?? $payload['transaction_id'] ?? null;
        $statusRaw = $payload['payment_status'] ?? $payload['status'] ?? '';
        $status = ($statusRaw === 'paid' || $statusRaw === 'successful' || $statusRaw === '00') ? 'successful' : 'failed';

        // Signature check if provided in header
        $verified = true;
        if (isset($headers['x-signature']) && isset($headers['x-secret'])) {
            $expected = hash_hmac('sha256', (string) $orderId, (string) $headers['x-secret']);
            $verified = hash_equals($expected, $headers['x-signature']);
        }

        return new WebhookResultDTO(
            verified: $verified,
            transactionId: $orderId,
            status: $status,
            amount: isset($payload['amount']) ? (float) $payload['amount'] : null,
            currency: $payload['currency'] ?? 'AMD',
            rawPayload: $payload
        );
    }
}
