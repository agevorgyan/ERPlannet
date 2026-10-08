<?php

namespace App\Http\Middleware;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Billing\Exceptions\FeatureNotAvailableException;
use App\Domain\Billing\Exceptions\PlanLimitExceededException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEntitlement
{
    public function __construct(
        protected EntitlementManagerInterface $entitlementManager
    ) {}

    public function handle(Request $request, Closure $next, string $featureCode): Response
    {
        try {
            $this->entitlementManager->assertCan($featureCode);
        } catch (FeatureNotAvailableException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FEATURE_NOT_INCLUDED',
                    'message' => $e->getMessage(),
                    'feature' => $featureCode,
                ],
            ], 403);
        } catch (PlanLimitExceededException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAN_LIMIT_EXCEEDED',
                    'message' => $e->getMessage(),
                    'feature' => $featureCode,
                    'limit' => $e->limit,
                    'current_usage' => $e->currentUsage,
                ],
            ], 402);
        }

        return $next($request);
    }
}
