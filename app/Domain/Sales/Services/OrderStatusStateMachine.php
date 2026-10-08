<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Exceptions\InvalidStatusTransitionException;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class OrderStatusStateMachine
{
    /** @var array<string, array<string>> */
    protected array $transitions = [
        'new' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['packed', 'cancelled'],
        'packed' => ['delivery', 'delivered', 'cancelled'],
        'delivery' => ['delivered', 'cancelled'],
        'delivered' => [], // Terminal state
        'cancelled' => [], // Terminal state
    ];

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->transitions[$from] ?? []);
    }

    public function transition(Order $order, string $newStatus, ?string $userId = null, ?string $comment = null): Order
    {
        $currentStatus = $order->status;

        if ($currentStatus === $newStatus) {
            return $order;
        }

        if (!$this->canTransition($currentStatus, $newStatus)) {
            throw new InvalidStatusTransitionException($currentStatus, $newStatus);
        }

        return DB::transaction(function () use ($order, $currentStatus, $newStatus, $userId, $comment) {
            $updates = ['status' => $newStatus];

            if ($newStatus === 'delivered') {
                $updates['delivered_at'] = now();

                // Increment customer total spent and orders count if customer is attached
                if ($order->customer_id && $order->customer) {
                    $order->customer->increment('orders_count');
                    $order->customer->increment('total_spent', $order->total);
                    $order->customer->update(['last_ordered_at' => now()]);
                }
            } elseif ($newStatus === 'cancelled') {
                $updates['cancelled_at'] = now();
            }

            $order->update($updates);

            // Record status history
            OrderStatusHistory::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'user_id' => $userId,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'comment' => $comment,
            ]);

            return $order->fresh(['statusHistories']);
        });
    }

    public function getAvailableTransitions(string $currentStatus): array
    {
        return $this->transitions[$currentStatus] ?? [];
    }
}
