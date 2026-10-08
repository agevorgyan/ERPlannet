<?php

namespace App\Domain\Warehouse\Models;

use App\Domain\Branch\Models\Branch;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, HasUuids, BelongsToTenant, SoftDeletes;

    protected $table = 'warehouses';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'code',
        'name',
        'type',
        'address',
        'is_active',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class, 'warehouse_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class, 'warehouse_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'warehouse_id');
    }
}
