<?php

namespace App\Domain\CRM\Actions;

use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerActivity;
use App\Domain\CRM\Models\CustomerAddress;
use App\Domain\CRM\Models\CustomerCompany;
use App\Domain\CRM\Models\CustomerContact;
use App\Domain\CRM\Models\CustomerIndividual;
use App\Domain\CRM\Services\CustomerCodeGenerator;
use App\Domain\CRM\Services\CustomerLoyaltyService;
use App\Domain\CRM\Services\PhoneNumberNormalizer;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateCustomerAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected CustomerLoyaltyService $loyaltyService
    ) {}

    /**
     * @param array{
     *     type?: string,
     *     first_name?: string|null,
     *     last_name?: string|null,
     *     middle_name?: string|null,
     *     birth_date?: string|null,
     *     gender?: string|null,
     *     company_name?: string|null,
     *     trade_name?: string|null,
     *     tax_id?: string|null,
     *     legal_address?: string|null,
     *     physical_address?: string|null,
     *     website?: string|null,
     *     director_name?: string|null,
     *     accountant_name?: string|null,
     *     purchasing_manager_name?: string|null,
     *     credit_limit?: float|null,
     *     payment_terms_days?: int|null,
     *     is_postpaid_allowed?: bool|null,
     *     phone: string,
     *     email?: string|null,
     *     primary_branch_id?: string|null,
     *     acquisition_source_id?: string|null,
     *     assigned_manager_id?: string|null,
     *     custom_discount_percent?: float|null,
     *     notes?: string|null,
     *     marketing_sms_consent?: bool|null,
     *     marketing_email_consent?: bool|null,
     *     marketing_calls_consent?: bool|null,
     *     address?: array{
     *         title?: string|null,
     *         country?: string|null,
     *         province?: string|null,
     *         city?: string|null,
     *         postal_code?: string|null,
     *         street?: string|null,
     *         building?: string|null,
     *         entrance?: string|null,
     *         floor?: string|null,
     *         apartment?: string|null,
     *         door_code?: string|null,
     *         delivery_instructions?: string|null,
     *         address_line_1?: string|null
     *     }|null,
     *     contacts?: array<array{name: string, position?: string|null, phone: string, email?: string|null, is_primary?: bool|null}>|null
     * } $data
     */
    public function execute(array $data, ?string $userId = null): Customer
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new InvalidArgumentException('No active tenant context.');
        }

        $type = $data['type'] ?? (empty($data['first_name']) && ! empty($data['company_name']) ? Customer::TYPE_COMPANY : Customer::TYPE_INDIVIDUAL);

        // Normalize phone
        $normalizedPhone = PhoneNumberNormalizer::normalize($data['phone']);
        if (! $normalizedPhone) {
            throw new InvalidArgumentException('Invalid phone number provided.');
        }

        return DB::transaction(function () use ($data, $tenant, $type, $normalizedPhone, $userId) {
            $customerCode = CustomerCodeGenerator::generate($tenant->id);

            $firstName = $data['first_name'] ?? null;
            $lastName = $data['last_name'] ?? null;
            $companyName = $data['company_name'] ?? null;

            if ($type === Customer::TYPE_COMPANY && empty($companyName)) {
                $companyName = trim("{$firstName} {$lastName}") ?: 'Company';
            }

            if ($type === Customer::TYPE_INDIVIDUAL && empty($firstName)) {
                $firstName = $companyName ?: 'Customer';
            }

            $displayName = $type === Customer::TYPE_COMPANY
                ? $companyName
                : trim("{$firstName} {$lastName}");

            $customer = Customer::create([
                'tenant_id' => $tenant->id,
                'customer_code' => $customerCode,
                'type' => $type,
                'status' => Customer::STATUS_ACTIVE,
                'primary_branch_id' => $data['primary_branch_id'] ?? null,
                'created_by_user_id' => $userId,
                'assigned_manager_id' => $data['assigned_manager_id'] ?? null,
                'acquisition_source_id' => $data['acquisition_source_id'] ?? null,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'company_name' => $companyName,
                'display_name' => $displayName,
                'tax_id' => $data['tax_id'] ?? null,
                'email' => $data['email'] ?? null,
                'primary_email' => $data['email'] ?? null,
                'phone' => $normalizedPhone,
                'primary_phone' => $normalizedPhone,
                'source' => $data['source'] ?? 'direct',
                'notes' => $data['notes'] ?? null,
                'custom_discount_percent' => (float) ($data['custom_discount_percent'] ?? 0.0),
                'marketing_sms_consent' => (bool) ($data['marketing_sms_consent'] ?? false),
                'marketing_email_consent' => (bool) ($data['marketing_email_consent'] ?? false),
                'marketing_calls_consent' => (bool) ($data['marketing_calls_consent'] ?? false),
                'consent_recorded_at' => now(),
            ]);

            // Create profile based on type
            if ($type === Customer::TYPE_COMPANY) {
                CustomerCompany::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'legal_name' => $data['legal_name'] ?? $companyName,
                    'trade_name' => $data['trade_name'] ?? $data['company_name'] ?? null,
                    'tax_id' => $data['tax_id'] ?? null,
                    'registration_country' => $data['registration_country'] ?? 'AM',
                    'legal_address' => $data['legal_address'] ?? null,
                    'physical_address' => $data['physical_address'] ?? null,
                    'website' => $data['website'] ?? null,
                    'director_name' => $data['director_name'] ?? null,
                    'accountant_name' => $data['accountant_name'] ?? null,
                    'purchasing_manager_name' => $data['purchasing_manager_name'] ?? null,
                    'credit_limit' => (float) ($data['credit_limit'] ?? 0.0),
                    'payment_terms_days' => (int) ($data['payment_terms_days'] ?? 0),
                    'is_postpaid_allowed' => (bool) ($data['is_postpaid_allowed'] ?? false),
                ]);
            } else {
                CustomerIndividual::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'middle_name' => $data['middle_name'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'preferred_language' => $data['preferred_language'] ?? 'hy',
                ]);
            }

            // Create primary address if provided
            if (! empty($data['address'])) {
                $addr = $data['address'];
                $addrLine1 = $addr['address_line_1'] ?? trim(sprintf('%s %s', $addr['street'] ?? '', $addr['building'] ?? ''));

                CustomerAddress::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'title' => $addr['title'] ?? 'Գլխավոր',
                    'country' => $addr['country'] ?? 'AM',
                    'province' => $addr['province'] ?? 'Երևան',
                    'city' => $addr['city'] ?? 'Երևան',
                    'postal_code' => $addr['postal_code'] ?? null,
                    'street' => $addr['street'] ?? null,
                    'building' => $addr['building'] ?? null,
                    'entrance' => $addr['entrance'] ?? null,
                    'floor' => $addr['floor'] ?? null,
                    'apartment' => $addr['apartment'] ?? null,
                    'door_code' => $addr['door_code'] ?? null,
                    'delivery_instructions' => $addr['delivery_instructions'] ?? null,
                    'address_line_1' => $addrLine1 ?: 'Հասցե',
                    'is_default' => true,
                    'is_last_used' => true,
                ]);
            }

            // Contacts
            if (! empty($data['contacts'])) {
                foreach ($data['contacts'] as $c) {
                    if (! empty($c['name'])) {
                        CustomerContact::create([
                            'tenant_id' => $tenant->id,
                            'customer_id' => $customer->id,
                            'name' => $c['name'],
                            'position' => $c['position'] ?? null,
                            'phone' => PhoneNumberNormalizer::normalize($c['phone']) ?? $c['phone'],
                            'email' => $c['email'] ?? null,
                            'is_primary' => (bool) ($c['is_primary'] ?? false),
                        ]);
                    }
                }
            }

            // Initialize loyalty account
            $this->loyaltyService->getOrCreateAccount($customer);

            // Record initial activity
            CustomerActivity::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'type' => CustomerActivity::TYPE_STATUS_CHANGED,
                'title' => 'Հաճախորդը գրանցվել է համակարգում',
                'content' => "Ստեղծվել է նոր {$type} քարտ ({$customerCode})",
                'reference_type' => Customer::class,
                'reference_id' => $customer->id,
                'metadata' => [
                    'customer_code' => $customerCode,
                    'type' => $type,
                    'created_by' => $userId,
                ],
                'user_id' => $userId,
            ]);

            return $customer;
        });
    }
}
