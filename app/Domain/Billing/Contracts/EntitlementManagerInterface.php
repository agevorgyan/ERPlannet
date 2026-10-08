<?php

namespace App\Domain\Billing\Contracts;

interface EntitlementManagerInterface
{
    /**
     * Check if a boolean feature is enabled for the current tenant's plan.
     */
    public function can(string $featureCode): bool;

    /**
     * Get the quota limit for a given feature code.
     * Returns INF if unlimited, or integer limit value.
     */
    public function getLimit(string $featureCode): int|float;

    /**
     * Get current usage count for the given feature code in the active billing cycle.
     */
    public function getUsage(string $featureCode): int;

    /**
     * Check if the tenant can consume $count units without exceeding quota.
     */
    public function canConsume(string $featureCode, int $count = 1): bool;

    /**
     * Consume units and persist in subscription_usages.
     */
    public function consume(string $featureCode, int $count = 1): void;

    /**
     * Assert feature entitlement or throw an exception.
     */
    public function assertCan(string $featureCode, int $count = 1): void;
}
