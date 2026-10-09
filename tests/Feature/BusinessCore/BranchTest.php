<?php

namespace Tests\Feature\BusinessCore;

use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Branch\Models\Branch;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Armenia Supermarket',
            'slug' => 'armmarket',
            'subdomain' => 'armmarket',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Vahagn',
            'email' => 'vahagn@market.am',
            'password' => Hash::make('password123'),
            'is_owner' => true,
        ]);

        $this->token = $this->user->createToken('token')->plainTextToken;

        // Plan with branches limit = 2
        $plan = Plan::create(['code' => 'plan_2_branches', 'name' => '2 Branches', 'price_monthly' => 20000, 'price_yearly' => 200000]);
        $feat = Feature::create(['code' => 'limit.branches', 'name' => 'Branches', 'type' => 'limit', 'module' => 'branches']);
        $plan->features()->attach($feat->id, ['value' => '2']);

        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    public function test_tenant_can_create_branches(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'armmarket',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/branches', [
            'code' => 'KENTRON',
            'name' => 'Kentron Branch',
            'address' => 'Abovyan 15, Yerevan',
            'phone' => '+37410123456',
            'is_main' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'KENTRON');

        $this->assertDatabaseHas('branches', [
            'tenant_id' => $this->tenant->id,
            'code' => 'KENTRON',
        ]);
    }

    public function test_branch_quota_limit_is_strictly_enforced(): void
    {
        // Create 2 branches (allowed limit is 2)
        Branch::create(['tenant_id' => $this->tenant->id, 'code' => 'B1', 'name' => 'Branch 1']);
        Branch::create(['tenant_id' => $this->tenant->id, 'code' => 'B2', 'name' => 'Branch 2']);

        // Attempt to create 3rd branch
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'armmarket',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/branches', [
            'code' => 'B3',
            'name' => 'Branch 3',
        ]);

        // Expect PlanLimitExceededException (HTTP 402 or Exception)
        $response->assertStatus(402);
    }

    public function test_branch_isolation_between_tenants(): void
    {
        $otherTenant = Tenant::create(['name' => 'Other Co', 'slug' => 'other', 'subdomain' => 'other']);
        $otherBranch = Branch::create(['tenant_id' => $otherTenant->id, 'code' => 'SECRET', 'name' => 'Secret Branch']);

        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'armmarket',
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/v1/branches/{$otherBranch->id}");

        $response->assertStatus(404);
    }
}
