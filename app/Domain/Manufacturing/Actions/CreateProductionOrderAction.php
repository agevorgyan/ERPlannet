<?php

namespace App\Domain\Manufacturing\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Manufacturing\Models\ProductionOrderItem;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Manufacturing\Services\ProductionOrderNumberGenerator;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateProductionOrderAction
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected ProductionOrderNumberGenerator $numberGenerator,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $recipeId,
        float $plannedQuantity,
        string $sourceWarehouseId,
        string $targetWarehouseId,
        string $userId,
        ?string $plannedStartDate = null,
        ?string $notes = null
    ): ProductionOrder {
        if ($plannedQuantity <= 0) {
            throw new \InvalidArgumentException('Planned quantity must be greater than zero.');
        }

        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        // 1. Entitlements
        $this->entitlements->assertCan('feature.production');
        $this->entitlements->consume('limit.production_orders_monthly', 1);

        $recipe = Recipe::with('items.product')->findOrFail($recipeId);
        $sourceWarehouse = Warehouse::findOrFail($sourceWarehouseId);
        $targetWarehouse = Warehouse::findOrFail($targetWarehouseId);

        return DB::transaction(function () use (
            $tenant,
            $recipe,
            $plannedQuantity,
            $sourceWarehouse,
            $targetWarehouse,
            $userId,
            $plannedStartDate,
            $notes
        ) {
            $orderNumber = $this->numberGenerator->generate($tenant);

            $scalingFactor = $plannedQuantity / max(0.0001, (float) $recipe->yield_quantity);

            $order = ProductionOrder::create([
                'tenant_id' => $tenant->id,
                'order_number' => $orderNumber,
                'recipe_id' => $recipe->id,
                'product_id' => $recipe->product_id,
                'product_variant_id' => $recipe->product_variant_id,
                'source_warehouse_id' => $sourceWarehouse->id,
                'target_warehouse_id' => $targetWarehouse->id,
                'user_id' => $userId,
                'status' => 'draft',
                'planned_quantity' => $plannedQuantity,
                'actual_quantity' => 0.0,
                'waste_quantity' => 0.0,
                'unit_cost' => 0.0,
                'total_cost' => 0.0,
                'planned_start_date' => $plannedStartDate ?? now()->toDateString(),
                'notes' => $notes,
            ]);

            foreach ($recipe->items as $item) {
                $requiredQty = round((float) $item->quantity * $scalingFactor * (1 + (float) $item->waste_percentage / 100), 4);
                $unitCost = (float) $item->product->cost_price;
                $totalCost = round($requiredQty * $unitCost, 4);

                ProductionOrderItem::create([
                    'tenant_id' => $tenant->id,
                    'production_order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'planned_quantity' => $requiredQty,
                    'consumed_quantity' => 0.0,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'created_at' => now(),
                ]);
            }

            return $order->load(['items.product.unit', 'recipe', 'product', 'sourceWarehouse', 'targetWarehouse']);
        });
    }
}
