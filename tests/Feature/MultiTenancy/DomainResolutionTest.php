<?php

namespace Tests\Feature\MultiTenancy;

use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantDomain;
use App\Infrastructure\MultiTenancy\Resolvers\TenantResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DomainResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected TenantResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(TenantResolver::class);
    }

    public function test_resolves_tenant_by_subdomain(): void
    {
        $tenant = Tenant::create([
            'name' => 'Food Direct',
            'slug' => 'fooddirect',
            'subdomain' => 'fooddirect',
        ]);

        $request = Request::create('https://fooddirect.erplannet.com/api/v1/health');
        $resolved = $this->resolver->resolve($request);

        $this->assertNotNull($resolved);
        $this->assertEquals($tenant->id, $resolved->id);
    }

    public function test_resolves_tenant_by_verified_custom_domain(): void
    {
        $tenant = Tenant::create([
            'name' => 'Boutique Armenia',
            'slug' => 'boutique',
            'subdomain' => 'boutique',
            'custom_domain' => 'app.boutique.am',
        ]);

        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'app.boutique.am',
            'is_verified' => true,
        ]);

        $request = Request::create('https://app.boutique.am/api/v1/health');
        $resolved = $this->resolver->resolve($request);

        $this->assertNotNull($resolved);
        $this->assertEquals($tenant->id, $resolved->id);
    }

    public function test_resolves_tenant_by_header_fallback(): void
    {
        $tenant = Tenant::create([
            'name' => 'Fast Delivery',
            'slug' => 'fastdelivery',
            'subdomain' => 'fastdelivery',
        ]);

        $request = Request::create('https://api.erplannet.com/api/v1/health');
        $request->headers->set('X-Tenant-Slug', 'fastdelivery');

        $resolved = $this->resolver->resolve($request);

        $this->assertNotNull($resolved);
        $this->assertEquals($tenant->id, $resolved->id);
    }

    public function test_returns_null_for_reserved_subdomain_or_missing_tenant(): void
    {
        $request = Request::create('https://admin.erplannet.com/api/v1/health');
        $resolved = $this->resolver->resolve($request);

        $this->assertNull($resolved);
    }
}
