<?php

namespace App\Domain\Procurement\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierCourier extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'supplier_couriers';

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'name',
        'phone',
        'vehicle_model',
        'license_plate',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
