<?php

namespace Tests\Feature\MultiTenancy;

use App\Domain\IAM\Models\Role;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected TenantContext $tenantContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantContext = app(TenantContext::class);
    }

    protected function tearDown(): void
    {
        $this->tenantContext->clear();
        parent::tearDown();
    }

    public function test_tenant_context_stores_and_clears_active_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme',
            'subdomain' => 'acme',
        ]);

        $this->tenantContext->setCurrentTenant($tenant);

        $this->assertTrue($this->tenantContext->hasTenant());
        $this->assertEquals($tenant->id, $this->tenantContext->id());
        $this->assertEquals('AMD', $this->tenantContext->currency());

        $this->tenantContext->clear();
        $this->assertFalse($this->tenantContext->hasTenant());
        $this->assertNull($this->tenantContext->id());
    }

    public function test_model_with_belongs_to_tenant_trait_automatically_assigns_tenant_id(): void
    {
        $tenant = Tenant::create([
            'name' => 'Armenia Food Co',
            'slug' => 'armfood',
            'subdomain' => 'armfood',
        ]);

        $this->tenantContext->setCurrentTenant($tenant);

        $role = Role::create([
            'name' => 'Chef',
            'slug' => 'chef',
        ]);

        $this->assertEquals($tenant->id, $role->tenant_id);
    }

    public function test_tenant_scope_strictly_isolates_records_between_tenants(): void
    {
        $tenantA = Tenant::create([
            'name' => 'Company A',
            'slug' => 'company-a',
            'subdomain' => 'company-a',
        ]);

        $tenantB = Tenant::create([
            'name' => 'Company B',
            'slug' => 'company-b',
            'subdomain' => 'company-b',
        ]);

        // Create role for Tenant A
        $this->tenantContext->setCurrentTenant($tenantA);
        $roleA = Role::create([
            'name' => 'Manager A',
            'slug' => 'manager-a',
        ]);

        // Create role for Tenant B
        $this->tenantContext->setCurrentTenant($tenantB);
        $roleB = Role::create([
            'name' => 'Manager B',
            'slug' => 'manager-b',
        ]);

        // While in Tenant B context, querying Role::all() must only return Tenant B's roles
        $rolesForTenantB = Role::all();
        $this->assertCount(1, $rolesForTenantB);
        $this->assertEquals($roleB->id, $rolesForTenantB->first()->id);
        $this->assertFalse($rolesForTenantB->contains('id', $roleA->id));

        // Switch to Tenant A context
        $this->tenantContext->setCurrentTenant($tenantA);
        $rolesForTenantA = Role::all();
        $this->assertCount(1, $rolesForTenantA);
        $this->assertEquals($roleA->id, $rolesForTenantA->first()->id);
        $this->assertFalse($rolesForTenantA->contains('id', $roleB->id));
    }

    public function test_same_email_can_exist_in_different_tenants_safely(): void
    {
        $tenantA = Tenant::create(['name' => 'Tenant One', 'slug' => 't1', 'subdomain' => 't1']);
        $tenantB = Tenant::create(['name' => 'Tenant Two', 'slug' => 't2', 'subdomain' => 't2']);

        $email = 'common.user@business.am';

        $userA = User::create([
            'tenant_id' => $tenantA->id,
            'name' => 'User A',
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        $userB = User::create([
            'tenant_id' => $tenantB->id,
            'name' => 'User B',
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        $this->assertNotEquals($userA->id, $userB->id);
        $this->assertEquals($tenantA->id, $userA->tenant_id);
        $this->assertEquals($tenantB->id, $userB->tenant_id);
    }
}
