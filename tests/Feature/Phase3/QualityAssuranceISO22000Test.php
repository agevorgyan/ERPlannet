<?php

namespace Tests\Feature\Phase3;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\Manufacturing\Actions\CreateProductionOrderAction;
use App\Domain\Manufacturing\Actions\CreateRecipeAction;
use App\Domain\Manufacturing\Actions\StartProductionOrderAction;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Quality\Actions\RecordQualityInspectionAction;
use App\Domain\Quality\Models\QualityInspection;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityAssuranceISO22000Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Warehouse $warehouse;
    protected Product $cheese;
    protected Product $milk;
    protected Unit $kg;
    protected Unit $liter;
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->where('code', 'WH-MAIN')->firstOrFail();

        // Switch Gourmet to Enterprise Plan to enable ISO 22000 feature
        $enterprise = Plan::where('code', 'enterprise')->firstOrFail();
        $sub = Subscription::where('tenant_id', $this->tenant->id)->first();
        if ($sub) {
            $sub->update(['plan_id' => $enterprise->id]);
        }

        app(\App\Infrastructure\MultiTenancy\TenantContext::class)->setCurrentTenant($this->tenant);

        $this->kg = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'KG'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'is_fractional' => true]
        );

        $this->liter = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'L'],
            ['name' => 'Liter', 'symbol' => 'L', 'is_fractional' => true]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'dairy'],
            ['name' => 'Dairy & Cheese', 'is_active' => true]
        );

        $this->cheese = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'CHEESE-LORI-ISO'],
            [
                'name' => 'Lori Cheese Aged 60 Days',
                'category_id' => $cat->id,
                'unit_id' => $this->kg->id,
                'price' => 3800,
                'cost_price' => 2400,
                'type' => 'manufactured',
                'shelf_life_days' => 90,
                'is_active' => true,
            ]
        );

        $this->milk = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'ING-MILK-RAW'],
            [
                'name' => 'Raw Cow Milk Grade A',
                'category_id' => $cat->id,
                'unit_id' => $this->liter->id,
                'price' => 280,
                'cost_price' => 220,
                'type' => 'raw_material',
                'is_active' => true,
            ]
        );

        // Preload milk stock
        $movementAction = app(RecordStockMovementAction::class);
        $movementAction->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->milk->id,
            productVariantId: null,
            type: 'adjustment_plus',
            quantity: 1000.0,
            unitCost: 220.0,
            userId: $this->user->id
        );

        // Create recipe for 10 kg Lori Cheese (yields 10 kg from 100 L milk)
        $recipeAction = app(CreateRecipeAction::class);
        $this->recipe = $recipeAction->execute([
            'product_id' => $this->cheese->id,
            'code' => 'RCP-LORI-CHEESE',
            'name' => 'Traditional Lori Cheese Recipe',
            'yield_quantity' => 10.0,
            'yield_unit_id' => $this->kg->id,
            'labor_cost' => 4000.0,
            'overhead_cost' => 2000.0,
            'items' => [
                [
                    'product_id' => $this->milk->id,
                    'quantity' => 100.0,
                    'unit_id' => $this->liter->id,
                    'waste_percentage' => 0.0,
                ],
            ],
        ]);
    }

    public function test_can_record_passed_quality_inspection_with_ccp_parameters(): void
    {
        $order = app(CreateProductionOrderAction::class)->execute(
            recipeId: $this->recipe->id,
            plannedQuantity: 10.0,
            sourceWarehouseId: $this->warehouse->id,
            targetWarehouseId: $this->warehouse->id,
            userId: $this->user->id
        );

        app(StartProductionOrderAction::class)->execute($order->id, $this->user->id);

        $action = app(RecordQualityInspectionAction::class);
        $inspection = $action->execute(
            productionOrderId: $order->id,
            inspectorId: $this->user->id,
            items: [
                [
                    'parameter_name' => 'Pasteurization Temperature',
                    'critical_control_point' => 'CCP-1',
                    'target_value' => '72.0',
                    'min_value' => 71.5,
                    'max_value' => 74.0,
                    'actual_value' => '72.5',
                    'unit' => '°C',
                ],
                [
                    'parameter_name' => 'Acidity Level (pH)',
                    'critical_control_point' => 'CCP-2',
                    'target_value' => '5.2',
                    'min_value' => 5.0,
                    'max_value' => 5.4,
                    'actual_value' => '5.25',
                    'unit' => 'pH',
                ],
            ],
            standardApplied: 'ISO 22000:2018',
            notes: 'Batch meets all HACCP microbiological and physical criteria.'
        );

        $this->assertInstanceOf(QualityInspection::class, $inspection);
        $this->assertEquals('passed', $inspection->status);
        $this->assertEquals(100.0, (float) $inspection->overall_score);
        $this->assertCount(2, $inspection->items);

        $order->refresh();
        $this->assertEquals('quality_check', $order->status);
    }

    public function test_inspection_fails_when_ccp_is_outside_tolerance_limits(): void
    {
        $order = app(CreateProductionOrderAction::class)->execute(
            recipeId: $this->recipe->id,
            plannedQuantity: 10.0,
            sourceWarehouseId: $this->warehouse->id,
            targetWarehouseId: $this->warehouse->id,
            userId: $this->user->id
        );

        app(StartProductionOrderAction::class)->execute($order->id, $this->user->id);

        $action = app(RecordQualityInspectionAction::class);
        $inspection = $action->execute(
            productionOrderId: $order->id,
            inspectorId: $this->user->id,
            items: [
                [
                    'parameter_name' => 'Pasteurization Temperature',
                    'critical_control_point' => 'CCP-1',
                    'target_value' => '72.0',
                    'min_value' => 71.5,
                    'max_value' => 74.0,
                    'actual_value' => '67.0', // Fails! Dangerously under-heated
                    'unit' => '°C',
                    'deviation_notes' => 'Heat exchanger temperature drop detected',
                ],
            ],
            standardApplied: 'ISO 22000:2018'
        );

        $this->assertEquals('failed', $inspection->status);
        $this->assertEquals(0.0, (float) $inspection->overall_score);

        $order->refresh();
        $this->assertEquals('rejected', $order->status);
    }

    public function test_iso22000_gating_blocks_completion_without_passed_inspection(): void
    {
        $order = app(CreateProductionOrderAction::class)->execute(
            recipeId: $this->recipe->id,
            plannedQuantity: 10.0,
            sourceWarehouseId: $this->warehouse->id,
            targetWarehouseId: $this->warehouse->id,
            userId: $this->user->id
        );

        app(StartProductionOrderAction::class)->execute($order->id, $this->user->id);

        // Attempt to complete WITHOUT QA inspection on Enterprise plan (with feature.iso22000 = true)
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/production-orders/{$order->id}/complete", [
                'actual_quantity' => 10.0,
            ]);

        // Expect 400/422/500 due to ISO 22000 gating failure
        $response->assertStatus(500);

        // Now record PASSED QA inspection via REST API
        $qaResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/quality-inspections', [
                'production_order_id' => $order->id,
                'standard_applied' => 'ISO 22000:2018',
                'items' => [
                    [
                        'parameter_name' => 'Pasteurization Temperature',
                        'critical_control_point' => 'CCP-1',
                        'target_value' => '72.0',
                        'min_value' => 71.5,
                        'max_value' => 74.0,
                        'actual_value' => '72.2',
                        'unit' => '°C',
                    ],
                ],
            ]);

        $qaResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'passed');

        // Now completion must succeed!
        $completeResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/production-orders/{$order->id}/complete", [
                'actual_quantity' => 10.0,
            ]);

        $completeResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.actual_quantity', 10);
    }
}
