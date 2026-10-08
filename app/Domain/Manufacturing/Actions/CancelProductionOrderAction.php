<?php

namespace App\Domain\Manufacturing\Actions;

use App\Domain\Manufacturing\Models\ProductionOrder;

class CancelProductionOrderAction
{
    public function execute(string $productionOrderId): ProductionOrder
    {
        $order = ProductionOrder::findOrFail($productionOrderId);

        if (in_array($order->status, ['in_progress', 'completed', 'cancelled'], true)) {
            throw new \InvalidArgumentException(
                "Cannot cancel production order in status: {$order->status}. Materials may have already been consumed."
            );
        }

        $order->status = 'cancelled';
        $order->save();

        return $order;
    }
}
