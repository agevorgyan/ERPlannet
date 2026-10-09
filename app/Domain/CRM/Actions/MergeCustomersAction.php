<?php

namespace App\Domain\CRM\Actions;

use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerActivity;
use App\Domain\CRM\Models\CustomerAddress;
use App\Domain\CRM\Models\CustomerContact;
use App\Domain\CRM\Services\CustomerAnalyticsService;
use App\Domain\CRM\Services\CustomerLoyaltyService;
use App\Domain\Sales\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MergeCustomersAction
{
    public function __construct(
        protected CustomerLoyaltyService $loyaltyService,
        protected CustomerAnalyticsService $analyticsService
    ) {}

    public function execute(Customer $targetCustomer, Customer $sourceCustomer, ?string $userId = null, ?string $notes = null): Customer
    {
        if ($targetCustomer->id === $sourceCustomer->id) {
            throw new InvalidArgumentException('Cannot merge a customer into themselves.');
        }

        if ($targetCustomer->tenant_id !== $sourceCustomer->tenant_id) {
            throw new InvalidArgumentException('Cannot merge customers across different tenants.');
        }

        return DB::transaction(function () use ($targetCustomer, $sourceCustomer, $userId, $notes) {
            $snapshot = $sourceCustomer->load(['individual', 'company', 'contacts', 'addresses', 'loyaltyAccount'])->toArray();

            // 1. Audit log the merge
            DB::table('customer_merge_logs')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $targetCustomer->tenant_id,
                'primary_customer_id' => $targetCustomer->id,
                'merged_customer_id' => $sourceCustomer->id,
                'merged_by_user_id' => $userId,
                'snapshot_data' => json_encode($snapshot),
                'notes' => $notes ?: 'Merged duplicate customer records',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Re-assign orders
            Order::withoutGlobalScopes()
                ->where('tenant_id', $targetCustomer->tenant_id)
                ->where('customer_id', $sourceCustomer->id)
                ->update(['customer_id' => $targetCustomer->id]);

            // 3. Move addresses
            CustomerAddress::withoutGlobalScopes()
                ->where('tenant_id', $targetCustomer->tenant_id)
                ->where('customer_id', $sourceCustomer->id)
                ->update([
                    'customer_id' => $targetCustomer->id,
                    'is_default' => false,
                ]);

            // 4. Move contacts
            CustomerContact::withoutGlobalScopes()
                ->where('tenant_id', $targetCustomer->tenant_id)
                ->where('customer_id', $sourceCustomer->id)
                ->update(['customer_id' => $targetCustomer->id]);

            // 5. Transfer loyalty points if source had points
            $sourceLoyalty = $sourceCustomer->loyaltyAccount;
            if ($sourceLoyalty && (float) $sourceLoyalty->points_balance > 0) {
                $points = (float) $sourceLoyalty->points_balance;
                $this->loyaltyService->recordTransaction(
                    $targetCustomer,
                    'earn',
                    $points,
                    "Միավորների տեղափոխում միավորված հաշվից ({$sourceCustomer->customer_code})",
                    null,
                    $userId
                );
            }

            // 6. Record activity in target
            CustomerActivity::create([
                'tenant_id' => $targetCustomer->tenant_id,
                'customer_id' => $targetCustomer->id,
                'type' => CustomerActivity::TYPE_STATUS_CHANGED,
                'title' => 'Հաճախորդների միավորում',
                'content' => "Հաճախորդ {$sourceCustomer->customer_code} ({$sourceCustomer->full_name}) միավորվել է ընթացիկ քարտի մեջ",
                'reference_type' => Customer::class,
                'reference_id' => $sourceCustomer->id,
                'metadata' => [
                    'source_customer_id' => $sourceCustomer->id,
                    'source_customer_code' => $sourceCustomer->customer_code,
                    'notes' => $notes,
                ],
                'user_id' => $userId,
            ]);

            // 7. Soft delete source customer
            $sourceCustomer->delete();

            // 8. Recalculate target analytics
            $this->analyticsService->recalculate($targetCustomer);

            return $targetCustomer->fresh(['individual', 'company', 'contacts', 'addresses', 'loyaltyAccount']);
        });
    }
}
