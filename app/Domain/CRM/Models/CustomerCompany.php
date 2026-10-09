<?php

namespace App\Domain\CRM\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCompany extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'customer_companies';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'legal_name',
        'trade_name',
        'tax_id',
        'registration_country',
        'legal_address',
        'physical_address',
        'website',
        'director_name',
        'accountant_name',
        'purchasing_manager_name',
        'contract_number',
        'contract_date',
        'credit_limit',
        'outstanding_balance',
        'payment_terms_days',
        'is_postpaid_allowed',
        'bank_account_details',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'is_postpaid_allowed' => 'boolean',
        'bank_account_details' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class, 'customer_id', 'customer_id');
    }
}
