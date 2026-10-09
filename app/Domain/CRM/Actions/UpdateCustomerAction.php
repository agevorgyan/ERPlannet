<?php

namespace App\Domain\CRM\Actions;

use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerActivity;
use App\Domain\CRM\Models\CustomerCompany;
use App\Domain\CRM\Models\CustomerIndividual;
use App\Domain\CRM\Services\PhoneNumberNormalizer;
use Illuminate\Support\Facades\DB;

class UpdateCustomerAction
{
    /**
     * Update an existing customer with CRM profile synchronization.
     */
    public function execute(Customer $customer, array $data, ?string $userId = null): Customer
    {
        return DB::transaction(function () use ($customer, $data, $userId) {
            $type = $data['type'] ?? $customer->type;

            $updateData = [];

            if (isset($data['first_name'])) {
                $updateData['first_name'] = $data['first_name'];
            }
            if (isset($data['last_name'])) {
                $updateData['last_name'] = $data['last_name'];
            }
            if (isset($data['company_name'])) {
                $updateData['company_name'] = $data['company_name'];
            }
            if (isset($data['tax_id'])) {
                $updateData['tax_id'] = $data['tax_id'];
            }
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
                $updateData['primary_email'] = $data['email'];
            }
            if (isset($data['phone'])) {
                $normalized = PhoneNumberNormalizer::normalize($data['phone']) ?? $data['phone'];
                $updateData['phone'] = $normalized;
                $updateData['primary_phone'] = $normalized;
            }
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            if (isset($data['primary_branch_id'])) {
                $updateData['primary_branch_id'] = $data['primary_branch_id'];
            }
            if (isset($data['assigned_manager_id'])) {
                $updateData['assigned_manager_id'] = $data['assigned_manager_id'];
            }
            if (isset($data['acquisition_source_id'])) {
                $updateData['acquisition_source_id'] = $data['acquisition_source_id'];
            }
            if (isset($data['custom_discount_percent'])) {
                $updateData['custom_discount_percent'] = (float) $data['custom_discount_percent'];
            }
            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }
            if (isset($data['marketing_sms_consent'])) {
                $updateData['marketing_sms_consent'] = (bool) $data['marketing_sms_consent'];
            }
            if (isset($data['marketing_email_consent'])) {
                $updateData['marketing_email_consent'] = (bool) $data['marketing_email_consent'];
            }
            if (isset($data['marketing_calls_consent'])) {
                $updateData['marketing_calls_consent'] = (bool) $data['marketing_calls_consent'];
            }

            // Display name
            if ($type === Customer::TYPE_COMPANY) {
                $updateData['display_name'] = $updateData['company_name'] ?? $customer->company_name;
            } else {
                $fn = $updateData['first_name'] ?? $customer->first_name;
                $ln = $updateData['last_name'] ?? $customer->last_name;
                $updateData['display_name'] = trim("{$fn} {$ln}");
            }

            $customer->update($updateData);

            // Update sub-profile
            if ($type === Customer::TYPE_COMPANY) {
                $company = $customer->company ?: new CustomerCompany([
                    'tenant_id' => $customer->tenant_id,
                    'customer_id' => $customer->id,
                ]);

                $company->legal_name = $customer->company_name ?? 'Company';
                $company->trade_name = $data['trade_name'] ?? $company->trade_name;
                $company->tax_id = $customer->tax_id;
                $company->legal_address = $data['legal_address'] ?? $company->legal_address;
                $company->physical_address = $data['physical_address'] ?? $company->physical_address;
                $company->website = $data['website'] ?? $company->website;
                $company->director_name = $data['director_name'] ?? $company->director_name;
                $company->accountant_name = $data['accountant_name'] ?? $company->accountant_name;
                $company->purchasing_manager_name = $data['purchasing_manager_name'] ?? $company->purchasing_manager_name;
                if (isset($data['credit_limit'])) {
                    $company->credit_limit = (float) $data['credit_limit'];
                }
                if (isset($data['payment_terms_days'])) {
                    $company->payment_terms_days = (int) $data['payment_terms_days'];
                }
                if (isset($data['is_postpaid_allowed'])) {
                    $company->is_postpaid_allowed = (bool) $data['is_postpaid_allowed'];
                }
                $company->save();
            } else {
                $indiv = $customer->individual ?: new CustomerIndividual([
                    'tenant_id' => $customer->tenant_id,
                    'customer_id' => $customer->id,
                ]);

                $indiv->first_name = $customer->first_name ?? '';
                $indiv->last_name = $customer->last_name;
                $indiv->middle_name = $data['middle_name'] ?? $indiv->middle_name;
                if (isset($data['birth_date'])) {
                    $indiv->birth_date = $data['birth_date'];
                }
                if (isset($data['gender'])) {
                    $indiv->gender = $data['gender'];
                }
                if (isset($data['preferred_language'])) {
                    $indiv->preferred_language = $data['preferred_language'];
                }
                $indiv->save();
            }

            CustomerActivity::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'type' => CustomerActivity::TYPE_STATUS_CHANGED,
                'title' => 'Տվյալների թարմացում',
                'content' => 'Հաճախորդի պրոֆիլի տվյալները հաջողությամբ փոփոխվել են',
                'reference_type' => Customer::class,
                'reference_id' => $customer->id,
                'metadata' => [
                    'updated_fields' => array_keys($updateData),
                ],
                'user_id' => $userId,
            ]);

            return $customer->fresh(['individual', 'company', 'contacts', 'addresses', 'loyaltyAccount']);
        });
    }
}
