<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class IdramGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'idram';
    }

    public function createPayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        return $this->initiatePayment($intent);
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $billNo = 'IDRAM_'.Str::upper(Str::random(10));

        // Idram standard checkout redirection
        $redirectUrl = "https://banking.idram.am/Payment/GetPayment?EDP_BILL_NO={$billNo}&EDP_AMOUNT={$intent->amount}";

        return new PaymentResultDTO(
            status: 'redirect',
            transactionId: $billNo,
            redirectUrl: $redirectUrl,
            gatewayResponse: [
                'bill_no' => $billNo,
                'gateway' => 'idram',
                'amount' => $intent->amount,
                'currency' => $intent->currency,
            ]
        );
    }

    public function authorize(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $billNo = 'IDRAM_AUTH_'.Str::upper(Str::random(10));

        return new PaymentResultDTO(
            status: 'authorized',
            transactionId: $billNo,
            redirectUrl: "https://banking.idram.am/Payment/Hold?EDP_BILL_NO={$billNo}",
            gatewayResponse: ['bill_no' => $billNo, 'amount' => $intent->amount]
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
            gatewayResponse: ['transaction_id' => $transactionId, 'gateway' => 'idram', 'status' => 'paid']
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        $status = ($payload['EDP_PRECHECK'] ?? '') === 'YES' || ($payload['status'] ?? '') === 'success';

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
            refundId: 'IDRAM_REF_'.Str::upper(Str::random(10)),
            gatewayResponse: ['refunded_amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookResultDTO
    {
        $billNo = $payload['EDP_BILL_NO'] ?? $payload['transaction_id'] ?? null;
        $isSuccess = isset($payload['EDP_PAYER_ACCOUNT']) || ($payload['status'] ?? '') === 'successful' || ($payload['status'] ?? '') === 'paid';

        // Check secret signature if present
        $verified = true;
        if (isset($headers['x-idram-signature']) && isset($headers['x-secret'])) {
            $expected = hash_hmac('sha256', (string) $billNo, (string) $headers['x-secret']);
            $verified = hash_equals($expected, $headers['x-idram-signature']);
        }

        return new WebhookResultDTO(
            verified: $verified,
            transactionId: $billNo,
            status: $isSuccess ? 'successful' : 'failed',
            amount: isset($payload['EDP_AMOUNT']) ? (float) $payload['EDP_AMOUNT'] : (isset($payload['amount']) ? (float) $payload['amount'] : null),
            currency: 'AMD',
            rawPayload: $payload
        );
    }
}
