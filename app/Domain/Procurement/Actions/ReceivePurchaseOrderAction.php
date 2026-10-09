<?php

namespace App\Domain\Procurement\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Procurement\Models\PurchaseOrder;
use App\Domain\Procurement\Models\PurchaseOrderItem;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockLevel;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class ReceivePurchaseOrderAction
{
    public function __construct(
        protected RecordStockMovementAction $recordMovement,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $purchaseOrderId,
        array $receivedItems,
        ?string $userId = null
    ): PurchaseOrder {
        if (empty($receivedItems)) {
            throw new \InvalidArgumentException('No items provided for receipt.');
        }

        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        return DB::transaction(function () use ($tenant, $purchaseOrderId, $receivedItems, $userId) {
            $po = PurchaseOrder::where('id', $purchaseOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($po->status, ['received', 'cancelled'], true)) {
                throw new \InvalidArgumentException("Cannot receive goods for purchase order in status: {$po->status}.");
            }

            foreach ($receivedItems as $receipt) {
                $itemId = $receipt['item_id'];
                $qty = (float) $receipt['quantity'];

                if ($qty <= 0) {
                    continue;
                }

                $item = PurchaseOrderItem::where('id', $itemId)
                    ->where('purchase_order_id', $po->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 1. Batch management if batch_number specified
                $batchId = null;
                if (! empty($receipt['batch_number'])) {
                    $batch = StockBatch::firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'warehouse_id' => $po->warehouse_id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'batch_number' => $receipt['batch_number'],
                        ],
                        [
                            'cost_price' => $item->unit_cost,
                            'mfg_date' => $receipt['mfg_date'] ?? null,
                            'expiry_date' => $receipt['expiry_date'] ?? null,
                            'status' => 'active',
                        ]
                    );
                    $batchId = $batch->id;
                }

                // 2. Append stock movement & increment physical stock
                $this->recordMovement->execute(
                    warehouseId: $po->warehouse_id,
                    productId: $item->product_id,
                    productVariantId: $item->product_variant_id,
                    type: 'purchase_receipt',
                    quantity: $qty,
                    unitCost: (float) $item->unit_cost,
                    stockBatchId: $batchId,
                    userId: $userId ?? $po->user_id,
                    referenceType: PurchaseOrder::class,
                    referenceId: $po->id,
                    notes: "PO Receipt #{$po->po_number} from {$po->supplier->company_name}"
                );

                // 3. Update line item received quantity
                $item->quantity_received += $qty;
                $item->save();

                // 4. Update Product cost_price using Weighted Average Costing (WAC)
                $product = Product::find($item->product_id);
                if ($product) {
                    $totalStock = (float) StockLevel::where('product_id', $product->id)->sum('quantity_on_hand');
                    $currentCost = (float) $product->cost_price;

                    if ($totalStock > 0 && $totalStock > $qty) {
                        $previousStock = $totalStock - $qty;
                        $wacCost = (($previousStock * $currentCost) + ($qty * $item->unit_cost)) / $totalStock;
                        $product->cost_price = round($wacCost, 2);
                    } else {
                        $product->cost_price = round($item->unit_cost, 2);
                    }
                    $product->save();
                }
            }

            // 5. Check if all items are fully received
            $allOrdered = (float) $po->items()->sum('quantity_ordered');
            $allReceived = (float) $po->items()->sum('quantity_received');

            if ($allReceived >= $allOrdered) {
                $po->status = 'received';
                $po->received_at = now();
            } else {
                $po->status = 'partial_received';
            }
            $po->save();

            return $po->load(['items.product', 'supplier', 'warehouse']);
        });
    }
}
