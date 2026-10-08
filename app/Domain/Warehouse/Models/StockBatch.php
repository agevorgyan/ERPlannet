<?php

namespace App\Domain\Warehouse\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'stock_batches';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'product_id',
        'product_variant_id',
        'batch_number',
        'quantity_on_hand',
        'quantity_reserved',
        'cost_price',
        'mfg_date',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity_on_hand' => 'float',
        'quantity_reserved' => 'float',
        'cost_price' => 'float',
        'mfg_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
