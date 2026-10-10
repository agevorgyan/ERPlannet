<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderStatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RescheduleOrderAction
{
    /**
     * Reschedule the fulfillment date of an order without modifying creation or placed timestamps.
     */
    public function execute(Order $order, string|Carbon $newScheduledFor, ?string $userId = null, ?string $reason = null): Order
    {
        if (! $order->canBeRescheduled()) {
            throw new InvalidArgumentException("Cannot reschedule order {$order->order_number} in status {$order->status}.");
        }

        $parsedDate = $newScheduledFor instanceof Carbon ? $newScheduledFor : Carbon::parse($newScheduledFor);
        $oldDate = $order->scheduled_for ? $order->scheduled_for->toIso8601String() : 'None';
        $originalPlacedAt = $order->placed_at;
        $originalCreatedAt = $order->created_at;

        return DB::transaction(function () use ($order, $parsedDate, $oldDate, $originalPlacedAt, $userId, $reason) {
            $order->update([
                'scheduled_for' => $parsedDate,
                'placed_at' => $originalPlacedAt, // explicitly preserve placed_at
            ]);

            // Audit history
            OrderStatusHistory::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'user_id' => $userId,
                'from_status' => $order->status,
                'to_status' => $order->status,
                'comment' => "Rescheduled fulfillment from {$oldDate} to {$parsedDate->toIso8601String()}".($reason ? ". Reason: {$reason}" : ''),
            ]);

            return $order->fresh(['statusHistories']);
        });
    }
}
