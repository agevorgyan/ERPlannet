<?php

namespace App\Domain\POS\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Fiscal\FiscalProviderManager;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\POS\Events\PosRefundCompletedEvent;
use App\Domain\POS\Models\PosCashMovement;
use App\Domain\POS\Models\PosRefund;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PosRefundAction
{
    public function __construct(
        protected RecordStockMovementAction $recordStockMovement,
        protected FiscalProviderManager $fiscalProviderManager,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $orderId,
        float $amount,
        array $itemsToRestock = [],
        string $refundMethod = 'cash',
        string $reason = 'Customer return',
        ?string $cashierId = null
    ): PosRefund {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Refund amount must be greater than zero.');
        }

        return DB::transaction(function () use ($orderId, $amount, $itemsToRestock, $refundMethod, $reason, $cashierId) {
            $order = Order::with(['items', 'fiscalReceipt', 'posSession', 'posTerminal'])
                ->where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->isCancelled()) {
                throw new InvalidArgumentException('Cannot refund a cancelled order.');
            }

            if ($amount > (float) $order->total) {
                throw new InvalidArgumentException("Refund amount {$amount} exceeds order total {$order->total}.");
            }

            $session = $order->posSession;
            $cashier = $cashierId ?? $session?->cashier_id ?? auth()->id();
            $refundNumber = 'REF-'.date('Ymd').'-'.strtoupper(Str::random(6));

            // 1. Restock items to warehouse if provided
            $restockedSummary = [];
            $warehouseId = $order->posTerminal?->warehouse_id;

            foreach ($itemsToRestock as $itemData) {
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('id', $itemData['order_item_id'])
                    ->firstOrFail();

                $qty = (float) ($itemData['quantity'] ?? $orderItem->quantity);

                if ($warehouseId && $qty > 0) {
                    $this->recordStockMovement->execute(
                        warehouseId: $warehouseId,
                        productId: $orderItem->product_id,
                        productVariantId: $orderItem->variant_id,
                        type: 'adjustment_plus',
                        quantity: $qty,
                        unitCost: 0.0,
                        userId: $cashier,
                        referenceType: PosRefund::class,
                        referenceId: $order->id,
                        notes: "POS Refund {$refundNumber} restock for order {$order->order_number}"
                    );
                }

                $restockedSummary[] = [
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity' => $qty,
                ];
            }

            // 2. If cash refund, deduct from session cash drawer and record cash movement
            if ($refundMethod === 'cash' && $session && $session->isOpen()) {
                $session->closing_cash_calculated = max(0.0, (float) $session->closing_cash_calculated - $amount);
                $session->save();

                PosCashMovement::create([
                    'tenant_id' => $order->tenant_id,
                    'pos_session_id' => $session->id,
                    'user_id' => $cashier,
                    'type' => 'cash_out',
                    'amount' => $amount,
                    'reason' => "POS Refund #{$refundNumber} for order {$order->receipt_number}",
                    'created_at' => now(),
                ]);
            }

            // 3. Record refund payment transaction
            PaymentTransaction::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'pos_session_id' => $session?->id,
                'gateway' => $refundMethod === 'cash' ? 'cash' : 'ameria',
                'payment_method' => $refundMethod,
                'transaction_id' => 'TX-REF-'.strtoupper(Str::random(10)),
                'amount' => -$amount,
                'currency' => $order->currency,
                'status' => 'refunded',
                'paid_at' => now(),
                'gateway_response' => [
                    'refund_number' => $refundNumber,
                    'reason' => $reason,
                ],
            ]);

            // 4. Update order payment status
            $isFullRefund = ($amount >= (float) $order->total);
            $order->payment_status = $isFullRefund ? 'refunded' : 'partially_refunded';
            if ($isFullRefund && ! empty($itemsToRestock)) {
                $order->status = 'delivered'; // keep delivered or set returned
            }
            $order->save();

            // 5. Fiscal Provider Refund Hook
            if ($order->fiscalReceipt) {
                $fiscalProvider = $this->fiscalProviderManager->provider($order->fiscalReceipt->provider);
                $fiscalProvider->refundReceipt($order->fiscalReceipt->id, $amount, ['reason' => $reason]);
            }

            // 6. Create PosRefund record
            $posRefund = PosRefund::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'pos_session_id' => $session?->id,
                'cashier_id' => $cashier,
                'refund_number' => $refundNumber,
                'amount' => $amount,
                'refund_method' => $refundMethod,
                'reason' => $reason,
                'status' => 'completed',
                'items_payload' => $restockedSummary,
            ]);

            // 7. Audit Logging
            AuditLog::create([
                'tenant_id' => $order->tenant_id,
                'user_id' => $cashier,
                'action' => 'pos.refund',
                'entity_type' => PosRefund::class,
                'entity_id' => $posRefund->id,
                'old_values' => ['order_total' => $order->total, 'payment_status' => 'paid'],
                'new_values' => [
                    'refund_amount' => $amount,
                    'is_full_refund' => $isFullRefund,
                    'refund_number' => $refundNumber,
                    'reason' => $reason,
                ],
                'created_at' => now(),
            ]);

            // 8. Event hook
            event(new PosRefundCompletedEvent($posRefund, $order));

            return $posRefund;
        });
    }
}
