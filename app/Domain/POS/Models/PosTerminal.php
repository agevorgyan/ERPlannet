<?php

namespace App\Domain\POS\Models;

use App\Domain\Branch\Models\Branch;
use App\Domain\Sales\Models\Order;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PosTerminal extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'pos_terminals';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'warehouse_id',
        'code',
        'name',
        'device_uid',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PosSession::class, 'pos_terminal_id');
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(PosSession::class, 'pos_terminal_id')
            ->where('status', 'open');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'pos_terminal_id');
    }
}
