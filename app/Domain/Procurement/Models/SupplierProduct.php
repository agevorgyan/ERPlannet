<?php

namespace App\Domain\Procurement\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SupplierProduct extends Pivot
{
    use BelongsToTenant, HasUuids;

    protected $table = 'supplier_products';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'product_id',
        'supply_price',
        'lead_time_days',
    ];

    protected $casts = [
        'supply_price' => 'decimal:2',
        'lead_time_days' => 'integer',
    ];
}
