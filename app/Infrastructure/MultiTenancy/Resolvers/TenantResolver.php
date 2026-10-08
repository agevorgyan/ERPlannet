<?php

namespace App\Infrastructure\MultiTenancy\Resolvers;

use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantDomain;
use Illuminate\Http\Request;

class TenantResolver
{
    /**
     * Resolve tenant from HTTP request.
     */
    public function resolve(Request $request): ?Tenant
    {
        // 1. Check custom domain
        $host = $request->getHost();
        $customDomainTenant = $this->resolveByCustomDomain($host);
        if ($customDomainTenant) {
            return $customDomainTenant;
        }

        // 2. Check subdomain
        $subdomainTenant = $this->resolveBySubdomain($host);
        if ($subdomainTenant) {
            return $subdomainTenant;
        }

        // 3. Check header fallback (useful for testing, Postman, mobile apps)
        return $this->resolveByHeader($request);
    }

    protected function resolveByCustomDomain(string $host): ?Tenant
    {
        $domainRecord = TenantDomain::where('domain', $host)
            ->where('is_verified', true)
            ->with('tenant')
            ->first();

        return $domainRecord?->tenant;
    }

    protected function resolveBySubdomain(string $host): ?Tenant
    {
        $parts = explode('.', $host);

        // If host is something like "acme.platform.com" or "acme.localhost" or "acme.erplannet.test"
        if (count($parts) >= 2) {
            $subdomain = $parts[0];

            // Ignore standard reserved subdomains
            if (in_array(strtolower($subdomain), ['www', 'api', 'admin', 'app', 'mail', 'staging'])) {
                return null;
            }

            return Tenant::where('subdomain', $subdomain)->first();
        }

        return null;
    }

    protected function resolveByHeader(Request $request): ?Tenant
    {
        if ($slug = $request->header('X-Tenant-Slug')) {
            return Tenant::where('slug', $slug)->first();
        }

        if ($id = $request->header('X-Tenant-ID')) {
            return Tenant::find($id);
        }

        return null;
    }
}
