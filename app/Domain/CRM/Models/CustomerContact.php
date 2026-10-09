<?php

namespace App\Domain\CRM\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContact extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'customer_contacts';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'name',
        'position',
        'phone',
        'email',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
