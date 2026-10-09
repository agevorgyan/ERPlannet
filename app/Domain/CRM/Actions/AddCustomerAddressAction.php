<?php

namespace App\Domain\CRM\Actions;

use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerActivity;
use App\Domain\CRM\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

class AddCustomerAddressAction
{
    /**
     * @param array{
     *     title?: string|null,
     *     country?: string|null,
     *     province?: string|null,
     *     city?: string|null,
     *     postal_code?: string|null,
     *     street?: string|null,
     *     building?: string|null,
     *     entrance?: string|null,
     *     floor?: string|null,
     *     apartment?: string|null,
     *     door_code?: string|null,
     *     delivery_instructions?: string|null,
     *     address_line_1?: string|null,
     *     address_line_2?: string|null,
     *     is_default?: bool|null,
     *     is_last_used?: bool|null
     * } $data
     */
    public function execute(Customer $customer, array $data, ?string $userId = null): CustomerAddress
    {
        return DB::transaction(function () use ($customer, $data, $userId) {
            $isDefault = (bool) ($data['is_default'] ?? false);
            $isLastUsed = (bool) ($data['is_last_used'] ?? true);

            // If new is default, unset other defaults
            if ($isDefault) {
                CustomerAddress::withoutGlobalScopes()
                    ->where('tenant_id', $customer->tenant_id)
                    ->where('customer_id', $customer->id)
                    ->update(['is_default' => false]);
            }

            if ($isLastUsed) {
                CustomerAddress::withoutGlobalScopes()
                    ->where('tenant_id', $customer->tenant_id)
                    ->where('customer_id', $customer->id)
                    ->update(['is_last_used' => false]);
            }

            $addrLine1 = $data['address_line_1'] ?? trim(sprintf('%s %s', $data['street'] ?? '', $data['building'] ?? ''));

            $address = CustomerAddress::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'title' => $data['title'] ?? 'Հասցե',
                'country' => $data['country'] ?? 'AM',
                'province' => $data['province'] ?? 'Երևան',
                'city' => $data['city'] ?? 'Երևան',
                'postal_code' => $data['postal_code'] ?? null,
                'street' => $data['street'] ?? null,
                'building' => $data['building'] ?? null,
                'entrance' => $data['entrance'] ?? null,
                'floor' => $data['floor'] ?? null,
                'apartment' => $data['apartment'] ?? null,
                'door_code' => $data['door_code'] ?? null,
                'delivery_instructions' => $data['delivery_instructions'] ?? null,
                'address_line_1' => $addrLine1 ?: 'Հասցե',
                'address_line_2' => $data['address_line_2'] ?? null,
                'is_default' => $isDefault,
                'is_last_used' => $isLastUsed,
            ]);

            CustomerActivity::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'type' => CustomerActivity::TYPE_ADDRESS_ADDED,
                'title' => 'Ավելացվել է նոր առաքման հասցե',
                'content' => $address->formatted_address,
                'reference_type' => CustomerAddress::class,
                'reference_id' => $address->id,
                'metadata' => [
                    'title' => $address->title,
                    'city' => $address->city,
                ],
                'user_id' => $userId,
            ]);

            return $address;
        });
    }
}
