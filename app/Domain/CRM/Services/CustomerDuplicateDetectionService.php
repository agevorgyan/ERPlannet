<?php

namespace App\Domain\CRM\Services;

use App\Domain\CRM\Models\Customer;

class CustomerDuplicateDetectionService
{
    /**
     * Find potential duplicate customers based on phone, email, or tax_id.
     *
     * @return array<array{id: string, name: string, phone: string|null, email: string|null, company_name: string|null, tax_id: string|null, matches: list<string>}>
     */
    public function findDuplicates(
        string $tenantId,
        ?string $phone = null,
        ?string $email = null,
        ?string $taxId = null,
        ?string $excludeCustomerId = null
    ): array {
        $normalizedPhone = PhoneNumberNormalizer::normalize($phone);
        $cleanEmail = $email ? strtolower(trim($email)) : null;
        $cleanTaxId = $taxId ? trim($taxId) : null;

        if (! $normalizedPhone && ! $cleanEmail && ! $cleanTaxId) {
            return [];
        }

        $query = Customer::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($normalizedPhone, $phone, $cleanEmail, $cleanTaxId) {
                if ($normalizedPhone) {
                    $q->orWhere('phone', $normalizedPhone)
                        ->orWhere('primary_phone', $normalizedPhone)
                        ->orWhere('phone', $phone);
                }
                if ($cleanEmail) {
                    $q->orWhereRaw('LOWER(email) = ?', [$cleanEmail])
                        ->orWhereRaw('LOWER(primary_email) = ?', [$cleanEmail]);
                }
                if ($cleanTaxId) {
                    $q->orWhere('tax_id', $cleanTaxId);
                }
            });

        if ($excludeCustomerId) {
            $query->where('id', '!=', $excludeCustomerId);
        }

        $candidates = $query->limit(10)->get();
        $results = [];

        foreach ($candidates as $cand) {
            $matches = [];
            if ($normalizedPhone && (
                $cand->phone === $normalizedPhone ||
                $cand->primary_phone === $normalizedPhone ||
                $cand->phone === $phone
            )) {
                $matches[] = 'phone';
            }

            if ($cleanEmail && (
                strtolower((string) $cand->email) === $cleanEmail ||
                strtolower((string) $cand->primary_email) === $cleanEmail
            )) {
                $matches[] = 'email';
            }

            if ($cleanTaxId && $cand->tax_id === $cleanTaxId) {
                $matches[] = 'tax_id';
            }

            if (! empty($matches)) {
                $results[] = [
                    'id' => $cand->id,
                    'customer_code' => $cand->customer_code,
                    'name' => $cand->full_name,
                    'type' => $cand->type,
                    'phone' => $cand->phone,
                    'email' => $cand->email,
                    'company_name' => $cand->company_name,
                    'tax_id' => $cand->tax_id,
                    'matches' => $matches,
                ];
            }
        }

        return $results;
    }
}
