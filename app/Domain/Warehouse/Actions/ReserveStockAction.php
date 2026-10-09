<?php

namespace App\Domain\Warehouse\Actions;

use App\Domain\Warehouse\Models\StockLevel;
use Illuminate\Support\Facades\DB;

class ReserveStockAction
{
    public function execute(
        string $warehouseId,
        string $productId,
        ?string $productVariantId,
        float $quantity
    ): StockLevel {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Reservation quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($warehouseId, $productId, $productVariantId, $quantity) {
            $stockLevel = StockLevel::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->when($productVariantId, fn ($q) => $q->where('product_variant_id', $productVariantId), fn ($q) => $q->whereNull('product_variant_id'))
                ->lockForUpdate()
                ->first();

            if (! $stockLevel) {
                throw new \InvalidArgumentException('No stock record exists for this item in the specified warehouse.');
            }

            $available = (float) $stockLevel->quantity_on_hand - (float) $stockLevel->quantity_reserved;
            if ($available < $quantity) {
                throw new \InvalidArgumentException(
                    "Insufficient available stock to reserve. Available: {$available}, Requested: {$quantity}"
                );
            }

            $stockLevel->quantity_reserved += $quantity;
            $stockLevel->updated_at = now();
            $stockLevel->save();

            return $stockLevel;
        });
    }
}
