<?php

namespace App\Domain\Procurement\Actions;

use App\Domain\Procurement\Models\PurchaseOrder;

class SubmitPurchaseOrderAction
{
    public function execute(string $purchaseOrderId): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($purchaseOrderId);

        if ($po->status !== 'draft') {
            throw new \InvalidArgumentException("Purchase order cannot be submitted from status: {$po->status}.");
        }

        $po->status = 'ordered';
        $po->save();

        return $po;
    }
}
