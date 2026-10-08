<?php

namespace App\Domain\Warehouse\Actions;

use App\Domain\Warehouse\Models\StockLevel;
use Illuminate\Support\Facades\DB;

class ReleaseStockAction
{
    public function execute(
        string $warehouseId,
        string $productId,
        ?string $productVariantId,
        float $quantity
    ): StockLevel {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Release quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($warehouseId, $productId, $productVariantId, $quantity) {
            $stockLevel = StockLevel::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->when($productVariantId, fn ($q) => $q->where('product_variant_id', $productVariantId), fn ($q) => $q->whereNull('product_variant_id'))
                ->lockForUpdate()
                ->firstOrFail();

            $stockLevel->quantity_reserved = max(0.0, (float) $stockLevel->quantity_reserved - $quantity);
            $stockLevel->updated_at = now();
            $stockLevel->save();

            return $stockLevel;
        });
    }
}
