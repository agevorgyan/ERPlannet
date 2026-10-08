<?php

namespace App\Infrastructure\Payments\Contracts;

use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;

interface PaymentGatewayInterface
{
    /**
     * Unique gateway identifier (e.g., 'ameriabank', 'idram', 'telcell', 'stripe', 'cash', 'bank_transfer')
     */
    public function getIdentifier(): string;

    /**
     * Create payment session / intent.
     */
    public function createPayment(PaymentIntentDTO $intent): PaymentResultDTO;

    /**
     * Alias for createPayment (backwards compatibility).
     */
    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO;

    /**
     * Authorize payment without immediate capture (pre-authorization / hold).
     */
    public function authorize(PaymentIntentDTO $intent): PaymentResultDTO;

    /**
     * Capture a previously authorized payment.
     */
    public function capture(string $transactionId, float $amount, array $options = []): PaymentResultDTO;

    /**
     * Cancel / void an authorized or pending payment.
     */
    public function cancel(string $transactionId, array $options = []): PaymentResultDTO;

    /**
     * Refund a completed payment partially or fully.
     */
    public function refund(RefundDTO $dto): RefundResultDTO;

    /**
     * Query gateway for real-time payment status.
     */
    public function getStatus(string $transactionId): PaymentResultDTO;

    /**
     * Verify payment status using transaction ID and raw gateway payload.
     */
    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO;

    /**
     * Parse and validate incoming webhook from the payment provider (verifying signature & headers).
     */
    public function handleWebhook(array $payload, array $headers = []): WebhookResultDTO;
}
