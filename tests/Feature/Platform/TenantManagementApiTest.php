<?php

namespace Tests\Feature\Platform;

use App\Domain\Billing\Models\Plan;
use App\Domain\Platform\Models\PlatformUser;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantManagementApiTest extends TestCase
{
    use RefreshDatabase;

    protected PlatformUser $admin;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = PlatformUser::create([
            'name' => 'Platform Superadmin',
            'email' => 'admin@erplannet.com',
            'password' => Hash::make('SecretAdmin123!'),
            'role' => 'superadmin',
        ]);

        $this->adminToken = $this->admin->createToken('admin_token', ['platform:*'])->plainTextToken;

        Plan::create([
            'code' => 'starter',
            'name' => 'Starter Plan',
            'price_monthly' => 15000,
            'price_yearly' => 150000,
        ]);
    }

    public function test_platform_admin_can_create_new_tenant_with_owner_and_subscription(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/platform/tenants', [
                'name' => 'Sas Gourmet Market',
                'slug' => 'sasgourmet',
                'subdomain' => 'sasgourmet',
                'custom_domain' => 'app.sasgourmet.am',
                'plan_code' => 'starter',
                'owner_name' => 'Artak Stepanyan',
                'owner_email' => 'artak@sasgourmet.am',
                'owner_password' => 'OwnerPass999!',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'sasgourmet');

        $tenant = Tenant::where('slug', 'sasgourmet')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('trialing', $tenant->status);
        $this->assertCount(1, $tenant->users);
        $this->assertTrue($tenant->users->first()->is_owner);
        $this->assertNotNull($tenant->activeSubscription);
    }

    public function test_platform_admin_can_suspend_and_reactivate_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Co',
            'slug' => 'testco',
            'subdomain' => 'testco',
            'status' => 'active',
        ]);

        // Suspend
        $suspendRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/platform/tenants/{$tenant->id}/suspend", [
                'reason' => 'Violation of terms',
            ]);

        $suspendRes->assertStatus(200)
            ->assertJsonPath('data.status', 'suspended');

        // Activate
        $activateRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/platform/tenants/{$tenant->id}/activate");

        $activateRes->assertStatus(200)
            ->assertJsonPath('data.status', 'active');
    }
}
