<?php

namespace App\Http\Middleware;

use App\Infrastructure\MultiTenancy\Resolvers\TenantResolver;
use App\Infrastructure\MultiTenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(
        protected TenantResolver $resolver,
        protected TenantContext $context
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolver->resolve($request);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TENANT_NOT_FOUND',
                    'message' => 'The requested tenant could not be identified from the domain or headers.',
                ],
            ], 404);
        }

        $this->context->setCurrentTenant($tenant);

        // Apply tenant-specific locale and timezone
        if ($tenant->default_locale) {
            app()->setLocale($tenant->default_locale);
        }

        if ($tenant->timezone) {
            date_default_timezone_set($tenant->timezone);
        }

        return $next($request);
    }
}
