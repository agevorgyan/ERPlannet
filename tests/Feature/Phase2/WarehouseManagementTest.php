<?php

namespace Tests\Feature\Phase2;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Branch\Models\Branch;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->branch = Branch::where('tenant_id', $this->tenant->id)->firstOrFail();
    }

    public function test_tenant_can_list_only_its_own_warehouses(): void
    {
        // Create warehouse for tenant A
        $whA = Warehouse::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-CUSTOM-A',
            'name' => 'Tenant A Warehouse',
            'type' => 'standard',
        ]);

        // Create Tenant B with its own warehouse
        $tenantB = Tenant::create([
            'name' => 'Company B',
            'slug' => 'company-b',
            'subdomain' => 'company-b',
            'status' => 'active',
        ]);
        $whB = Warehouse::create([
            'tenant_id' => $tenantB->id,
            'code' => 'WH-B',
            'name' => 'Tenant B Warehouse',
            'type' => 'standard',
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/warehouses');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($whA->id, $ids);
        $this->assertNotContains($whB->id, $ids);
    }

    public function test_tenant_can_create_warehouse_within_entitlement(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'code' => 'WH-NEW-01',
            'name' => 'New Regional Hub',
            'type' => 'retail',
            'address' => 'Northern Ave 5, Yerevan',
        ];

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/warehouses', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'WH-NEW-01')
            ->assertJsonPath('data.type', 'retail');

        $this->assertDatabaseHas('warehouses', [
            'tenant_id' => $this->tenant->id,
            'code' => 'WH-NEW-01',
        ]);
    }

    public function test_multi_warehouse_feature_is_blocked_on_starter_plan(): void
    {
        // Downgrade tenant to starter plan
        $starterPlan = Plan::where('code', 'starter')->firstOrFail();
        Subscription::where('tenant_id', $this->tenant->id)->update(['plan_id' => $starterPlan->id]);

        // Clear existing warehouses to test creating a second one
        Warehouse::where('tenant_id', $this->tenant->id)->delete();

        // 1st warehouse succeeds
        Warehouse::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'code' => 'WH-SINGLE',
            'name' => 'Single Allowed Warehouse',
        ]);

        // Attempting to create 2nd warehouse on starter plan fails with 403 FeatureNotAvailable
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/warehouses', [
                'branch_id' => $this->branch->id,
                'code' => 'WH-SECOND',
                'name' => 'Second Warehouse (Should Fail)',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FEATURE_NOT_INCLUDED');
    }

    public function test_cannot_delete_warehouse_with_positive_stock_on_hand(): void
    {
        $warehouse = Warehouse::where('tenant_id', $this->tenant->id)->where('code', 'WH-COLD')->firstOrFail();

        // WH-COLD has stock from seeders
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'WAREHOUSE_NOT_EMPTY');

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'deleted_at' => null,
        ]);
    }
}
