<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class ArCaGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'arca';
    }

    public function getName(): string
    {
        return 'arca';
    }

    public function initializePayment(object $payment, array $options = []): array
    {
        $intent = new PaymentIntentDTO(
            tenantId: (string) ($payment->tenant_id ?? 'default'),
            invoiceId: isset($payment->invoice_id) ? (string) $payment->invoice_id : null,
            amount: (float) $payment->amount,
            currency: $payment->currency ?? 'AMD',
            description: 'Order #'.($payment->order_id ?? $payment->id),
            returnUrl: $options['return_url'] ?? 'https://arca.am/return',
            cancelUrl: $options['cancel_url'] ?? 'https://arca.am/cancel'
        );

        $result = $this->initiatePayment($intent);

        return [
            'success' => true,
            'transaction_id' => $result->transactionId,
            'payment_url' => $result->redirectUrl,
        ];
    }

    public function createPayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        return $this->initiatePayment($intent);
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $orderId = 'ARCA_'.Str::upper(Str::random(12));
        $redirectUrl = "https://arca.am/payment/pay?orderId={$orderId}";

        return new PaymentResultDTO(
            status: 'redirect',
            transactionId: $orderId,
            redirectUrl: $redirectUrl,
            gatewayResponse: [
                'order_id' => $orderId,
                'gateway' => 'arca',
                'amount' => $intent->amount,
                'currency' => $intent->currency,
            ]
        );
    }

    public function authorize(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $orderId = 'ARCA_AUTH_'.Str::upper(Str::random(12));

        return new PaymentResultDTO(
            status: 'authorized',
            transactionId: $orderId,
            redirectUrl: "https://arca.am/payment/hold?orderId={$orderId}",
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
                'action' => 'deposit',
                'amount' => $amount,
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
                'action' => 'reverse',
                'cancelled_at' => now()->toIso8601String(),
            ]
        );
    }

    public function refund(RefundDTO $dto): RefundResultDTO
    {
        $refundId = 'ARCA_REF_'.Str::upper(Str::random(10));

        return new RefundResultDTO(
            status: 'successful',
            refundId: $refundId,
            refundedAmount: $dto->amount,
            gatewayResponse: [
                'refund_id' => $refundId,
                'transaction_id' => $dto->transactionId,
                'amount' => $dto->amount,
                'reason' => $dto->reason,
            ]
        );
    }

    public function getStatus(string $transactionId): PaymentResultDTO
    {
        return new PaymentResultDTO(
            status: 'successful',
            transactionId: $transactionId,
            gatewayResponse: [
                'orderStatus' => 2, // 2 = deposited / approved in ArCa
                'actionCode' => 0,
                'actionCodeDescription' => 'Approved',
            ]
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        $status = 'successful';
        if (isset($payload['orderStatus']) && (int) $payload['orderStatus'] !== 2) {
            $status = 'failed';
        }

        return new PaymentResultDTO(
            status: $status,
            transactionId: $transactionId,
            gatewayResponse: $payload
        );
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookResultDTO
    {
        $transactionId = $payload['orderId'] ?? ($payload['mdOrder'] ?? 'UNKNOWN');
        $status = 'successful';
        if (isset($payload['status']) && strtolower($payload['status']) === 'declined') {
            $status = 'failed';
        }

        return new WebhookResultDTO(
            event: 'payment.completed',
            transactionId: $transactionId,
            status: $status,
            amount: isset($payload['amount']) ? (float) ($payload['amount'] / 100) : null,
            payload: $payload
        );
    }
}
