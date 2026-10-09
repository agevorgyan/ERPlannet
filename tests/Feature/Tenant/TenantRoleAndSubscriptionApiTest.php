<?php

namespace Tests\Feature\Tenant;

use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\IAM\Models\Role;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantRoleAndSubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $owner;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Armenia Pastry',
            'slug' => 'pastry',
            'subdomain' => 'pastry',
            'currency' => 'AMD',
        ]);

        $this->owner = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pastry Chef',
            'email' => 'chef@pastry.am',
            'password' => Hash::make('ChefPass123!'),
            'is_owner' => true,
        ]);

        $this->token = $this->owner->createToken('tenant_token')->plainTextToken;

        // Create a plan with features
        $plan = Plan::create([
            'code' => 'pro',
            'name' => 'Professional',
            'price_monthly' => 30000,
            'price_yearly' => 300000,
        ]);

        $feature = Feature::create([
            'code' => 'limit.users',
            'name' => 'Users Limit',
            'type' => 'limit',
            'module' => 'iam',
        ]);

        $plan->features()->attach($feature->id, ['value' => '10']);

        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    public function test_tenant_can_create_custom_role(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'pastry',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/roles', [
            'name' => 'Inventory Clerk',
            'description' => 'Responsible for warehouse records',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Inventory Clerk');

        $role = Role::where('slug', 'inventory-clerk')->first();
        $this->assertNotNull($role);
        $this->assertEquals($this->tenant->id, $role->tenant_id);
    }

    public function test_tenant_cannot_view_or_modify_other_tenant_roles(): void
    {
        $otherTenant = Tenant::create(['name' => 'Other Co', 'slug' => 'other', 'subdomain' => 'other']);
        $otherRole = Role::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Secret Auditor',
            'slug' => 'secret-auditor',
        ]);

        // Attempt to fetch $otherRole under 'pastry' tenant
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'pastry',
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/v1/roles/{$otherRole->id}");

        $response->assertStatus(404); // Scoped to tenant, so returns 404
    }

    public function test_tenant_can_view_active_subscription_and_entitlements(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'pastry',
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/subscription');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.plan.code', 'pro')
            ->assertJsonPath('data.subscription.is_active', true);
    }

    public function test_tenant_can_initiate_payment(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'pastry',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/payments/initiate', [
            'gateway' => 'cash',
            'amount' => 12000,
            'return_url' => 'https://pastry.erplannet.com/orders',
            'cancel_url' => 'https://pastry.erplannet.com/orders',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'successful');
    }
}
