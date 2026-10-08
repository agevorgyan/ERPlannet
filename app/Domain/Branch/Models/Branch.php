<?php

namespace App\Domain\Branch\Models;

use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, HasUuids, BelongsToTenant, SoftDeletes;

    protected $table = 'branches';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'address',
        'phone',
        'is_main',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'branch_id');
    }
}
