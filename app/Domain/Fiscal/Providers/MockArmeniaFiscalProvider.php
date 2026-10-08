<?php

namespace App\Domain\Fiscal\Providers;

use App\Domain\Fiscal\Contracts\FiscalProviderInterface;
use App\Domain\Fiscal\DTOs\FiscalReceiptResultDTO;
use App\Domain\Sales\Models\Order;
use Illuminate\Support\Str;

class MockArmeniaFiscalProvider implements FiscalProviderInterface
{
    public function getIdentifier(): string
    {
        return 'mock_armenia_src';
    }

    public function createReceipt(Order $order, array $options = []): FiscalReceiptResultDTO
    {
        $fiscalNumber = 'SRC-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        $crn = $options['crn'] ?? 'CRN-' . rand(10000000, 99999999);
        $taxId = $options['tax_id'] ?? '02654321'; // Armenia TIN

        // Armenian SRC QR standard payload simulation
        $qrPayload = json_encode([
            'tin' => $taxId,
            'crn' => $crn,
            'fisc' => $fiscalNumber,
            'total' => (float) $order->total,
            'time' => now()->toIso8601String(),
            'sec' => hash('sha256', $fiscalNumber . $order->total),
        ]);

        return new FiscalReceiptResultDTO(
            success: true,
            fiscalReceiptId: (string) Str::uuid(),
            fiscalNumber: $fiscalNumber,
            crn: $crn,
            taxId: $taxId,
            qrPayload: $qrPayload,
            status: 'registered',
            rawResponse: [
                'provider' => 'mock_armenia_src',
                'status' => 'APPROVED',
                'message' => 'Fiscal receipt registered with Armenian SRC tax service.',
                'fiscal_timestamp' => now()->toIso8601String(),
            ]
        );
    }

    public function cancelReceipt(string $fiscalReceiptId, array $options = []): FiscalReceiptResultDTO
    {
        return new FiscalReceiptResultDTO(
            success: true,
            fiscalReceiptId: $fiscalReceiptId,
            fiscalNumber: null,
            crn: null,
            taxId: null,
            qrPayload: null,
            status: 'cancelled',
            rawResponse: [
                'provider' => 'mock_armenia_src',
                'status' => 'CANCELLED',
                'cancelled_at' => now()->toIso8601String(),
                'reason' => $options['reason'] ?? 'POS cashier void',
            ]
        );
    }

    public function refundReceipt(string $fiscalReceiptId, float $amount, array $options = []): FiscalReceiptResultDTO
    {
        return new FiscalReceiptResultDTO(
            success: true,
            fiscalReceiptId: $fiscalReceiptId,
            fiscalNumber: 'SRC-REF-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            crn: null,
            taxId: null,
            qrPayload: null,
            status: 'refunded',
            rawResponse: [
                'provider' => 'mock_armenia_src',
                'status' => 'REFUNDED',
                'refunded_amount' => $amount,
                'refunded_at' => now()->toIso8601String(),
            ]
        );
    }

    public function getStatus(string $fiscalReceiptId): FiscalReceiptResultDTO
    {
        return new FiscalReceiptResultDTO(
            success: true,
            fiscalReceiptId: $fiscalReceiptId,
            fiscalNumber: null,
            crn: null,
            taxId: null,
            qrPayload: null,
            status: 'registered',
            rawResponse: [
                'provider' => 'mock_armenia_src',
                'status' => 'ACTIVE',
            ]
        );
    }
}
