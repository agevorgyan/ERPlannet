<?php

namespace App\Domain\POS\Models;

use App\Domain\IAM\Models\User;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosZReport extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'pos_z_reports';

    protected $fillable = [
        'tenant_id',
        'pos_session_id',
        'pos_terminal_id',
        'cashier_id',
        'z_report_number',
        'opened_at',
        'closed_at',
        'opening_cash',
        'total_sales_amount',
        'total_cash_sales',
        'total_card_sales',
        'total_other_sales',
        'total_tax_amount',
        'total_refunds_amount',
        'total_discounts_amount',
        'cash_in_amount',
        'cash_out_amount',
        'expected_cash_in_drawer',
        'closing_cash_declared',
        'cash_difference',
        'sales_count',
        'refunds_count',
        'void_count',
        'tax_breakdown',
        'payments_breakdown',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'total_sales_amount' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_card_sales' => 'decimal:2',
        'total_other_sales' => 'decimal:2',
        'total_tax_amount' => 'decimal:2',
        'total_refunds_amount' => 'decimal:2',
        'total_discounts_amount' => 'decimal:2',
        'cash_in_amount' => 'decimal:2',
        'cash_out_amount' => 'decimal:2',
        'expected_cash_in_drawer' => 'decimal:2',
        'closing_cash_declared' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'tax_breakdown' => 'array',
        'payments_breakdown' => 'array',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
