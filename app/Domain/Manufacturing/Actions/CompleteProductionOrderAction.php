<?php

namespace App\Domain\Manufacturing\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockBatch;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CompleteProductionOrderAction
{
    public function __construct(
        protected RecordStockMovementAction $recordMovement,
        protected EntitlementManagerInterface $entitlements,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $productionOrderId,
        float $actualQuantity,
        float $wasteQuantity = 0.0,
        ?string $userId = null
    ): ProductionOrder {
        if ($actualQuantity <= 0) {
            throw new \InvalidArgumentException('Actual output quantity must be greater than zero.');
        }

        $tenant = $this->tenantContext->getTenant();
        if (!$tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        return DB::transaction(function () use ($tenant, $productionOrderId, $actualQuantity, $wasteQuantity, $userId) {
            $order = ProductionOrder::where('id', $productionOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($order->status, ['in_progress', 'quality_check'], true)) {
                throw new \InvalidArgumentException("Production order cannot be completed from current status: {$order->status}.");
            }

            // 1. ISO 22000 Gating check: if tenant has ISO 22000 feature, verify inspection passed
            if ($this->entitlements->can('feature.iso22000')) {
                $hasPassedInspection = $order->inspections()->where('status', 'passed')->exists();
                if (!$hasPassedInspection) {
                    throw new \InvalidArgumentException(
                        'ISO 22000 compliance violation: Production order cannot be released into inventory without a PASSED quality inspection.'
                    );
                }
            }

            // 2. Cost calculation
            $materialCost = (float) $order->items()->sum('total_cost');
            $laborCost = (float) $order->recipe->labor_cost;
            $overheadCost = (float) $order->recipe->overhead_cost;
            $totalCost = round($materialCost + $laborCost + $overheadCost, 4);
            $unitCost = round($totalCost / max(0.0001, $actualQuantity), 2);

            // 3. Batch formulation with automatic shelf-life expiration
            $shelfLife = $order->product->shelf_life_days ?? 30;
            $expiryDate = now()->addDays($shelfLife)->toDateString();
            $batchNumber = 'LOT-' . date('Ymd') . '-' . substr($order->order_number, -6);

            $batch = StockBatch::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'warehouse_id' => $order->target_warehouse_id,
                    'product_id' => $order->product_id,
                    'product_variant_id' => $order->product_variant_id,
                    'batch_number' => $batchNumber,
                ],
                [
                    'quantity_on_hand' => 0.0,
                    'cost_price' => $unitCost,
                    'mfg_date' => now()->toDateString(),
                    'expiry_date' => $expiryDate,
                    'status' => 'active',
                    'notes' => "Yield from Production Order #{$order->order_number}",
                ]
            );

            // 4. Record stock movement: type 'production_yield'
            $this->recordMovement->execute(
                warehouseId: $order->target_warehouse_id,
                productId: $order->product_id,
                productVariantId: $order->product_variant_id,
                type: 'production_yield',
                quantity: $actualQuantity,
                unitCost: $unitCost,
                stockBatchId: $batch->id,
                userId: $userId ?? $order->user_id,
                referenceType: ProductionOrder::class,
                referenceId: $order->id,
                notes: "Finished goods yield for {$order->order_number} ({$actualQuantity} units)"
            );

            // 5. Update Product cost_price
            $order->product->cost_price = $unitCost;
            $order->product->save();

            // 6. Complete order
            $order->actual_quantity = $actualQuantity;
            $order->waste_quantity = $wasteQuantity;
            $order->unit_cost = $unitCost;
            $order->total_cost = $totalCost;
            $order->stock_batch_id = $batch->id;
            $order->status = 'completed';
            $order->completed_at = now();
            $order->save();

            return $order->load(['product', 'targetWarehouse', 'batch', 'recipe']);
        });
    }
}
