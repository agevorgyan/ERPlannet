<?php

namespace App\Domain\Warehouse\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\IAM\Models\User;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'stock_movements';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'product_id',
        'product_variant_id',
        'stock_batch_id',
        'user_id',
        'type',
        'quantity',
        'unit_cost',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_cost' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
        'created_at' => 'datetime',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
