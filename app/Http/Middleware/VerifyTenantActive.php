<?php

namespace App\Http\Middleware;

use App\Infrastructure\MultiTenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTenantActive
{
    public function __construct(
        protected TenantContext $context
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->context->getTenant();

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TENANT_NOT_IDENTIFIED',
                    'message' => 'No tenant context is active for this request.',
                ],
            ], 404);
        }

        if ($tenant->isSuspended()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TENANT_SUSPENDED',
                    'message' => 'This account has been suspended. Please contact platform support.',
                    'reason' => $tenant->suspended_reason,
                ],
            ], 403);
        }

        if ($tenant->status === 'canceled') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TENANT_CANCELED',
                    'message' => 'This account has been canceled.',
                ],
            ], 403);
        }

        return $next($request);
    }
}
