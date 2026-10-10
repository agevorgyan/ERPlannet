<?php

namespace App\Domain\Sales\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\Unit;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'order_items';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'product_id',
        'variant_id',
        'unit_id',
        'unit_name',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'original_price',
        'discount',
        'discount_type', // fixed, percent
        'discount_rate',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
        'unit_cost',
        'notes',
        'options_snapshot',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'options_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
