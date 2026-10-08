<?php

namespace App\Domain\Manufacturing\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Warehouse\Models\StockBatch;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderItem extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'production_order_items';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'production_order_id',
        'product_id',
        'product_variant_id',
        'planned_quantity',
        'consumed_quantity',
        'unit_cost',
        'total_cost',
        'stock_batch_id',
        'created_at',
    ];

    protected $casts = [
        'planned_quantity' => 'float',
        'consumed_quantity' => 'float',
        'unit_cost' => 'float',
        'total_cost' => 'float',
        'created_at' => 'datetime',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }
}
