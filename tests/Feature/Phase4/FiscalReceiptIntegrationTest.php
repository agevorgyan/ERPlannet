<?php

namespace Tests\Feature\Phase4;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Fiscal\Contracts\FiscalProviderInterface;
use App\Domain\Fiscal\FiscalProviderManager;
use App\Domain\Fiscal\Models\FiscalReceipt;
use App\Domain\IAM\Models\User;
use App\Domain\POS\Actions\OpenPosSessionAction;
use App\Domain\POS\Actions\PosCheckoutAction;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiscalReceiptIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected PosTerminal $terminal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $branch = Branch::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'BR-FISC'],
            ['name' => 'Fiscal Branch', 'is_active' => true, 'is_headquarters' => false]
        );

        $warehouse = Warehouse::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'WH-FISC'],
            ['branch_id' => $branch->id, 'name' => 'Fiscal Warehouse', 'is_active' => true]
        );

        $this->terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'code' => 'POS-FISC-01',
            'name' => 'Fiscal Desk 01',
            'device_uid' => 'CRN-ARM-998877',
            'is_active' => true,
        ]);
    }

    public function test_fiscal_provider_interface_abstraction_lifecycle(): void
    {
        $manager = app(FiscalProviderManager::class);
        $provider = $manager->provider();

        $this->assertInstanceOf(FiscalProviderInterface::class, $provider);
        $this->assertEquals('mock_armenia_src', $provider->getIdentifier());

        // Test cancel, refund and getStatus
        $statusResult = $provider->getStatus('dummy-fiscal-id');
        $this->assertTrue($statusResult->success);
        $this->assertEquals('registered', $statusResult->status);

        $refundResult = $provider->refundReceipt('dummy-fiscal-id', 2500.0);
        $this->assertTrue($refundResult->success);
        $this->assertEquals('refunded', $refundResult->status);
        $this->assertStringStartsWith('SRC-REF-', $refundResult->fiscalNumber);

        $cancelResult = $provider->cancelReceipt('dummy-fiscal-id', ['reason' => 'Test void']);
        $this->assertTrue($cancelResult->success);
        $this->assertEquals('cancelled', $cancelResult->status);
    }

    public function test_pos_checkout_automatically_generates_decoupled_fiscal_receipt(): void
    {
        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $this->terminal->id,
            cashierId: $this->user->id,
            openingCash: 5000.0
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'fisc-cat'],
            ['name' => 'Fiscal Goods', 'is_active' => true]
        );

        $product = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'FISC-COFFEE'],
            [
                'name' => ['hy' => 'Armenian Coffee'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 800,
                'cost_price' => 300,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        app(RecordStockMovementAction::class)->execute(
            warehouseId: $this->terminal->warehouse_id,
            productId: $product->id,
            productVariantId: null,
            type: 'adjustment_plus',
            quantity: 10.0,
            unitCost: 300.0,
            referenceType: 'test_seed',
            referenceId: (string) \Illuminate\Support\Str::uuid(),
            notes: 'Stock for fiscal test'
        );

        $order = app(PosCheckoutAction::class)->execute(
            posSessionId: $session->id,
            items: [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 800],
            ],
            payments: [
                ['gateway' => 'cash', 'method' => 'cash', 'amount' => 1600],
            ]
        );

        // Internal receipt number vs Fiscal receipt number
        $this->assertNotEmpty($order->receipt_number);
        $this->assertStringStartsWith('REC-', $order->receipt_number);

        // Verify Fiscal Receipt record
        $fiscalReceipt = FiscalReceipt::where('order_id', $order->id)->first();
        $this->assertNotNull($fiscalReceipt);
        $this->assertEquals('mock_armenia_src', $fiscalReceipt->provider);
        $this->assertStringStartsWith('SRC-', $fiscalReceipt->fiscal_number);
        $this->assertEquals('CRN-ARM-998877', $fiscalReceipt->crn);
        $this->assertEquals('1600.00', $fiscalReceipt->total_amount);
        $this->assertEquals('registered', $fiscalReceipt->status);

        // Verify QR Payload contains Armenian tax register compliance keys
        $qrDecoded = json_decode($fiscalReceipt->qr_payload, true);
        $this->assertIsArray($qrDecoded);
        $this->assertArrayHasKey('tin', $qrDecoded);
        $this->assertArrayHasKey('crn', $qrDecoded);
        $this->assertArrayHasKey('fisc', $qrDecoded);
        $this->assertArrayHasKey('sec', $qrDecoded);
    }
}
