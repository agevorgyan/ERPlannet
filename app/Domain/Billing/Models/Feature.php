<?php

namespace App\Domain\Billing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'features';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'type', // 'boolean', 'limit'
        'module',
        'description',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features', 'feature_id', 'plan_id')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function isLimit(): bool
    {
        return $this->type === 'limit';
    }

    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }
}
