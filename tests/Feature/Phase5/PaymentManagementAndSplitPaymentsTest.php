<?php

declare(strict_types=1);

namespace Tests\Feature\Phase5;

use App\Domain\Branch\Models\Branch;
use App\Domain\IAM\Models\User;
use App\Domain\Payments\Models\Payment;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use App\Infrastructure\Payments\Gateways\ArCaGateway;
use App\Infrastructure\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentManagementAndSplitPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Branch $branch;

    protected Warehouse $warehouse;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $this->branch = Branch::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->firstOrFail();

        $this->order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'order_number' => 'ORD-PAY-001',
            'source' => 'pos',
            'status' => 'new',
            'payment_status' => 'pending',
            'currency' => 'AMD',
            'subtotal' => 10000,
            'total' => 10000,
            'paid_amount' => 0,
            'balance_due' => 10000,
            'placed_at' => now(),
        ]);
    }

    public function test_can_record_split_payments_across_cash_and_card(): void
    {
        // 1. Pay 6,000 AMD in Cash
        $payCashRes = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/sales/orders/{$this->order->id}/payments", [
                'payment_method' => 'cash',
                'amount' => 6000,
            ]);

        $payCashRes->assertStatus(201);
        $this->order->refresh();

        $this->assertEquals(6000.0, (float) $this->order->paid_amount);
        $this->assertEquals(4000.0, (float) $this->order->balance_due);
        $this->assertEquals('partial', $this->order->payment_status);

        // 2. Pay remaining 4,000 AMD via Card
        $payCardRes = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/sales/orders/{$this->order->id}/payments", [
                'payment_method' => 'card',
                'amount' => 4000,
                'transaction_reference' => 'POS-CARD-9912',
            ]);

        $payCardRes->assertStatus(201);
        $this->order->refresh();

        $this->assertEquals(10000.0, (float) $this->order->paid_amount);
        $this->assertEquals(0.0, (float) $this->order->balance_due);
        $this->assertEquals('paid', $this->order->payment_status);

        // Verify two payment records exist
        $this->assertCount(2, $this->order->payments);
    }

    public function test_arca_gateway_adapter_initializes_transaction(): void
    {
        $manager = app(PaymentGatewayManager::class);
        $gateway = $manager->gateway('arca');

        $this->assertInstanceOf(ArCaGateway::class, $gateway);
        $this->assertEquals('arca', $gateway->getName());

        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'order_id' => $this->order->id,
            'payment_method' => 'arca',
            'amount' => 5000,
            'currency' => 'AMD',
            'status' => 'pending',
        ]);

        $initResult = $gateway->initializePayment($payment, [
            'return_url' => 'https://erp.test/checkout/complete',
        ]);

        $this->assertTrue($initResult['success']);
        $this->assertNotEmpty($initResult['transaction_id']);
        $this->assertNotEmpty($initResult['payment_url']);
    }

    public function test_can_record_partial_and_full_refunds(): void
    {
        // 1. Initial full payment
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'order_id' => $this->order->id,
            'payment_method' => 'cash',
            'amount' => 10000,
            'currency' => 'AMD',
            'status' => 'completed',
        ]);
        $this->order->recalculateBalances();

        $this->assertEquals('paid', $this->order->payment_status);
        $this->assertEquals(10000.0, (float) $this->order->paid_amount);

        // 2. Partial refund 3,000 AMD
        $refundRes = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/sales/orders/{$this->order->id}/payments/{$payment->id}/refund", [
                'amount' => 3000,
                'reason' => 'Item defective',
            ]);

        $refundRes->assertStatus(200);
        $this->order->refresh();

        // After refunding 3,000, net paid is 7,000, balance due is 3,000, status partial
        $this->assertEquals(7000.0, (float) $this->order->paid_amount);
        $this->assertEquals(3000.0, (float) $this->order->balance_due);
        $this->assertEquals('partial', $this->order->payment_status);

        // 3. Refund remaining 7,000 AMD
        $refundRemaining = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/sales/orders/{$this->order->id}/payments/{$payment->id}/refund", [
                'amount' => 7000,
                'reason' => 'Return remaining items',
            ]);

        $refundRemaining->assertStatus(200);
        $this->order->refresh();

        $this->assertEquals(0.0, (float) $this->order->paid_amount);
        $this->assertEquals(10000.0, (float) $this->order->balance_due);
        $this->assertEquals('refunded', $this->order->payment_status);
    }
}
