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

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        $status = ($payload['status'] ?? '') === 'success' || ($payload['state'] ?? '') === 'PAID';

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

    public function handleWebhook(array $payload): WebhookResultDTO
    {
        $transactionId = $payload['invoice_id'] ?? $payload['transaction_id'] ?? null;
        $status = ($payload['status'] ?? '') === 'paid' ? 'successful' : 'failed';

        return new WebhookResultDTO(
            handled: true,
            status: $status,
            transactionId: $transactionId,
            payload: $payload
        );
    }
}
