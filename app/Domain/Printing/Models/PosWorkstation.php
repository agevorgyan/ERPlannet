<?php

namespace App\Domain\Printing\Models;

use App\Domain\Branch\Models\Branch;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosWorkstation extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'pos_workstations';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'code',
        'identifier',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function printers(): HasMany
    {
        return $this->hasMany(Printer::class, 'workstation_id');
    }
}
