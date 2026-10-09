<?php

namespace App\Domain\Procurement\Models;

use App\Domain\Catalog\Models\Product;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'tenant_id',
        'company_name',
        'legal_name',
        'tax_id',
        'contact_person',
        'email',
        'phone',
        'website',
        'address',
        'legal_address',
        'shipping_address',
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

    public function couriers(): HasMany
    {
        return $this->hasMany(SupplierCourier::class, 'supplier_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'supplier_products', 'supplier_id', 'product_id')
            ->using(SupplierProduct::class)
            ->withPivot(['supply_price', 'lead_time_days'])
            ->withTimestamps();
    }
}
