<?php

namespace App\Domain\Manufacturing\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\IAM\Models\User;
use App\Domain\Quality\Models\QualityInspection;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'production_orders';

    protected $fillable = [
        'tenant_id',
        'order_number',
        'recipe_id',
        'product_id',
        'product_variant_id',
        'source_warehouse_id',
        'target_warehouse_id',
        'user_id',
        'status',
        'planned_quantity',
        'actual_quantity',
        'waste_quantity',
        'unit_cost',
        'total_cost',
        'stock_batch_id',
        'planned_start_date',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'planned_quantity' => 'float',
        'actual_quantity' => 'float',
        'waste_quantity' => 'float',
        'unit_cost' => 'float',
        'total_cost' => 'float',
        'planned_start_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class, 'production_order_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(QualityInspection::class, 'production_order_id');
    }
}
