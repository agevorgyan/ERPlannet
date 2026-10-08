<?php

namespace Tests\Feature\Phase4;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\Delivery\Actions\AssignDeliveryDriverAction;
use App\Domain\Delivery\Actions\CompleteDeliveryAction;
use App\Domain\Delivery\Actions\CreateDeliveryShipmentAction;
use App\Domain\Delivery\Actions\DispatchShipmentAction;
use App\Domain\Delivery\Actions\SettleCourierCodAction;
use App\Domain\Delivery\Models\CodSettlement;
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\IAM\Models\User;
use App\Domain\POS\Actions\OpenPosSessionAction;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodDiscrepancyAndSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected DeliveryDriver $driver;
    protected Order $order;
    protected PosTerminal $terminal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $branch = Branch::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'BR-COD-HUB'],
            ['name' => 'COD Hub Branch', 'is_active' => true, 'is_headquarters' => false]
        );

        $warehouse = Warehouse::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'WH-COD-HUB'],
            ['branch_id' => $branch->id, 'name' => 'COD Warehouse', 'is_active' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'cod-goods'],
            ['name' => 'COD Goods', 'is_active' => true]
        );

        $product = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'COD-PRD-01'],
            [
                'name' => ['hy' => 'COD Catering Box'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 20000,
                'cost_price' => 8000,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Gagik',
            'last_name' => 'Ghazaryan',
            'phone' => '+37493556677',
        ]);

        $this->order = app(CreateOrderAction::class)->execute([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'source' => 'direct',
            'delivery_type' => 'delivery',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $this->driver = DeliveryDriver::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Tigran',
            'last_name' => 'Tigranyan',
            'phone' => '+37494889900',
            'vehicle_type' => 'van',
            'license_plate' => '88TT888',
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'code' => 'POS-COD-DESK',
            'name' => 'COD Reconciliation Desk',
            'is_active' => true,
        ]);
    }

    public function test_full_cod_lifecycle_with_discrepancy_and_settlement(): void
    {
        // 1. Create Shipment: COD status is 'expected'
        $shipment = app(CreateDeliveryShipmentAction::class)->execute(
            orderId: $this->order->id,
            deliveryAddress: 'Baghramyan 26, Yerevan',
            recipientName: 'Gagik Ghazaryan',
            recipientPhone: '+37493556677',
            codAmount: 20000.0
        );

        $settlement = CodSettlement::where('delivery_shipment_id', $shipment->id)->firstOrFail();
        $this->assertEquals('expected', $settlement->status);
        $this->assertEquals(20000.0, (float) $settlement->expected_amount);

        // 2. Assign Driver & Dispatch
        app(AssignDeliveryDriverAction::class)->execute($shipment->id, $this->driver->id);
        app(DispatchShipmentAction::class)->execute($shipment->id);

        // 3. Courier collects 18,000 AMD instead of expected 20,000 AMD (-2,000 AMD discrepancy)
        app(CompleteDeliveryAction::class)->execute(
            shipmentId: $shipment->id,
            receivedByName: 'Gagik Ghazaryan',
            codCollected: 18000.0,
            notes: 'Client had 2,000 AMD short in cash'
        );

        $settlement->refresh();
        $this->assertEquals('courier_holding', $settlement->status);
        $this->assertEquals(18000.0, (float) $settlement->collected_amount);
        $this->assertEquals(-2000.0, (float) $settlement->discrepancy_amount);
        $this->assertNotNull($settlement->discrepancy_reason);

        // 4. Courier submits collected cash to dispatch desk: status becomes 'submitted'
        $settleAction = app(SettleCourierCodAction::class);
        $settleAction->submitCash($settlement->id, 18000.0);
        $this->assertEquals('submitted', $settlement->fresh()->status);
        $this->assertEquals(18000.0, (float) $settlement->fresh()->submitted_amount);

        // 5. Cashier counts and verifies cash: status becomes 'verified'
        $settleAction->verifyCash(
            codSettlementId: $settlement->id,
            verifiedAmount: 18000.0,
            discrepancyReason: 'Confirmed 2,000 AMD short by client; approved by manager',
            cashierId: $this->user->id
        );

        $freshSettlement = $settlement->fresh();
        $this->assertEquals('verified', $freshSettlement->status);
        $this->assertEquals(18000.0, (float) $freshSettlement->verified_amount);
        $this->assertEquals(-2000.0, (float) $freshSettlement->discrepancy_amount);

        // 6. Cashier settles into open POS cash register: status becomes 'settled'
        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $this->terminal->id,
            cashierId: $this->user->id,
            openingCash: 50000.0
        );

        $settleAction->settleCash(
            codSettlementId: $settlement->id,
            posSessionId: $session->id,
            cashierId: $this->user->id
        );

        $this->assertEquals('settled', $settlement->fresh()->status);
        $this->assertNotNull($settlement->fresh()->settled_at);

        // Check drawer cash balance increased by 18,000 AMD (50,000 + 18,000 = 68,000 AMD)
        $this->assertEquals(68000.0, (float) $session->fresh()->closing_cash_calculated);

        // Verify Audit Logs for COD lifecycle and discrepancy
        $auditDiscrepancy = AuditLog::where('action', 'cod.verified')
            ->where('entity_id', $settlement->id)
            ->first();

        $this->assertNotNull($auditDiscrepancy);
        $this->assertEquals(-2000.0, (float) $auditDiscrepancy->new_values['discrepancy_amount']);
    }
}
