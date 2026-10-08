<?php

namespace App\Domain\POS\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\POS\Models\PosCashMovement;
use App\Domain\POS\Models\PosRefund;
use App\Domain\POS\Models\PosSession;
use App\Domain\POS\Models\PosZReport;
use App\Domain\Sales\Models\Order;
use Illuminate\Support\Facades\DB;

class GenerateZReportAction
{
    public function execute(string $posSessionId): PosZReport
    {
        return DB::transaction(function () use ($posSessionId) {
            $session = PosSession::with('terminal')
                ->where('id', $posSessionId)
                ->lockForUpdate()
                ->firstOrFail();

            // Orders in this session
            $orders = Order::where('pos_session_id', $session->id)->get();
            $completedOrders = $orders->where('status', '!=', 'cancelled');
            $voidOrders = $orders->where('status', '===', 'cancelled');

            $totalSales = (float) $completedOrders->sum('total');
            $totalTax = (float) $completedOrders->sum('tax');
            $totalDiscount = (float) $completedOrders->sum('discount');

            // Payment transactions in this session
            $transactions = PaymentTransaction::where('pos_session_id', $session->id)
                ->where('status', 'successful')
                ->get();

            $cashSales = (float) $transactions->where('payment_method', 'cash')->sum('amount');
            $cardSales = (float) $transactions->where('payment_method', 'card')->sum('amount');
            $otherSales = (float) $transactions->whereNotIn('payment_method', ['cash', 'card'])->sum('amount');

            // Cash movements
            $cashMovements = PosCashMovement::where('pos_session_id', $session->id)->get();
            $cashIn = (float) $cashMovements->where('type', 'cash_in')->sum('amount');
            $cashOut = (float) $cashMovements->where('type', 'cash_out')->sum('amount');

            // Refunds
            $refunds = PosRefund::where('pos_session_id', $session->id)->get();
            $totalRefunds = (float) $refunds->sum('amount');

            $openingCash = (float) $session->opening_cash;
            $expectedCash = round($openingCash + $cashSales + $cashIn - $cashOut, 2);
            $declaredCash = (float) ($session->closing_cash_declared ?? $session->closing_cash_calculated);
            $cashDifference = round($declaredCash - $expectedCash, 2);

            $zReportNumber = 'Z-' . date('Ymd') . '-' . substr($session->session_number, -6);

            $zReport = PosZReport::updateOrCreate(
                [
                    'tenant_id' => $session->tenant_id,
                    'pos_session_id' => $session->id,
                ],
                [
                    'pos_terminal_id' => $session->pos_terminal_id,
                    'cashier_id' => $session->cashier_id,
                    'z_report_number' => $zReportNumber,
                    'opened_at' => $session->opened_at,
                    'closed_at' => $session->closed_at ?? now(),
                    'opening_cash' => $openingCash,
                    'total_sales_amount' => $totalSales,
                    'total_cash_sales' => $cashSales,
                    'total_card_sales' => $cardSales,
                    'total_other_sales' => $otherSales,
                    'total_tax_amount' => $totalTax,
                    'total_refunds_amount' => $totalRefunds,
                    'total_discounts_amount' => $totalDiscount,
                    'cash_in_amount' => $cashIn,
                    'cash_out_amount' => $cashOut,
                    'expected_cash_in_drawer' => $expectedCash,
                    'closing_cash_declared' => $declaredCash,
                    'cash_difference' => $cashDifference,
                    'sales_count' => $completedOrders->count(),
                    'refunds_count' => $refunds->count(),
                    'void_count' => $voidOrders->count(),
                    'tax_breakdown' => [
                        'standard_vat_20' => round($totalTax, 2),
                        'exempt' => 0.00,
                    ],
                    'payments_breakdown' => [
                        'cash' => $cashSales,
                        'card' => $cardSales,
                        'other' => $otherSales,
                    ],
                ]
            );

            // Audit log
            AuditLog::create([
                'tenant_id' => $session->tenant_id,
                'user_id' => $session->cashier_id,
                'action' => 'pos.z_report_generated',
                'entity_type' => PosZReport::class,
                'entity_id' => $zReport->id,
                'new_values' => [
                    'z_report_number' => $zReportNumber,
                    'total_sales' => $totalSales,
                    'expected_cash' => $expectedCash,
                    'cash_difference' => $cashDifference,
                ],
                'created_at' => now(),
            ]);

            return $zReport;
        });
    }
}
