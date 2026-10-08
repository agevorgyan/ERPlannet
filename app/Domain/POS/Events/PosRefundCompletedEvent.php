<?php

namespace App\Domain\POS\Events;

use App\Domain\POS\Models\PosRefund;
use App\Domain\Sales\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosRefundCompletedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PosRefund $refund,
        public Order $order
    ) {}
}
