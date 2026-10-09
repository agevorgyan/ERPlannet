<?php

namespace App\Domain\Manufacturing\Actions;

use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use Illuminate\Support\Facades\DB;

class StartProductionOrderAction
{
    public function __construct(
        protected RecordStockMovementAction $recordMovement
    ) {}

    public function execute(string $productionOrderId, ?string $userId = null): ProductionOrder
    {
        return DB::transaction(function () use ($productionOrderId, $userId) {
            $order = ProductionOrder::where('id', $productionOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($order->status, ['draft', 'confirmed'], true)) {
                throw new \InvalidArgumentException("Production order cannot be started from current status: {$order->status}.");
            }

            $totalMaterialCost = 0.0;

            foreach ($order->items as $item) {
                $qtyToConsume = (float) $item->planned_quantity;

                $productName = is_array($order->product->name) ? $order->product->getLocalizedName() : (string) $order->product->name;

                // 1. Deduct raw materials from source warehouse
                $movement = $this->recordMovement->execute(
                    warehouseId: $order->source_warehouse_id,
                    productId: $item->product_id,
                    productVariantId: $item->product_variant_id,
                    type: 'production_consume',
                    quantity: $qtyToConsume,
                    unitCost: (float) $item->product->cost_price,
                    stockBatchId: $item->stock_batch_id,
                    userId: $userId ?? $order->user_id,
                    referenceType: ProductionOrder::class,
                    referenceId: $order->id,
                    notes: "Raw material consumption for {$order->order_number} ({$productName})"
                );

                $itemCost = (float) $item->product->cost_price;
                $itemTotal = round($qtyToConsume * $itemCost, 4);
                $totalMaterialCost += $itemTotal;

                $item->consumed_quantity = $qtyToConsume;
                $item->unit_cost = $itemCost;
                $item->total_cost = $itemTotal;
                $item->save();
            }

            $order->status = 'in_progress';
            $order->started_at = now();
            $order->total_cost = $totalMaterialCost;
            $order->save();

            return $order->load(['items.product', 'sourceWarehouse', 'targetWarehouse']);
        });
    }
}
