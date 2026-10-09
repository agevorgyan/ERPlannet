<?php

namespace App\Domain\CRM\Services;

use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerActivity;
use App\Domain\CRM\Models\CustomerLoyaltyAccount;
use App\Domain\CRM\Models\CustomerLoyaltyTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerLoyaltyService
{
    /**
     * Get or initialize a loyalty account for the customer.
     */
    public function getOrCreateAccount(Customer $customer): CustomerLoyaltyAccount
    {
        $account = CustomerLoyaltyAccount::withoutGlobalScopes()
            ->where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->first();

        if ($account) {
            return $account;
        }

        $cardNumber = 'LOYAL-'.strtoupper(Str::random(8));

        return CustomerLoyaltyAccount::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'card_number' => $cardNumber,
            'barcode' => $cardNumber,
            'qr_code_token' => Str::uuid()->toString(),
            'current_tier' => $customer->loyalty_tier ?? Customer::TIER_BASIC,
            'points_balance' => 0.00,
            'lifetime_points_earned' => 0.00,
            'lifetime_points_spent' => 0.00,
            'active_discount_percent' => (float) $customer->custom_discount_percent,
        ]);
    }

    /**
     * Record a loyalty points ledger transaction with idempotency protection.
     */
    public function recordTransaction(
        Customer $customer,
        string $type,
        float $pointsDelta,
        string $reason,
        ?string $orderId = null,
        ?string $userId = null
    ): CustomerLoyaltyTransaction {
        return DB::transaction(function () use ($customer, $type, $pointsDelta, $reason, $orderId, $userId) {
            $account = $this->getOrCreateAccount($customer);

            // Idempotency check: if orderId is provided, don't duplicate earn/redeem for the same order
            if ($orderId) {
                $existing = CustomerLoyaltyTransaction::withoutGlobalScopes()
                    ->where('tenant_id', $customer->tenant_id)
                    ->where('loyalty_account_id', $account->id)
                    ->where('order_id', $orderId)
                    ->where('type', $type)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $currentBalance = (float) $account->points_balance;
            $newBalance = max(0.00, round($currentBalance + $pointsDelta, 2));

            $transaction = CustomerLoyaltyTransaction::create([
                'tenant_id' => $customer->tenant_id,
                'loyalty_account_id' => $account->id,
                'customer_id' => $customer->id,
                'order_id' => $orderId,
                'type' => $type,
                'points_delta' => $pointsDelta,
                'balance_after' => $newBalance,
                'currency_value' => abs($pointsDelta), // 1 point = 1 AMD
                'reason' => $reason,
                'performed_by_user_id' => $userId,
            ]);

            // Update account aggregates
            $account->points_balance = $newBalance;
            if ($pointsDelta > 0) {
                $account->lifetime_points_earned = (float) $account->lifetime_points_earned + $pointsDelta;
            } else {
                $account->lifetime_points_spent = (float) $account->lifetime_points_spent + abs($pointsDelta);
            }

            // Recalculate tier
            $newTier = $this->determineTier((float) $account->lifetime_points_earned);
            $account->current_tier = $newTier;
            $account->save();

            // Sync with customer model
            $customer->loyalty_tier = $newTier;
            $customer->save();

            // Log activity
            CustomerActivity::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'type' => CustomerActivity::TYPE_LOYALTY,
                'title' => $pointsDelta >= 0 ? "Լոյալության միավորների կուտակում (+{$pointsDelta})" : "Լոյալության միավորների օգտագործում ({$pointsDelta})",
                'content' => $reason,
                'reference_type' => CustomerLoyaltyTransaction::class,
                'reference_id' => $transaction->id,
                'metadata' => [
                    'points_delta' => $pointsDelta,
                    'balance_after' => $newBalance,
                    'tier' => $newTier,
                ],
                'user_id' => $userId,
            ]);

            return $transaction;
        });
    }

    /**
     * Determine customer loyalty tier based on earned points.
     */
    public function determineTier(float $lifetimePoints): string
    {
        if ($lifetimePoints >= 7500) {
            return Customer::TIER_VIP;
        }
        if ($lifetimePoints >= 3500) {
            return Customer::TIER_GOLD;
        }
        if ($lifetimePoints >= 1500) {
            return Customer::TIER_SILVER;
        }
        if ($lifetimePoints >= 500) {
            return Customer::TIER_BRONZE;
        }

        return Customer::TIER_BASIC;
    }
}
