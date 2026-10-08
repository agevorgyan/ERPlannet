<?php

namespace App\Domain\Manufacturing\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\Unit;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeItem extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'recipe_items';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'recipe_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_id',
        'waste_percentage',
        'sort_order',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'float',
        'waste_percentage' => 'float',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
