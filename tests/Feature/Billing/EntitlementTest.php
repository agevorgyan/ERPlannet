<?php

namespace Tests\Feature\Billing;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Billing\Exceptions\FeatureNotAvailableException;
use App\Domain\Billing\Exceptions\PlanLimitExceededException;
use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntitlementTest extends TestCase
{
    use RefreshDatabase;

    protected EntitlementManagerInterface $entitlementManager;
    protected TenantContext $tenantContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entitlementManager = app(EntitlementManagerInterface::class);
        $this->tenantContext = app(TenantContext::class);
    }

    protected function tearDown(): void
    {
        $this->tenantContext->clear();
        parent::tearDown();
    }

    public function test_entitlement_manager_validates_boolean_feature_flags(): void
    {
        $crmFeature = Feature::create(['code' => 'feature.crm', 'name' => 'CRM', 'type' => 'boolean', 'module' => 'crm']);
        $prodFeature = Feature::create(['code' => 'feature.production', 'name' => 'Prod', 'type' => 'boolean', 'module' => 'production']);

        $plan = Plan::create([
            'code' => 'basic_plan',
            'name' => 'Basic Plan',
            'price_monthly' => 10000,
            'price_yearly' => 100000,
        ]);

        $plan->features()->attach([
            $crmFeature->id => ['value' => 'true'],
            $prodFeature->id => ['value' => 'false'],
        ]);

        $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'test-tenant', 'subdomain' => 'test-tenant']);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->tenantContext->setCurrentTenant($tenant);

        $this->assertTrue($this->entitlementManager->can('feature.crm'));
        $this->assertFalse($this->entitlementManager->can('feature.production'));
    }

    public function test_entitlement_manager_enforces_quota_limits_and_throws_exception(): void
    {
        $userLimitFeature = Feature::create([
            'code' => 'limit.users',
            'name' => 'Users Limit',
            'type' => 'limit',
            'module' => 'iam',
        ]);

        $plan = Plan::create([
            'code' => 'starter_plan',
            'name' => 'Starter Plan',
            'price_monthly' => 10000,
            'price_yearly' => 100000,
        ]);

        $plan->features()->attach($userLimitFeature->id, ['value' => '2']);

        $tenant = Tenant::create(['name' => 'Bakery Ltd', 'slug' => 'bakery', 'subdomain' => 'bakery']);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->tenantContext->setCurrentTenant($tenant);

        $this->assertEquals(2, $this->entitlementManager->getLimit('limit.users'));
        $this->assertEquals(0, $this->entitlementManager->getUsage('limit.users'));

        // Consume 1 unit
        $this->entitlementManager->consume('limit.users', 1);
        $this->assertEquals(1, $this->entitlementManager->getUsage('limit.users'));

        // Consume 2nd unit
        $this->entitlementManager->consume('limit.users', 1);
        $this->assertEquals(2, $this->entitlementManager->getUsage('limit.users'));

        // Attempting to consume 3rd unit must throw PlanLimitExceededException
        $this->expectException(PlanLimitExceededException::class);
        $this->entitlementManager->consume('limit.users', 1);
    }
}
