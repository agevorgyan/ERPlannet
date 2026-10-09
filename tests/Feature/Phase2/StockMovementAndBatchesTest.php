<?php

namespace Tests\Feature\Phase2;

use App\Domain\Catalog\Models\Product;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Actions\ReleaseStockAction;
use App\Domain\Warehouse\Actions\ReserveStockAction;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementAndBatchesTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Warehouse $warehouse;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->where('code', 'WH-MAIN')->firstOrFail();
        $this->product = Product::where('tenant_id', $this->tenant->id)->where('sku', 'CHEESE-LORI')->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);
    }

    public function test_inbound_movement_increments_stock_level_and_creates_immutable_log(): void
    {
        $action = app(RecordStockMovementAction::class);

        $initialLevel = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();
        $initialOnHand = $initialLevel ? (float) $initialLevel->quantity_on_hand : 0.0;

        $movement = $action->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'purchase_receipt',
            quantity: 25.5,
            unitCost: 2200.0,
            userId: $this->user->id,
            notes: 'Test Inbound Delivery'
        );

        $this->assertEquals($initialOnHand, $movement->balance_before);
        $this->assertEquals($initialOnHand + 25.5, $movement->balance_after);
        $this->assertEquals(25.5, $movement->quantity);
        $this->assertEquals('purchase_receipt', $movement->type);

        $this->assertDatabaseHas('stock_levels', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity_on_hand' => $initialOnHand + 25.5,
        ]);
    }

    public function test_outbound_movement_decrements_stock_and_prevents_negative_balance(): void
    {
        $action = app(RecordStockMovementAction::class);

        // 1. Initial Inbound
        $action->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'purchase_receipt',
            quantity: 10.0,
            userId: $this->user->id
        );

        $currentLevel = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->firstOrFail();
        $beforeDeduction = (float) $currentLevel->quantity_on_hand;

        // 2. Valid Outbound deduction
        $outMovement = $action->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'sale_delivery',
            quantity: 4.0,
            userId: $this->user->id
        );

        $this->assertEquals($beforeDeduction, $outMovement->balance_before);
        $this->assertEquals($beforeDeduction - 4.0, $outMovement->balance_after);

        // 3. Attempting to withdraw more than available throws InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient physical stock in warehouse');

        $action->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'sale_delivery',
            quantity: 9999.0,
            userId: $this->user->id
        );
    }

    public function test_batch_quantity_tracking_and_expiry_lifecycle(): void
    {
        $action = app(RecordStockMovementAction::class);

        $batch = StockBatch::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-TEST-999',
            'quantity_on_hand' => 0.0,
            'cost_price' => 2100.0,
            'mfg_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(45)->toDateString(),
            'status' => 'active',
        ]);

        // Inbound with batch
        $action->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'purchase_receipt',
            quantity: 50.0,
            unitCost: 2100.0,
            stockBatchId: $batch->id,
            userId: $this->user->id
        );

        $batch->refresh();
        $this->assertEquals(50.0, $batch->quantity_on_hand);
        $this->assertEquals('active', $batch->status);

        // Outbound exact quantity with batch
        $action->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'sale_delivery',
            quantity: 50.0,
            stockBatchId: $batch->id,
            userId: $this->user->id
        );

        $batch->refresh();
        $this->assertEquals(0.0, $batch->quantity_on_hand);
        $this->assertEquals('exhausted', $batch->status);
    }

    public function test_stock_reservation_and_release_lifecycle(): void
    {
        $movementAction = app(RecordStockMovementAction::class);
        $reserveAction = app(ReserveStockAction::class);
        $releaseAction = app(ReleaseStockAction::class);

        // Add 20 units
        $movementAction->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'purchase_receipt',
            quantity: 20.0,
            userId: $this->user->id
        );

        // Reserve 8 units
        $level = $reserveAction->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            quantity: 8.0
        );

        $this->assertEquals(8.0, $level->quantity_reserved);
        $this->assertEquals(12.0, (float) $level->quantity_on_hand - (float) $level->quantity_reserved);

        // Release 3 units
        $level = $releaseAction->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            quantity: 3.0
        );

        $this->assertEquals(5.0, $level->quantity_reserved);
    }

    public function test_stock_adjustment_via_api_creates_audit_entries(): void
    {
        $currentLevel = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();
        $targetQty = ($currentLevel ? (float) $currentLevel->quantity_on_hand : 0.0) + 15.0;

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/inventory/adjust', [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'counted_quantity' => $targetQty,
                'notes' => 'Physical Inventory Audit 2026',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'adjustment_plus')
            ->assertJsonPath('data.quantity', 15);

        $this->assertDatabaseHas('stock_levels', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity_on_hand' => $targetQty,
        ]);
    }

    public function test_stock_scrap_via_api_creates_scrap_audit_movement(): void
    {
        // Ensure initial stock
        $action = app(RecordStockMovementAction::class);
        $action->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'purchase_receipt',
            quantity: 20.0,
            unitCost: 1000.0,
            userId: $this->user->id
        );

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/inventory/scrap', [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'quantity' => 4.0,
                'reason' => 'kitchen_waste',
                'notes' => 'Damaged during prep work',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'scrap')
            ->assertJsonPath('data.quantity', 4);

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'type' => 'scrap',
            'quantity' => 4.0,
        ]);
    }
}
