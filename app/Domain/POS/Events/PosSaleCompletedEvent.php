<?php

namespace App\Domain\POS\Events;

use App\Domain\Sales\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosSaleCompletedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public array $payments = []
    ) {}
}
