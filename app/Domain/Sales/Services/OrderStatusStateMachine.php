<?php

namespace App\Domain\Sales\Services;

use App\Domain\CRM\Models\Customer;
use App\Domain\Sales\Exceptions\InvalidStatusTransitionException;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Actions\ReleaseStockAction;
use App\Domain\Warehouse\Actions\ReserveStockAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStatusStateMachine
{
    /** @var array<string, array<string>> */
    protected array $transitions = [
        'draft' => ['new', 'confirmed', 'cancelled'],
        'new' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'in_progress', 'ready', 'cancelled'],
        'processing' => ['ready', 'packed', 'delivery', 'completed', 'cancelled'],
        'in_progress' => ['ready', 'packed', 'delivery', 'completed', 'cancelled'],
        'packed' => ['delivery', 'ready', 'completed', 'cancelled'],
        'delivery' => ['delivered', 'completed', 'cancelled'],
        'ready' => ['completed', 'delivered', 'cancelled'],
        'delivered' => ['completed'],
        'completed' => [], // Terminal state
        'cancelled' => [], // Terminal state
    ];

    public function __construct(
        protected ?ReserveStockAction $reserveStockAction = null,
        protected ?ReleaseStockAction $releaseStockAction = null,
        protected ?RecordStockMovementAction $recordStockMovementAction = null
    ) {
        $this->reserveStockAction ??= app(ReserveStockAction::class);
        $this->releaseStockAction ??= app(ReleaseStockAction::class);
        $this->recordStockMovementAction ??= app(RecordStockMovementAction::class);
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->transitions[$from] ?? [], true);
    }

    public function transition(Order $order, string $newStatus, ?string $userId = null, ?string $comment = null): Order
    {
        $currentStatus = $order->status;

        if ($currentStatus === $newStatus) {
            return $order;
        }

        if (! $this->canTransition($currentStatus, $newStatus)) {
            throw new InvalidStatusTransitionException($currentStatus, $newStatus);
        }

        return DB::transaction(function () use ($order, $currentStatus, $newStatus, $userId, $comment) {
            $updates = ['status' => $newStatus];
            $metadata = $order->metadata ?? [];

            // 1. Entering Confirmed: Reserve stock if warehouse is assigned
            if ($newStatus === 'confirmed') {
                $updates['confirmed_at'] = now();

                if ($order->warehouse_id && empty($metadata['stock_reserved'])) {
                    $this->reserveOrderStock($order);
                    $metadata['stock_reserved'] = true;
                }
            }

            // 2. Entering Completed / Delivered
            if ($newStatus === 'completed' || $newStatus === 'delivered') {
                if ($newStatus === 'delivered' && ! $order->delivered_at) {
                    $updates['delivered_at'] = now();
                }

                if ($newStatus === 'completed' && ! $order->completed_at) {
                    $updates['completed_at'] = now();
                }

                // Deduct inventory movement upon completion or delivery
                if ($order->warehouse_id && empty($metadata['stock_deducted'])) {
                    $this->fulfillOrderStock($order, $userId);
                    $metadata['stock_reserved'] = false;
                    $metadata['stock_deducted'] = true;
                }

                // Increment customer stats once
                if ($order->customer_id && empty($metadata['customer_stats_updated'])) {
                    $customer = $order->customer ?? Customer::find($order->customer_id);
                    if ($customer) {
                        $customer->increment('orders_count');
                        $customer->increment('total_spent', $order->total);
                        $customer->update(['last_ordered_at' => now()]);
                        $metadata['customer_stats_updated'] = true;
                    }
                }
            }

            // 3. Entering Cancelled: Release stock reservations if held
            if ($newStatus === 'cancelled') {
                $updates['cancelled_at'] = now();
                $updates['cancellation_reason'] = $comment;

                if ($order->warehouse_id) {
                    $this->releaseOrderStock($order);
                    $metadata['stock_reserved'] = false;
                }
            }

            $updates['metadata'] = $metadata;
            $order->update($updates);

            // Record status audit history
            OrderStatusHistory::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'user_id' => $userId,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'comment' => $comment,
            ]);

            return $order->fresh(['statusHistories', 'items', 'customer', 'branch', 'warehouse']);
        });
    }

    public function getAvailableTransitions(string $currentStatus): array
    {
        return $this->transitions[$currentStatus] ?? [];
    }

    /**
     * Reserve inventory for each order item in the designated warehouse.
     */
    protected function reserveOrderStock(Order $order): void
    {
        if (! $order->warehouse_id) {
            return;
        }

        foreach ($order->items as $item) {
            try {
                $this->reserveStockAction->execute(
                    warehouseId: $order->warehouse_id,
                    productId: $item->product_id,
                    productVariantId: $item->variant_id,
                    quantity: (float) $item->quantity
                );
            } catch (\Throwable $e) {
                Log::warning("Could not reserve stock for item {$item->product_sku}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Release previously reserved stock upon cancellation.
     */
    protected function releaseOrderStock(Order $order): void
    {
        if (! $order->warehouse_id) {
            return;
        }

        foreach ($order->items as $item) {
            try {
                $this->releaseStockAction->execute(
                    warehouseId: $order->warehouse_id,
                    productId: $item->product_id,
                    productVariantId: $item->variant_id,
                    quantity: (float) $item->quantity
                );
            } catch (\Throwable $e) {
                Log::warning("Could not release stock for item {$item->product_sku}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Deduct physical inventory through immutable double-entry stock movement upon order completion.
     */
    protected function fulfillOrderStock(Order $order, ?string $userId): void
    {
        if (! $order->warehouse_id) {
            return;
        }

        foreach ($order->items as $item) {
            // First release reservation
            try {
                $this->releaseStockAction->execute(
                    warehouseId: $order->warehouse_id,
                    productId: $item->product_id,
                    productVariantId: $item->variant_id,
                    quantity: (float) $item->quantity
                );
            } catch (\Throwable $e) {
                Log::warning("Could not release reservation before fulfillment: {$e->getMessage()}");
            }

            // Then record immutable outbound stock movement
            try {
                $this->recordStockMovementAction->execute(
                    warehouseId: $order->warehouse_id,
                    productId: $item->product_id,
                    productVariantId: $item->variant_id,
                    type: 'sale_delivery',
                    quantity: (float) $item->quantity,
                    unitCost: (float) $item->unit_cost,
                    stockBatchId: null,
                    userId: $userId,
                    referenceType: 'Order',
                    referenceId: $order->id,
                    notes: "Fulfilled for order {$order->order_number}"
                );
            } catch (\Throwable $e) {
                Log::error("Failed recording stock movement for order {$order->order_number}: {$e->getMessage()}");
            }
        }
    }
}
