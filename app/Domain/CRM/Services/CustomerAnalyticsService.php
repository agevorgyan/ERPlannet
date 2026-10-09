<?php

namespace App\Domain\CRM\Services;

use App\Domain\CRM\Models\Customer;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsService
{
    /**
     * Compute and update metrics for a customer.
     */
    public function recalculate(Customer $customer): Customer
    {
        $orders = Order::withoutGlobalScopes()
            ->where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->get();

        $totalCount = $orders->count();
        $completedOrders = $orders->whereIn('status', ['delivered', 'completed', 'paid']);
        $completedCount = $completedOrders->count();
        $canceledCount = $orders->where('status', 'cancelled')->count();
        $returnedCount = $orders->where('payment_status', 'refunded')->count();

        $totalSpent = (float) $completedOrders->sum('total');
        $aov = $completedCount > 0 ? round($totalSpent / $completedCount, 2) : 0.00;

        $firstOrder = $orders->sortBy('placed_at')->first();
        $lastOrder = $orders->sortByDesc('placed_at')->first();

        $firstOrderedAt = $firstOrder?->placed_at ? Carbon::parse($firstOrder->placed_at) : null;
        $lastOrderedAt = $lastOrder?->placed_at ? Carbon::parse($lastOrder->placed_at) : null;

        $avgFrequencyDays = 0.00;
        if ($completedCount > 1 && $firstOrderedAt && $lastOrderedAt) {
            $diffDays = $firstOrderedAt->diffInDays($lastOrderedAt);
            $avgFrequencyDays = round($diffDays / ($completedCount - 1), 2);
        }

        // RFM Score (0-100)
        $score = $this->calculateRfmScore($lastOrderedAt, $completedCount, $totalSpent);

        $customer->update([
            'orders_count' => $totalCount,
            'canceled_orders_count' => $canceledCount,
            'returned_orders_count' => $returnedCount,
            'total_spent' => $totalSpent,
            'average_order_value' => $aov,
            'lifetime_value' => $totalSpent,
            'first_ordered_at' => $firstOrderedAt,
            'last_ordered_at' => $lastOrderedAt,
            'average_order_frequency_days' => $avgFrequencyDays,
            'customer_score' => $score,
        ]);

        return $customer;
    }

    /**
     * Calculate composite RFM customer score (0 to 100).
     */
    public function calculateRfmScore(?Carbon $lastOrderedAt, int $frequency, float $monetary): int
    {
        if (! $lastOrderedAt || $frequency === 0) {
            return 20; // baseline prospect
        }

        // Recency score (max 35 pts)
        $daysSinceLast = Carbon::now()->diffInDays($lastOrderedAt);
        $recencyScore = match (true) {
            $daysSinceLast <= 7 => 35,
            $daysSinceLast <= 30 => 28,
            $daysSinceLast <= 60 => 20,
            $daysSinceLast <= 90 => 12,
            default => 5,
        };

        // Frequency score (max 35 pts)
        $frequencyScore = match (true) {
            $frequency >= 20 => 35,
            $frequency >= 10 => 28,
            $frequency >= 5 => 20,
            $frequency >= 2 => 14,
            default => 7,
        };

        // Monetary score (max 30 pts)
        $monetaryScore = match (true) {
            $monetary >= 500000 => 30,
            $monetary >= 200000 => 24,
            $monetary >= 100000 => 18,
            $monetary >= 30000 => 12,
            default => 6,
        };

        return min(100, $recencyScore + $frequencyScore + $monetaryScore);
    }

    /**
     * Get top purchased products for customer.
     *
     * @return array<array{product_id: string, product_name: string, product_sku: string, total_quantity: float, total_spent: float, orders_count: int}>
     */
    public function getTopProducts(Customer $customer, int $limit = 5): array
    {
        $orderIds = Order::withoutGlobalScopes()
            ->where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['delivered', 'completed', 'paid'])
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            return [];
        }

        return OrderItem::withoutGlobalScopes()
            ->whereIn('order_id', $orderIds)
            ->select(
                'product_id',
                'product_name',
                'product_sku',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('COUNT(DISTINCT order_id) as orders_count')
            )
            ->groupBy('product_id', 'product_name', 'product_sku')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'product_sku' => $row->product_sku,
                'total_quantity' => (float) $row->total_quantity,
                'total_spent' => (float) $row->total_spent,
                'orders_count' => (int) $row->orders_count,
            ])
            ->toArray();
    }
}
