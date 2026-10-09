<?php

namespace App\Domain\POS\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Fiscal\FiscalProviderManager;
use App\Domain\POS\Models\PosCashMovement;
use App\Domain\Sales\Models\Order;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PosVoidAction
{
    public function __construct(
        protected RecordStockMovementAction $recordStockMovement,
        protected FiscalProviderManager $fiscalProviderManager
    ) {}

    public function execute(string $orderId, string $reason = 'Cashier void', ?string $userId = null): Order
    {
        return DB::transaction(function () use ($orderId, $reason, $userId) {
            $order = Order::with(['items', 'fiscalReceipt', 'posSession', 'posTerminal'])
                ->where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->isCancelled()) {
                throw new InvalidArgumentException('Order is already cancelled/voided.');
            }

            $session = $order->posSession;
            $cashier = $userId ?? $session?->cashier_id ?? auth()->id();
            $warehouseId = $order->posTerminal?->warehouse_id;

            // 1. Restock all items back into inventory
            if ($warehouseId) {
                foreach ($order->items as $item) {
                    $this->recordStockMovement->execute(
                        warehouseId: $warehouseId,
                        productId: $item->product_id,
                        productVariantId: $item->variant_id,
                        type: 'adjustment_plus',
                        quantity: (float) $item->quantity,
                        unitCost: 0.0,
                        userId: $cashier,
                        referenceType: Order::class,
                        referenceId: $order->id,
                        notes: "POS Void restock for order {$order->order_number} ({$reason})"
                    );
                }
            }

            // 2. Reverse cash drawer if it was a cash sale and session is open
            $hasCash = $order->paymentTransactions()->where('payment_method', 'cash')->exists();
            if ($hasCash && $session && $session->isOpen()) {
                $cashAmount = (float) $order->paymentTransactions()->where('payment_method', 'cash')->sum('amount');
                $session->closing_cash_calculated = max(0.0, (float) $session->closing_cash_calculated - $cashAmount);
                $session->save();

                PosCashMovement::create([
                    'tenant_id' => $order->tenant_id,
                    'pos_session_id' => $session->id,
                    'user_id' => $cashier,
                    'type' => 'cash_out',
                    'amount' => $cashAmount,
                    'reason' => "POS Void reversal for order {$order->receipt_number}",
                    'created_at' => now(),
                ]);
            }

            // 3. Fiscal Provider Void Hook
            if ($order->fiscalReceipt) {
                $fiscalProvider = $this->fiscalProviderManager->provider($order->fiscalReceipt->provider);
                $fiscalProvider->cancelReceipt($order->fiscalReceipt->id, ['reason' => $reason]);
                $order->fiscalReceipt->status = 'cancelled';
                $order->fiscalReceipt->save();
            }

            // 4. Update order status
            $order->status = 'cancelled';
            $order->payment_status = 'refunded';
            $order->cancelled_at = now();
            $order->internal_notes = ($order->internal_notes ? $order->internal_notes."\n" : '')."Voided: {$reason}";
            $order->save();

            // 5. Audit Logging
            AuditLog::create([
                'tenant_id' => $order->tenant_id,
                'user_id' => $cashier,
                'action' => 'pos.void',
                'entity_type' => Order::class,
                'entity_id' => $order->id,
                'old_values' => ['status' => 'delivered', 'payment_status' => 'paid'],
                'new_values' => ['status' => 'cancelled', 'payment_status' => 'refunded', 'reason' => $reason],
                'created_at' => now(),
            ]);

            return $order;
        });
    }
}
