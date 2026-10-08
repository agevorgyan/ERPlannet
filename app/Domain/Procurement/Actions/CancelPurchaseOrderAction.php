<?php

namespace App\Domain\Procurement\Actions;

use App\Domain\Procurement\Models\PurchaseOrder;

class CancelPurchaseOrderAction
{
    public function execute(string $purchaseOrderId): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($purchaseOrderId);

        if (in_array($po->status, ['received', 'cancelled'], true)) {
            throw new \InvalidArgumentException("Purchase order cannot be cancelled in status: {$po->status}.");
        }

        // Check if any items were partially received
        $receivedCount = $po->items()->where('quantity_received', '>', 0)->count();
        if ($receivedCount > 0) {
            throw new \InvalidArgumentException('Cannot cancel purchase order with received goods. Please perform stock adjustment or return.');
        }

        $po->status = 'cancelled';
        $po->save();

        return $po;
    }
}
