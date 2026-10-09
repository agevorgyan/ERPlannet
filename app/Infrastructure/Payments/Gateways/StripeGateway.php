<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class StripeGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'stripe';
    }

    public function createPayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        return $this->initiatePayment($intent);
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $paymentIntentId = 'pi_'.Str::lower(Str::random(24));
        $checkoutUrl = "https://checkout.stripe.com/c/pay/{$paymentIntentId}";

        return new PaymentResultDTO(
            status: 'redirect',
            transactionId: $paymentIntentId,
            redirectUrl: $checkoutUrl,
            gatewayResponse: [
                'id' => $paymentIntentId,
                'gateway' => 'stripe',
                'amount' => $intent->amount,
                'currency' => strtolower($intent->currency),
            ]
        );
    }

    public function authorize(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $paymentIntentId = 'pi_auth_'.Str::lower(Str::random(22));

        return new PaymentResultDTO(
            status: 'authorized',
            transactionId: $paymentIntentId,
            gatewayResponse: ['id' => $paymentIntentId, 'capture_method' => 'manual', 'amount' => $intent->amount]
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
            gatewayResponse: ['id' => $transactionId, 'gateway' => 'stripe', 'status' => 'succeeded']
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        $status = ($payload['status'] ?? 'succeeded') === 'succeeded' ? 'successful' : 'failed';

        return new PaymentResultDTO(
            status: $status,
            transactionId: $transactionId,
            gatewayResponse: $payload
        );
    }

    public function refund(RefundDTO $dto): RefundResultDTO
    {
        return new RefundResultDTO(
            success: true,
            refundId: 're_'.Str::lower(Str::random(24)),
            gatewayResponse: ['amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookResultDTO
    {
        $type = $payload['type'] ?? '';
        $object = $payload['data']['object'] ?? [];

        $isSuccess = ($type === 'payment_intent.succeeded' || $type === 'checkout.session.completed');

        $verified = true;
        if (isset($headers['stripe-signature']) && isset($headers['stripe-secret'])) {
            $expected = hash_hmac('sha256', json_encode($payload), (string) $headers['stripe-secret']);
            $verified = hash_equals($expected, $headers['stripe-signature']);
        }

        return new WebhookResultDTO(
            verified: $verified,
            transactionId: $object['id'] ?? $payload['transaction_id'] ?? null,
            status: $isSuccess ? 'successful' : 'failed',
            amount: isset($object['amount']) ? ((float) $object['amount']) / 100 : null,
            currency: strtoupper($object['currency'] ?? 'USD'),
            rawPayload: $payload
        );
    }
}
