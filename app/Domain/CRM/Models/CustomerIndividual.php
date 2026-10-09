<?php

namespace App\Domain\CRM\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerIndividual extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'customer_individuals';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'first_name',
        'last_name',
        'middle_name',
        'birth_date',
        'gender',
        'preferred_language',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
