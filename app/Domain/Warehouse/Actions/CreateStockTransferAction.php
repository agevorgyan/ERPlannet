<?php

namespace App\Domain\Warehouse\Actions;

use App\Domain\Warehouse\Models\StockTransfer;
use App\Domain\Warehouse\Models\StockTransferItem;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateStockTransferAction
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $sourceWarehouseId,
        string $destinationWarehouseId,
        string $userId,
        array $items,
        ?string $notes = null
    ): StockTransfer {
        if ($sourceWarehouseId === $destinationWarehouseId) {
            throw new \InvalidArgumentException('Source and destination warehouses must be different.');
        }

        if (empty($items)) {
            throw new \InvalidArgumentException('Transfer must contain at least one item.');
        }

        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        return DB::transaction(function () use (
            $tenant,
            $sourceWarehouseId,
            $destinationWarehouseId,
            $userId,
            $items,
            $notes
        ) {
            $year = date('Y');
            $prefix = "TRF-{$year}-";

            $latestTransfer = StockTransfer::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('transfer_number', 'like', "{$prefix}%")
                ->orderByDesc('transfer_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latestTransfer) {
                $lastNum = (int) substr($latestTransfer->transfer_number, strlen($prefix));
                $nextSequence = $lastNum + 1;
            }

            $transferNumber = $prefix.str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);

            $transfer = StockTransfer::create([
                'tenant_id' => $tenant->id,
                'transfer_number' => $transferNumber,
                'source_warehouse_id' => $sourceWarehouseId,
                'destination_warehouse_id' => $destinationWarehouseId,
                'status' => 'draft',
                'user_id' => $userId,
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                StockTransferItem::create([
                    'tenant_id' => $tenant->id,
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'stock_batch_id' => $item['stock_batch_id'] ?? null,
                    'quantity' => (float) $item['quantity'],
                    'created_at' => now(),
                ]);
            }

            return $transfer->load(['items.product', 'sourceWarehouse', 'destinationWarehouse']);
        });
    }
}
