<?php

namespace Tests\Feature\Phase2;

use App\Domain\Catalog\Models\Product;
use App\Domain\IAM\Models\User;
use App\Domain\Procurement\Models\PurchaseOrder;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementAndPurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Warehouse $warehouse;

    protected Product $product;

    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->where('code', 'WH-MAIN')->firstOrFail();
        $this->product = Product::where('tenant_id', $this->tenant->id)->where('sku', 'MEAT-BASTURMA')->firstOrFail();
        $this->supplier = Supplier::where('tenant_id', $this->tenant->id)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);
    }

    public function test_supplier_crud_and_tenant_isolation(): void
    {
        // Create supplier for gourmet tenant
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/suppliers', [
                'company_name' => 'New Regional Spices LLC',
                'tax_id' => '09887766',
                'phone' => '+37498112233',
                'payment_terms_days' => 30,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.company_name', 'New Regional Spices LLC');

        $supplierId = $response->json('data.id');

        // Other tenant cannot see it
        $tenantB = Tenant::create(['name' => 'Company B', 'slug' => 'company-b', 'subdomain' => 'company-b', 'status' => 'active']);
        $userB = User::create([
            'tenant_id' => $tenantB->id,
            'email' => 'user@b.am',
            'name' => 'User B',
            'password' => bcrypt('password'),
        ]);

        $responseB = $this->actingAs($userB)
            ->withHeader('X-Tenant-Slug', $tenantB->slug)
            ->getJson("/api/v1/suppliers/{$supplierId}");

        $responseB->assertStatus(404);
    }

    public function test_purchase_order_full_procurement_and_goods_receipt_lifecycle(): void
    {
        $initialOnHand = (float) (StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->value('quantity_on_hand') ?? 0.0);

        // 1. Create Purchase Order (Draft)
        $createRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/purchase-orders', [
                'supplier_id' => $this->supplier->id,
                'warehouse_id' => $this->warehouse->id,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity_ordered' => 20.0,
                        'unit_cost' => 4200.0,
                    ],
                ],
            ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.total', 84000);

        $poId = $createRes->json('data.id');
        $itemId = $createRes->json('data.items.0.id');
        $poNumber = $createRes->json('data.po_number');

        $this->assertStringStartsWith('PO-'.date('Y').'-', $poNumber);

        // 2. Submit PO to supplier -> ordered
        $submitRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/purchase-orders/{$poId}/submit");

        $submitRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ordered');

        // 3. Receive Goods at Warehouse with Batch & Expiry
        $receiveRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/purchase-orders/{$poId}/receive", [
                'items' => [
                    [
                        'item_id' => $itemId,
                        'quantity' => 20.0,
                        'batch_number' => 'LOT-2026-MEAT-NEW',
                        'mfg_date' => now()->toDateString(),
                        'expiry_date' => now()->addDays(90)->toDateString(),
                    ],
                ],
            ]);

        $receiveRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'received');

        // Verify physical stock incremented
        $afterOnHand = (float) StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->value('quantity_on_hand');
        $this->assertEquals($initialOnHand + 20.0, $afterOnHand);

        // Verify batch created
        $this->assertDatabaseHas('stock_batches', [
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-2026-MEAT-NEW',
            'quantity_on_hand' => 20.0,
        ]);

        // Verify double-entry stock movement
        $this->assertDatabaseHas('stock_movements', [
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'type' => 'purchase_receipt',
            'quantity' => 20.0,
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $poId,
        ]);
    }

    public function test_cannot_cancel_purchase_order_with_received_goods(): void
    {
        $po = PurchaseOrder::where('tenant_id', $this->tenant->id)->where('status', 'received')->firstOrFail();

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/purchase-orders/{$po->id}/cancel");

        $response->assertStatus(500); // InvalidArgumentException rendered
    }
}
