<?php

namespace App\Domain\Warehouse\Actions;

use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class AdjustStockAction
{
    public function __construct(
        protected RecordStockMovementAction $recordMovement
    ) {}

    public function execute(
        string $warehouseId,
        string $productId,
        ?string $productVariantId,
        float $countedQuantity,
        ?string $userId = null,
        ?string $notes = null,
        ?string $batchId = null
    ): ?StockMovement {
        if ($countedQuantity < 0) {
            throw new \InvalidArgumentException('Counted quantity cannot be negative.');
        }

        $stockLevel = StockLevel::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->when($productVariantId, fn ($q) => $q->where('product_variant_id', $productVariantId), fn ($q) => $q->whereNull('product_variant_id'))
            ->first();

        $currentOnHand = $stockLevel ? (float) $stockLevel->quantity_on_hand : 0.0;
        $difference = $countedQuantity - $currentOnHand;

        if (abs($difference) < 0.0001) {
            return null; // No difference, no adjustment needed
        }

        if ($difference > 0) {
            return $this->recordMovement->execute(
                warehouseId: $warehouseId,
                productId: $productId,
                productVariantId: $productVariantId,
                type: 'adjustment_plus',
                quantity: $difference,
                unitCost: 0.0,
                stockBatchId: $batchId,
                userId: $userId,
                referenceType: 'inventory_count',
                referenceId: null,
                notes: $notes ?? "Physical stock audit adjustment (+{$difference})"
            );
        }

        return $this->recordMovement->execute(
            warehouseId: $warehouseId,
            productId: $productId,
            productVariantId: $productVariantId,
            type: 'adjustment_minus',
            quantity: abs($difference),
            unitCost: 0.0,
            stockBatchId: $batchId,
            userId: $userId,
            referenceType: 'inventory_count',
            referenceId: null,
            notes: $notes ?? "Physical stock audit adjustment (-" . abs($difference) . ")"
        );
    }
}
