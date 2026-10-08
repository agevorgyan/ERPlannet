<?php

namespace App\Domain\Warehouse\Actions;

use App\Domain\Warehouse\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

class ReceiveStockTransferAction
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

            if ($transfer->status !== 'in_transit') {
                throw new \InvalidArgumentException("Transfer cannot be received from current status: {$transfer->status}.");
            }

            foreach ($transfer->items as $item) {
                $this->recordMovement->execute(
                    warehouseId: $transfer->destination_warehouse_id,
                    productId: $item->product_id,
                    productVariantId: $item->product_variant_id,
                    type: 'transfer_in',
                    quantity: (float) $item->quantity,
                    unitCost: 0.0,
                    stockBatchId: $item->stock_batch_id,
                    userId: $userId ?? $transfer->user_id,
                    referenceType: StockTransfer::class,
                    referenceId: $transfer->id,
                    notes: "Stock Transfer In from {$transfer->sourceWarehouse->name} (#{$transfer->transfer_number})"
                );
            }

            $transfer->status = 'completed';
            $transfer->received_at = now();
            $transfer->save();

            return $transfer;
        });
    }
}
