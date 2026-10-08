<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected EntitlementManagerInterface $entitlementManager
    ) {}

    public function show(): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        $subscription = $tenant?->activeSubscription?->load(['plan.features', 'usages']);

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_ACTIVE_SUBSCRIPTION',
                    'message' => 'No active subscription found for this company.',
                ],
            ], 404);
        }

        $features = $subscription->plan->features->map(function ($feat) {
            $isLimit = $feat->type === 'limit';
            $limitValue = $this->entitlementManager->getLimit($feat->code);
            $usageValue = $isLimit ? $this->entitlementManager->getUsage($feat->code) : null;

            return [
                'code' => $feat->code,
                'name' => $feat->name,
                'type' => $feat->type,
                'module' => $feat->module,
                'value' => $feat->pivot->value,
                'limit' => is_infinite($limitValue) ? 'unlimited' : $limitValue,
                'usage' => $usageValue,
                'available' => is_infinite($limitValue) ? 'unlimited' : max(0, $limitValue - $usageValue),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'billing_cycle' => $subscription->billing_cycle,
                    'starts_at' => $subscription->starts_at?->toIso8601String(),
                    'ends_at' => $subscription->ends_at?->toIso8601String(),
                    'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                    'is_active' => $subscription->isActive(),
                ],
                'plan' => [
                    'id' => $subscription->plan->id,
                    'code' => $subscription->plan->code,
                    'name' => $subscription->plan->name,
                    'price_monthly' => $subscription->plan->price_monthly,
                    'price_yearly' => $subscription->plan->price_yearly,
                    'currency' => $subscription->plan->currency,
                ],
                'entitlements' => $features,
            ],
        ]);
    }
}
