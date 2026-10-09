<?php

namespace Tests\Feature\BusinessCore;

use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected string $token;

    protected Branch $branch;

    protected Customer $customer;

    protected Product $productPizza;

    protected ProductVariant $variantLarge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Italiano Yerevan',
            'slug' => 'italiano',
            'subdomain' => 'italiano',
            'currency' => 'AMD',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Luigi',
            'email' => 'luigi@italiano.am',
            'password' => Hash::make('password123'),
            'is_owner' => true,
        ]);

        $this->token = $this->user->createToken('token')->plainTextToken;

        // Plan with orders limit = 5
        $plan = Plan::create(['code' => 'plan_orders', 'name' => 'Orders Plan', 'price_monthly' => 20000, 'price_yearly' => 200000]);
        $feat = Feature::create(['code' => 'limit.orders_monthly', 'name' => 'Monthly Orders', 'type' => 'limit', 'module' => 'sales']);
        $plan->features()->attach($feat->id, ['value' => '5']);

        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->branch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'CENTRAL',
            'name' => 'Central Pizzeria',
            'is_main' => true,
        ]);

        $this->customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Narek',
            'phone' => '+37494112233',
        ]);

        $unitPcs = Unit::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'pcs',
            'name' => ['hy' => 'հատ'],
            'precision' => 0,
        ]);

        $this->productPizza = Product::create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $unitPcs->id,
            'sku' => 'PIZZA-MARG',
            'name' => ['hy' => 'Պիցցա Մարգարիտա', 'en' => 'Pizza Margherita'],
            'sale_price' => 2800.00,
        ]);

        $this->variantLarge = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->productPizza->id,
            'sku' => 'PIZZA-MARG-LRG',
            'name' => ['hy' => 'Մեծ 32սմ'],
            'sale_price' => 3800.00,
        ]);
    }

    public function test_can_create_order_with_server_side_price_calculations_and_sequence(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'italiano',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/orders', [
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'delivery_type' => 'delivery',
            'delivery_fee' => 500.00,
            'discount' => 300.00,
            'items' => [
                [
                    'product_id' => $this->productPizza->id,
                    'variant_id' => $this->variantLarge->id, // 3800 AMD
                    'quantity' => 2, // 2 * 3800 = 7600 AMD
                ],
            ],
        ]);

        // Expected total: 7600 (subtotal) - 300 (discount) + 500 (delivery_fee) = 7800.00 AMD
        $year = date('Y');
        $expectedOrderNumber = "ORD-{$year}-000001";

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', $expectedOrderNumber)
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.subtotal', '7600.00')
            ->assertJsonPath('data.total', '7800.00');

        $this->assertDatabaseHas('orders', [
            'tenant_id' => $this->tenant->id,
            'order_number' => $expectedOrderNumber,
            'total' => '7800.00',
        ]);
    }

    public function test_order_status_state_machine_transitions_and_delivers_order(): void
    {
        $year = date('Y');
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'order_number' => "ORD-{$year}-000099",
            'status' => 'new',
            'total' => 5000.00,
        ]);

        // Transition: new -> confirmed
        $resConfirmed = $this->withHeaders([
            'X-Tenant-Slug' => 'italiano',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'confirmed',
            'comment' => 'Order verified by call center',
        ]);

        $resConfirmed->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed');

        // Transition: confirmed -> processing
        $resProcessing = $this->withHeaders([
            'X-Tenant-Slug' => 'italiano',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'processing',
            'comment' => 'Kitchen started cooking',
        ]);
        $resProcessing->assertStatus(200);

        // Transition: processing -> packed
        $this->withHeaders([
            'X-Tenant-Slug' => 'italiano',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/orders/{$order->id}/status", ['status' => 'packed'])->assertStatus(200);

        // Transition: packed -> delivery
        $this->withHeaders([
            'X-Tenant-Slug' => 'italiano',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/orders/{$order->id}/status", ['status' => 'delivery'])->assertStatus(200);

        // Transition: delivery -> delivered
        $resDelivered = $this->withHeaders([
            'X-Tenant-Slug' => 'italiano',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/orders/{$order->id}/status", ['status' => 'delivered']);
        $resDelivered->assertStatus(200);

        // Verify customer stats were updated upon delivery
        $this->customer->refresh();
        $this->assertEquals(1, $this->customer->orders_count);
        $this->assertEquals('5000.00', $this->customer->total_spent);
        $this->assertNotNull($this->customer->last_ordered_at);

        // Attempt invalid transition: delivered cannot transition back to new!
        $resInvalid = $this->withHeaders([
            'X-Tenant-Slug' => 'italiano',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/orders/{$order->id}/status", ['status' => 'new']);

        $resInvalid->assertStatus(422); // Rejection
    }

    public function test_can_cancel_order_with_reason(): void
    {
        $year = date('Y');
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'order_number' => "ORD-{$year}-000100",
            'status' => 'new',
            'total' => 2000.00,
        ]);

        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'italiano',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/orders/{$order->id}/cancel", [
            'reason' => 'Customer requested cancellation due to delay',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('order_status_histories', [
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'to_status' => 'cancelled',
            'comment' => 'Customer requested cancellation due to delay',
        ]);
    }
}
