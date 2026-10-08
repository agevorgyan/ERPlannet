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

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $paymentIntentId = 'pi_' . Str::lower(Str::random(24));
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
            refundId: 're_' . Str::lower(Str::random(24)),
            gatewayResponse: ['amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload): WebhookResultDTO
    {
        $type = $payload['type'] ?? '';
        $object = $payload['data']['object'] ?? [];

        $isSuccess = ($type === 'payment_intent.succeeded' || $type === 'checkout.session.completed');

        return new WebhookResultDTO(
            verified: true,
            transactionId: $object['id'] ?? null,
            status: $isSuccess ? 'successful' : 'failed',
            amount: isset($object['amount']) ? ((float) $object['amount']) / 100 : null,
            currency: strtoupper($object['currency'] ?? 'USD'),
            rawPayload: $payload
        );
    }
}
