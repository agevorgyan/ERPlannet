<?php

namespace App\Domain\Quality\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspectionItem extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'quality_inspection_items';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'quality_inspection_id',
        'parameter_name',
        'critical_control_point',
        'target_value',
        'min_value',
        'max_value',
        'actual_value',
        'unit',
        'is_passed',
        'deviation_notes',
        'created_at',
    ];

    protected $casts = [
        'min_value' => 'float',
        'max_value' => 'float',
        'is_passed' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(QualityInspection::class, 'quality_inspection_id');
    }
}
