<?php

namespace App\Domain\Procurement\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, HasUuids, BelongsToTenant, SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'tenant_id',
        'company_name',
        'legal_name',
        'tax_id',
        'contact_person',
        'email',
        'phone',
        'address',
        'bank_name',
        'bank_account',
        'currency',
        'payment_terms_days',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'payment_terms_days' => 'integer',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
}
