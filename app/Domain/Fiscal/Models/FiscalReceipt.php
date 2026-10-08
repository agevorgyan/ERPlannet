<?php

namespace App\Domain\Fiscal\Models;

use App\Domain\POS\Models\PosSession;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalReceipt extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'fiscal_receipts';

    protected $fillable = [
        'tenant_id',
        'order_id',
        'pos_session_id',
        'provider',
        'fiscal_number',
        'crn',
        'status',
        'total_amount',
        'tax_amount',
        'qr_payload',
        'provider_response',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'provider_response' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }
}
