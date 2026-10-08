<?php

namespace App\Domain\Warehouse\Actions;

use App\Domain\Warehouse\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

class ShipStockTransferAction
{
    public function __construct(
        protected RecordStockMovementAction $recordMovement
    ) {}

    public function execute(string $transferId, ?string $userId = null): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $userId) {
            $transfer = StockTransfer::where('id', $transferId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transfer->status !== 'draft') {
                throw new \InvalidArgumentException("Transfer cannot be shipped from current status: {$transfer->status}.");
            }

            foreach ($transfer->items as $item) {
                $this->recordMovement->execute(
                    warehouseId: $transfer->source_warehouse_id,
                    productId: $item->product_id,
                    productVariantId: $item->product_variant_id,
                    type: 'transfer_out',
                    quantity: (float) $item->quantity,
                    unitCost: 0.0,
                    stockBatchId: $item->stock_batch_id,
                    userId: $userId ?? $transfer->user_id,
                    referenceType: StockTransfer::class,
                    referenceId: $transfer->id,
                    notes: "Stock Transfer Out to {$transfer->destinationWarehouse->name} (#{$transfer->transfer_number})"
                );
            }

            $transfer->status = 'in_transit';
            $transfer->shipped_at = now();
            $transfer->save();

            return $transfer;
        });
    }
}
