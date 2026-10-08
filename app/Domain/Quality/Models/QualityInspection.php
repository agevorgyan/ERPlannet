<?php

namespace App\Domain\Quality\Models;

use App\Domain\IAM\Models\User;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityInspection extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'quality_inspections';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'production_order_id',
        'inspector_id',
        'inspection_number',
        'status',
        'standard_applied',
        'overall_score',
        'notes',
        'inspected_at',
        'created_at',
    ];

    protected $casts = [
        'overall_score' => 'float',
        'inspected_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QualityInspectionItem::class, 'quality_inspection_id');
    }
}
