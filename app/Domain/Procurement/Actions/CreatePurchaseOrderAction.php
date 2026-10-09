<?php

namespace App\Domain\Procurement\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Procurement\Models\PurchaseOrder;
use App\Domain\Procurement\Models\PurchaseOrderItem;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Procurement\Services\PurchaseOrderNumberGenerator;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrderAction
{
    public function __construct(
        protected PurchaseOrderNumberGenerator $numberGenerator,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $supplierId,
        string $warehouseId,
        string $userId,
        array $items,
        ?string $orderDate = null,
        ?string $expectedDeliveryDate = null,
        float $taxAmount = 0.0,
        ?string $notes = null
    ): PurchaseOrder {
        if (empty($items)) {
            throw new \InvalidArgumentException('Purchase order must contain at least one line item.');
        }

        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        // Verify supplier & warehouse exist in tenant
        $supplier = Supplier::findOrFail($supplierId);
        $warehouse = Warehouse::findOrFail($warehouseId);

        return DB::transaction(function () use (
            $tenant,
            $supplier,
            $warehouse,
            $userId,
            $items,
            $orderDate,
            $expectedDeliveryDate,
            $taxAmount,
            $notes
        ) {
            $poNumber = $this->numberGenerator->generate($tenant);

            $subtotal = 0.0;
            $preparedItems = [];

            foreach ($items as $itemData) {
                $qty = (float) $itemData['quantity_ordered'];
                $unitCost = (float) $itemData['unit_cost'];
                if ($qty <= 0 || $unitCost < 0) {
                    throw new \InvalidArgumentException('Item quantity must be > 0 and unit cost >= 0.');
                }

                $product = Product::findOrFail($itemData['product_id']);
                $itemTotal = round($qty * $unitCost, 4);
                $subtotal += $itemTotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $itemData['product_variant_id'] ?? null,
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0.0,
                    'unit_cost' => $unitCost,
                    'total' => $itemTotal,
                ];
            }

            $total = round($subtotal + $taxAmount, 4);

            $po = PurchaseOrder::create([
                'tenant_id' => $tenant->id,
                'po_number' => $poNumber,
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $userId,
                'status' => 'draft',
                'order_date' => $orderDate ?? now()->toDateString(),
                'expected_delivery_date' => $expectedDeliveryDate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'currency' => $supplier->currency ?? $tenant->currency ?? 'AMD',
                'payment_status' => 'unpaid',
                'notes' => $notes,
            ]);

            foreach ($preparedItems as $item) {
                PurchaseOrderItem::create(array_merge($item, [
                    'tenant_id' => $tenant->id,
                    'purchase_order_id' => $po->id,
                ]));
            }

            return $po->load(['items.product', 'supplier', 'warehouse']);
        });
    }
}
