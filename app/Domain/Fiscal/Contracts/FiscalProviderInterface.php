<?php

namespace App\Domain\Fiscal\Contracts;

use App\Domain\Fiscal\DTOs\FiscalReceiptResultDTO;
use App\Domain\Sales\Models\Order;

interface FiscalProviderInterface
{
    /**
     * Provider unique code (e.g. 'mock_armenia_src', 'armenia_e_invoicing')
     */
    public function getIdentifier(): string;

    /**
     * Register a fiscal receipt for an order with tax authority / ECR device.
     */
    public function createReceipt(Order $order, array $options = []): FiscalReceiptResultDTO;

    /**
     * Void or cancel an existing fiscal receipt.
     */
    public function cancelReceipt(string $fiscalReceiptId, array $options = []): FiscalReceiptResultDTO;

    /**
     * Refund a fiscal receipt partially or fully.
     */
    public function refundReceipt(string $fiscalReceiptId, float $amount, array $options = []): FiscalReceiptResultDTO;

    /**
     * Get real-time status of fiscal receipt from tax authority / ECR device.
     */
    public function getStatus(string $fiscalReceiptId): FiscalReceiptResultDTO;
}
