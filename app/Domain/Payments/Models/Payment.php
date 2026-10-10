<?php

declare(strict_types=1);

namespace App\Domain\Payments\Models;

/**
 * Class Payment
 *
 * Domain model alias representing an order or invoice payment record.
 */
class Payment extends PaymentTransaction
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'order_id',
        'invoice_id',
        'pos_session_id',
        'gateway',
        'payment_method',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'refunded_amount',
        'payer_details',
        'gateway_response',
        'paid_at',
    ];
}
