<?php

namespace App\Domain\Warehouse\Actions;

use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\StockMovement;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class RecordStockMovementAction
{
    public const INBOUND_TYPES = [
        'purchase_receipt',
        'transfer_in',
        'adjustment_plus',
        'production_yield',
    ];

    public const OUTBOUND_TYPES = [
        'sale_delivery',
        'transfer_out',
        'adjustment_minus',
        'scrap',
        'production_consume',
    ];

    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $warehouseId,
        string $productId,
        ?string $productVariantId,
        string $type,
        float $quantity,
        float $unitCost = 0.0,
        ?string $stockBatchId = null,
        ?string $userId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Stock movement quantity must be greater than zero.');
        }

        $allTypes = array_merge(self::INBOUND_TYPES, self::OUTBOUND_TYPES);
        if (!in_array($type, $allTypes, true)) {
            throw new \InvalidArgumentException("Invalid stock movement type: {$type}");
        }

        $isInbound = in_array($type, self::INBOUND_TYPES, true);
        $tenant = $this->tenantContext->getTenant();
        if (!$tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        return DB::transaction(function () use (
            $tenant,
            $warehouseId,
            $productId,
            $productVariantId,
            $type,
            $quantity,
            $unitCost,
            $stockBatchId,
            $userId,
            $referenceType,
            $referenceId,
            $notes,
            $isInbound
        ) {
            // 1. Lock StockLevel or create it
            $stockLevel = StockLevel::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->when($productVariantId, fn ($q) => $q->where('product_variant_id', $productVariantId), fn ($q) => $q->whereNull('product_variant_id'))
                ->lockForUpdate()
                ->first();

            if (!$stockLevel) {
                $stockLevel = StockLevel::create([
                    'tenant_id' => $tenant->id,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'product_variant_id' => $productVariantId,
                    'quantity_on_hand' => 0.0,
                    'quantity_reserved' => 0.0,
                    'reorder_point' => 0.0,
                    'ideal_stock' => 0.0,
                    'updated_at' => now(),
                ]);

                // Re-lock
                $stockLevel = StockLevel::where('id', $stockLevel->id)->lockForUpdate()->first();
            }

            $balanceBefore = (float) $stockLevel->quantity_on_hand;

            // 2. Validate outbound sufficiency
            if (!$isInbound && $balanceBefore < $quantity) {
                throw new \InvalidArgumentException(
                    "Insufficient physical stock in warehouse for product. Available: {$balanceBefore}, Requested deduction: {$quantity}"
                );
            }

            // 3. Batch handling if provided
            if ($stockBatchId) {
                $batch = StockBatch::where('id', $stockBatchId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($isInbound) {
                    $batch->quantity_on_hand += $quantity;
                    if ($batch->status === 'exhausted') {
                        $batch->status = 'active';
                    }
                } else {
                    if ($batch->quantity_on_hand < $quantity) {
                        throw new \InvalidArgumentException(
                            "Insufficient stock in batch {$batch->batch_number}. Available: {$batch->quantity_on_hand}, Requested: {$quantity}"
                        );
                    }
                    $batch->quantity_on_hand -= $quantity;
                    if ($batch->quantity_on_hand <= 0) {
                        $batch->status = 'exhausted';
                    }
                }
                $batch->save();
            }

            // 4. Update StockLevel
            $balanceAfter = $isInbound ? ($balanceBefore + $quantity) : ($balanceBefore - $quantity);
            $stockLevel->quantity_on_hand = $balanceAfter;
            $stockLevel->updated_at = now();
            $stockLevel->save();

            // 5. Append immutable movement entry
            return StockMovement::create([
                'tenant_id' => $tenant->id,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'product_variant_id' => $productVariantId,
                'stock_batch_id' => $stockBatchId,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_at' => now(),
            ]);
        });
    }
}
