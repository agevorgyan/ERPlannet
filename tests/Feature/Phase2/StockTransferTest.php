<?php

namespace Tests\Feature\Phase2;

use App\Domain\Catalog\Models\Product;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\StockTransfer;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Warehouse $sourceWh;
    protected Warehouse $destWh;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->sourceWh = Warehouse::where('tenant_id', $this->tenant->id)->where('code', 'WH-MAIN')->firstOrFail();
        $this->destWh = Warehouse::where('tenant_id', $this->tenant->id)->where('code', 'WH-COLD')->firstOrFail();
        $this->product = Product::where('tenant_id', $this->tenant->id)->where('sku', 'BREAD-MATNAKASH')->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);
    }

    public function test_create_stock_transfer_creates_draft_with_sequential_number(): void
    {
        $payload = [
            'source_warehouse_id' => $this->sourceWh->id,
            'destination_warehouse_id' => $this->destWh->id,
            'notes' => 'Transferring bread to cold dispatch',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 15.0,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/inventory/transfers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'draft');

        $transferNumber = $response->json('data.transfer_number');
        $this->assertStringStartsWith('TRF-' . date('Y') . '-', $transferNumber);
    }

    public function test_full_two_step_stock_transfer_lifecycle(): void
    {
        // 1. Ensure source warehouse has enough stock
        app(RecordStockMovementAction::class)->execute(
            warehouseId: $this->sourceWh->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'purchase_receipt',
            quantity: 50.0,
            userId: $this->user->id
        );

        $srcInitial = (float) StockLevel::where('warehouse_id', $this->sourceWh->id)
            ->where('product_id', $this->product->id)
            ->value('quantity_on_hand');

        $destInitial = (float) (StockLevel::where('warehouse_id', $this->destWh->id)
            ->where('product_id', $this->product->id)
            ->value('quantity_on_hand') ?? 0.0);

        // 2. Create Draft Transfer
        $createRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/inventory/transfers', [
                'source_warehouse_id' => $this->sourceWh->id,
                'destination_warehouse_id' => $this->destWh->id,
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 20.0],
                ],
            ]);

        $transferId = $createRes->json('data.id');

        // 3. Dispatch / Ship goods -> in_transit
        $shipRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/inventory/transfers/{$transferId}/ship");

        $shipRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'in_transit');

        // Source warehouse stock must have decreased by 20
        $srcAfterShip = (float) StockLevel::where('warehouse_id', $this->sourceWh->id)
            ->where('product_id', $this->product->id)
            ->value('quantity_on_hand');
        $this->assertEquals($srcInitial - 20.0, $srcAfterShip);

        // Destination warehouse stock must not have changed yet
        $destAfterShip = (float) (StockLevel::where('warehouse_id', $this->destWh->id)
            ->where('product_id', $this->product->id)
            ->value('quantity_on_hand') ?? 0.0);
        $this->assertEquals($destInitial, $destAfterShip);

        // 4. Receive goods -> completed
        $receiveRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/inventory/transfers/{$transferId}/receive");

        $receiveRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'completed');

        // Destination warehouse stock must now have increased by 20
        $destAfterReceive = (float) StockLevel::where('warehouse_id', $this->destWh->id)
            ->where('product_id', $this->product->id)
            ->value('quantity_on_hand');
        $this->assertEquals($destInitial + 20.0, $destAfterReceive);
    }

    public function test_cannot_transfer_to_same_warehouse(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/inventory/transfers', [
                'source_warehouse_id' => $this->sourceWh->id,
                'destination_warehouse_id' => $this->sourceWh->id,
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 5.0],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_warehouse_id']);
    }
}
