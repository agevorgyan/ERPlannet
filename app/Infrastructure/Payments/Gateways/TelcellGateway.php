<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class TelcellGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'telcell';
    }

    public function createPayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        return $this->initiatePayment($intent);
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $invoiceId = 'TELCELL_' . Str::upper(Str::random(10));

        // Telcell Wallet checkout & dynamic QR payload
        $checkoutUrl = "https://pay.telcell.am/checkout?invoice_id={$invoiceId}&sum={$intent->amount}&currency={$intent->currency}";
        $qrPayload = "telcell://pay?invoice={$invoiceId}&amount={$intent->amount}";

        return new PaymentResultDTO(
            status: 'redirect',
            transactionId: $invoiceId,
            redirectUrl: $checkoutUrl,
            gatewayResponse: [
                'invoice_id' => $invoiceId,
                'gateway' => 'telcell',
                'amount' => $intent->amount,
                'currency' => $intent->currency,
                'qr_payload' => $qrPayload,
                'qr_code' => $qrPayload,
                'deep_link' => $qrPayload,
            ]
        );
    }

    public function authorize(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $invoiceId = 'TELCELL_AUTH_' . Str::upper(Str::random(10));
        return new PaymentResultDTO(
            status: 'authorized',
            transactionId: $invoiceId,
            redirectUrl: "https://pay.telcell.am/checkout?invoice_id={$invoiceId}&type=auth",
            gatewayResponse: ['invoice_id' => $invoiceId, 'amount' => $intent->amount]
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
            gatewayResponse: ['transaction_id' => $transactionId, 'gateway' => 'telcell', 'status' => 'successful']
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        $status = ($payload['status'] ?? '') === 'success' || ($payload['state'] ?? '') === 'PAID' || ($payload['status'] ?? '') === 'successful';

        return new PaymentResultDTO(
            status: $status ? 'successful' : 'failed',
            transactionId: $transactionId,
            gatewayResponse: $payload
        );
    }

    public function refund(RefundDTO $dto): RefundResultDTO
    {
        return new RefundResultDTO(
            success: true,
            refundId: 'TELCELL_REF_' . Str::upper(Str::random(10)),
            gatewayResponse: ['refunded_amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookResultDTO
    {
        $transactionId = $payload['invoice_id'] ?? $payload['transaction_id'] ?? null;
        $statusRaw = $payload['status'] ?? '';
        $status = ($statusRaw === 'paid' || $statusRaw === 'successful' || $statusRaw === 'PAID') ? 'successful' : 'failed';

        $verified = true;
        if (isset($headers['x-telcell-signature']) && isset($headers['x-secret'])) {
            $expected = hash_hmac('sha256', (string) $transactionId, (string) $headers['x-secret']);
            $verified = hash_equals($expected, $headers['x-telcell-signature']);
        }

        return new WebhookResultDTO(
            verified: $verified,
            transactionId: $transactionId,
            status: $status,
            amount: isset($payload['sum']) ? (float) $payload['sum'] : (isset($payload['amount']) ? (float) $payload['amount'] : null),
            currency: $payload['currency'] ?? 'AMD',
            rawPayload: $payload
        );
    }
}
