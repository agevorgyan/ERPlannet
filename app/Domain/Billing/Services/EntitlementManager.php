<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Billing\Exceptions\FeatureNotAvailableException;
use App\Domain\Billing\Exceptions\PlanLimitExceededException;
use App\Domain\Billing\Models\SubscriptionUsage;
use App\Infrastructure\MultiTenancy\TenantContext;

class EntitlementManager implements EntitlementManagerInterface
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function can(string $featureCode): bool
    {
        $tenant = $this->tenantContext->getTenant();
        if (!$tenant) {
            return false;
        }

        $subscription = $tenant->activeSubscription;
        if (!$subscription || !$subscription->isActive()) {
            return false;
        }

        $plan = $subscription->plan;
        if (!$plan) {
            return false;
        }

        $value = $plan->getFeatureValue($featureCode);
        if ($value === null) {
            return false;
        }

        return in_array(strtolower($value), ['true', '1', 'yes', 'enabled', 'unlimited']);
    }

    public function getLimit(string $featureCode): int|float
    {
        $tenant = $this->tenantContext->getTenant();
        if (!$tenant) {
            return 0;
        }

        $subscription = $tenant->activeSubscription;
        if (!$subscription || !$subscription->isActive()) {
            return 0;
        }

        $plan = $subscription->plan;
        if (!$plan) {
            return 0;
        }

        $value = $plan->getFeatureValue($featureCode);
        if ($value === null) {
            return 0;
        }

        if (strtolower($value) === 'unlimited' || (int)$value === -1) {
            return INF;
        }

        return (int) $value;
    }

    public function getUsage(string $featureCode): int
    {
        $tenant = $this->tenantContext->getTenant();
        if (!$tenant) {
            return 0;
        }

        $subscription = $tenant->activeSubscription;
        if (!$subscription) {
            return 0;
        }

        $usage = SubscriptionUsage::where('subscription_id', $subscription->id)
            ->where('feature_code', $featureCode)
            ->first();

        return $usage ? (int) $usage->used_count : 0;
    }

    public function canConsume(string $featureCode, int $count = 1): bool
    {
        $limit = $this->getLimit($featureCode);

        if (is_infinite($limit)) {
            return true;
        }

        $currentUsage = $this->getUsage($featureCode);

        return ($currentUsage + $count) <= $limit;
    }

    public function consume(string $featureCode, int $count = 1): void
    {
        $tenant = $this->tenantContext->getTenant();
        if (!$tenant) {
            throw new FeatureNotAvailableException($featureCode, 'No active tenant.');
        }

        $subscription = $tenant->activeSubscription;
        if (!$subscription || !$subscription->isActive()) {
            throw new FeatureNotAvailableException($featureCode, 'No active subscription.');
        }

        $this->assertCan($featureCode, $count);

        $usage = SubscriptionUsage::firstOrNew([
            'subscription_id' => $subscription->id,
            'feature_code' => $featureCode,
        ]);

        $usage->used_count = ($usage->used_count ?? 0) + $count;
        $usage->save();
    }

    public function assertCan(string $featureCode, int $count = 1): void
    {
        $limit = $this->getLimit($featureCode);

        if ($limit === 0 && !$this->can($featureCode)) {
            throw new FeatureNotAvailableException($featureCode);
        }

        if (!is_infinite($limit)) {
            $currentUsage = $this->getUsage($featureCode);
            if (($currentUsage + $count) > $limit) {
                throw new PlanLimitExceededException($featureCode, $limit, $currentUsage);
            }
        }
    }
}
