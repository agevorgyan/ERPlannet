<?php

namespace Tests\Feature\Phase3;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\Manufacturing\Actions\CreateProductionOrderAction;
use App\Domain\Manufacturing\Actions\CreateRecipeAction;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionOrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Warehouse $warehouse;
    protected Product $matnakash;
    protected Product $flour;
    protected Product $yeast;
    protected Unit $kg;
    protected Unit $pcs;
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->where('code', 'WH-MAIN')->firstOrFail();

        // Ensure Growth plan (production enabled, iso22000 optional)
        $growth = \App\Domain\Billing\Models\Plan::where('code', 'growth')->firstOrFail();
        $sub = \App\Domain\Billing\Models\Subscription::where('tenant_id', $this->tenant->id)->first();
        if ($sub) {
            $sub->update(['plan_id' => $growth->id]);
        }

        app(\App\Infrastructure\MultiTenancy\TenantContext::class)->setCurrentTenant($this->tenant);

        $this->kg = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'KG'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'is_fractional' => true]
        );

        $this->pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'bakery'],
            ['name' => 'Bakery & Bread', 'is_active' => true]
        );

        $this->matnakash = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'BREAD-MATNAKASH-PRD'],
            [
                'name' => 'Tonir Matnakash Traditional',
                'category_id' => $cat->id,
                'unit_id' => $this->pcs->id,
                'price' => 300,
                'cost_price' => 150,
                'type' => 'manufactured',
                'shelf_life_days' => 3,
                'is_active' => true,
            ]
        );

        $this->flour = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'ING-FLOUR-PRD'],
            [
                'name' => 'Wheat Flour Premium Grade',
                'category_id' => $cat->id,
                'unit_id' => $this->kg->id,
                'price' => 450,
                'cost_price' => 320,
                'type' => 'raw_material',
                'is_active' => true,
            ]
        );

        $this->yeast = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'ING-YEAST-PRD'],
            [
                'name' => 'Baking Yeast Dry',
                'category_id' => $cat->id,
                'unit_id' => $this->kg->id,
                'price' => 2500,
                'cost_price' => 1800,
                'type' => 'raw_material',
                'is_active' => true,
            ]
        );

        // Preload stock for raw materials
        $movementAction = app(RecordStockMovementAction::class);
        $movementAction->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->flour->id,
            productVariantId: null,
            type: 'adjustment_plus',
            quantity: 500.0,
            unitCost: 320.0,
            userId: $this->user->id
        );

        $movementAction->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->yeast->id,
            productVariantId: null,
            type: 'adjustment_plus',
            quantity: 50.0,
            unitCost: 1800.0,
            userId: $this->user->id
        );

        // Create Recipe for 100 pcs
        $recipeAction = app(CreateRecipeAction::class);
        $this->recipe = $recipeAction->execute([
            'product_id' => $this->matnakash->id,
            'code' => 'RCP-MATNAKASH-AUTO',
            'name' => 'Automated Matnakash Recipe (100 pcs)',
            'yield_quantity' => 100.0,
            'yield_unit_id' => $this->pcs->id,
            'labor_cost' => 3000.0,
            'overhead_cost' => 1500.0,
            'scrap_percentage' => 1.0,
            'items' => [
                [
                    'product_id' => $this->flour->id,
                    'quantity' => 50.0, // 50kg per 100 loaves
                    'unit_id' => $this->kg->id,
                    'waste_percentage' => 2.0, // 2% waste -> 51kg
                ],
                [
                    'product_id' => $this->yeast->id,
                    'quantity' => 1.5, // 1.5kg per 100 loaves
                    'unit_id' => $this->kg->id,
                    'waste_percentage' => 0.0,
                ],
            ],
        ]);
    }

    public function test_can_create_production_order_and_scale_bom_quantities(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/production-orders', [
                'recipe_id' => $this->recipe->id,
                'planned_quantity' => 200.0, // Scale by factor of 2.0
                'source_warehouse_id' => $this->warehouse->id,
                'target_warehouse_id' => $this->warehouse->id,
                'notes' => 'Batch #1 Morning Production',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.planned_quantity', 200);

        $order = ProductionOrder::with('items')->find($response->json('data.id'));
        $this->assertNotNull($order);
        $this->assertStringStartsWith('PRD-', $order->order_number);
        $this->assertCount(2, $order->items);

        // Scaled flour: 50 * 2 * 1.02 = 102.0 kg
        $flourItem = $order->items->firstWhere('product_id', $this->flour->id);
        $this->assertEquals(102.0, (float) $flourItem->planned_quantity);

        // Scaled yeast: 1.5 * 2 * 1.0 = 3.0 kg
        $yeastItem = $order->items->firstWhere('product_id', $this->yeast->id);
        $this->assertEquals(3.0, (float) $yeastItem->planned_quantity);
    }

    public function test_start_order_deducts_raw_materials_from_source_warehouse(): void
    {
        $createAction = app(CreateProductionOrderAction::class);
        $order = $createAction->execute(
            recipeId: $this->recipe->id,
            plannedQuantity: 200.0,
            sourceWarehouseId: $this->warehouse->id,
            targetWarehouseId: $this->warehouse->id,
            userId: $this->user->id
        );

        $flourInitial = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->flour->id)
            ->value('quantity_on_hand');
        $this->assertEquals(500.0, (float) $flourInitial);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/production-orders/{$order->id}/start");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');

        // Verify stock deducted: 500 - 102 = 398 kg
        $flourAfter = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->flour->id)
            ->value('quantity_on_hand');
        $this->assertEquals(398.0, (float) $flourAfter);

        // Verify yeast deducted: 50 - 3 = 47 kg
        $yeastAfter = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->yeast->id)
            ->value('quantity_on_hand');
        $this->assertEquals(47.0, (float) $yeastAfter);
    }

    public function test_complete_order_yields_finished_goods_batch_and_updates_product_cost(): void
    {
        $createAction = app(CreateProductionOrderAction::class);
        $order = $createAction->execute(
            recipeId: $this->recipe->id,
            plannedQuantity: 100.0,
            sourceWarehouseId: $this->warehouse->id,
            targetWarehouseId: $this->warehouse->id,
            userId: $this->user->id
        );

        // Start order first
        $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/production-orders/{$order->id}/start")
            ->assertStatus(200);

        // Complete order
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/production-orders/{$order->id}/complete", [
                'actual_quantity' => 98.0, // 98 loaves yielded
                'waste_quantity' => 2.0,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.actual_quantity', 98);

        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->stock_batch_id);

        // Check Stock Batch
        $batch = $order->batch;
        $this->assertNotNull($batch);
        $this->assertEquals(98.0, (float) $batch->quantity_on_hand);
        // Expiry date should be mfg_date + 3 days
        $this->assertEquals(now()->addDays(3)->toDateString(), $batch->expiry_date->toDateString());

        // Check Finished goods stock level
        $breadStock = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->matnakash->id)
            ->value('quantity_on_hand');
        $this->assertEquals(98.0, (float) $breadStock);

        // Check Product cost_price was updated
        $this->matnakash->refresh();
        $this->assertGreaterThan(0, (float) $this->matnakash->cost_price);
    }

    public function test_can_cancel_draft_production_order(): void
    {
        $createAction = app(CreateProductionOrderAction::class);
        $order = $createAction->execute(
            recipeId: $this->recipe->id,
            plannedQuantity: 50.0,
            sourceWarehouseId: $this->warehouse->id,
            targetWarehouseId: $this->warehouse->id,
            userId: $this->user->id
        );

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/production-orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }
}
