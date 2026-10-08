<?php

namespace App\Domain\POS\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\POS\Models\PosSession;
use App\Domain\POS\Services\PosReceiptNumberGenerator;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use App\Domain\Sales\Services\OrderNumberGenerator;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosCheckoutAction
{
    public function __construct(
        protected OrderNumberGenerator $orderNumberGenerator,
        protected PosReceiptNumberGenerator $receiptNumberGenerator,
        protected RecordStockMovementAction $recordStockMovement,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $posSessionId,
        array $items,
        array $payments,
        ?string $customerId = null,
        ?string $notes = null
    ): Order {
        if (empty($items)) {
            throw new \InvalidArgumentException('POS cart must contain at least one item.');
        }

        if (empty($payments)) {
            throw new \InvalidArgumentException('At least one payment method is required.');
        }

        $tenant = $this->tenantContext->getTenant();
        if (!$tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        return DB::transaction(function () use ($tenant, $posSessionId, $items, $payments, $customerId, $notes) {
            $session = PosSession::with('terminal')
                ->where('id', $posSessionId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$session->isOpen()) {
                throw new \InvalidArgumentException("POS Session {$session->session_number} is closed.");
            }

            $terminal = $session->terminal;
            $warehouseId = $terminal->warehouse_id;
            $branchId = $terminal->branch_id;

            // 1. Calculate items subtotal and total
            $subtotal = 0.0;
            $totalDiscount = 0.0;
            $calculatedItems = [];

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variant = !empty($itemData['product_variant_id'])
                    ? ProductVariant::findOrFail($itemData['product_variant_id'])
                    : null;

                $quantity = (float) $itemData['quantity'];
                if ($quantity <= 0) {
                    throw new \InvalidArgumentException("Invalid quantity for product {$product->sku}.");
                }

                $unitPrice = isset($itemData['unit_price'])
                    ? (float) $itemData['unit_price']
                    : (float) ($variant ? $variant->sale_price : $product->sale_price);

                $discountPercent = (float) ($itemData['discount_percent'] ?? 0.0);
                $lineSubtotal = round($quantity * $unitPrice, 2);
                $lineDiscount = round($lineSubtotal * ($discountPercent / 100), 2);
                $lineTotal = $lineSubtotal - $lineDiscount;

                $subtotal += $lineSubtotal;
                $totalDiscount += $lineDiscount;

                $calculatedItems[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $lineDiscount,
                    'total' => $lineTotal,
                ];
            }

            $orderTotal = round($subtotal - $totalDiscount, 2);

            // 2. Validate payments and calculate cash change
            $totalPaid = 0.0;
            $cashReceived = 0.0;
            $nonCashPaid = 0.0;

            foreach ($payments as $pay) {
                $amt = (float) $pay['amount'];
                if ($amt <= 0) {
                    throw new \InvalidArgumentException('Payment amount must be greater than zero.');
                }
                $totalPaid += $amt;
                if (($pay['gateway'] ?? '') === 'cash' || ($pay['method'] ?? '') === 'cash') {
                    $cashReceived += $amt;
                } else {
                    $nonCashPaid += $amt;
                }
            }

            if ($totalPaid < $orderTotal) {
                throw new \InvalidArgumentException(
                    "Insufficient payment: Order total is {$orderTotal} {$tenant->currency}, but only {$totalPaid} received."
                );
            }

            $changeGiven = 0.0;
            if ($cashReceived > 0) {
                $changeGiven = max(0.0, round($totalPaid - $orderTotal, 2));
            }

            $netCashKept = $cashReceived - $changeGiven;

            // 3. Create Order
            $orderNumber = $this->orderNumberGenerator->generate();
            $receiptNumber = $this->receiptNumberGenerator->generate($tenant);

            $order = Order::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branchId,
                'pos_terminal_id' => $terminal->id,
                'pos_session_id' => $session->id,
                'receipt_number' => $receiptNumber,
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'status' => 'delivered',
                'source' => 'pos',
                'delivery_type' => 'pickup',
                'currency' => $tenant->currency,
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'delivery_fee' => 0.00,
                'tax' => 0.00,
                'total' => $orderTotal,
                'payment_status' => 'paid',
                'customer_notes' => $notes,
                'placed_at' => now(),
                'delivered_at' => now(),
            ]);

            // 4. Create Order Items & Deduct Inventory
            foreach ($calculatedItems as $ci) {
                $pName = is_array($ci['product']->name)
                    ? ($ci['product']->name['hy'] ?? reset($ci['product']->name) ?? $ci['product']->sku)
                    : (string) $ci['product']->name;

                OrderItem::create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $order->id,
                    'product_id' => $ci['product']->id,
                    'variant_id' => $ci['variant']?->id,
                    'product_name' => $pName,
                    'product_sku' => $ci['variant']?->sku ?? $ci['product']->sku,
                    'quantity' => $ci['quantity'],
                    'unit_price' => $ci['unit_price'],
                    'discount' => $ci['discount'],
                    'total' => $ci['total'],
                    'created_at' => now(),
                ]);

                // Record stock movement (sale_delivery)
                $this->recordStockMovement->execute(
                    warehouseId: $warehouseId,
                    productId: $ci['product']->id,
                    productVariantId: $ci['variant']?->id,
                    type: 'sale_delivery',
                    quantity: $ci['quantity'],
                    unitCost: (float) ($ci['product']->cost_price ?? 0.0),
                    userId: $session->cashier_id,
                    referenceType: Order::class,
                    referenceId: $order->id,
                    notes: "POS checkout receipt {$receiptNumber} ({$order->order_number})"
                );
            }

            // 5. Record Payment Transactions
            foreach ($payments as $pay) {
                $payGateway = $pay['gateway'] ?? 'cash';
                $payMethod = $pay['method'] ?? 'cash';
                $payAmount = (float) $pay['amount'];

                // If cash payment and change was given, the actual net transaction is payAmount - changeGiven
                $actualTxAmount = ($payGateway === 'cash' || $payMethod === 'cash')
                    ? max(0.01, $payAmount - $changeGiven)
                    : $payAmount;

                PaymentTransaction::create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $order->id,
                    'pos_session_id' => $session->id,
                    'gateway' => $payGateway,
                    'payment_method' => $payMethod,
                    'transaction_id' => 'TX-POS-' . strtoupper(Str::random(10)),
                    'amount' => $actualTxAmount,
                    'currency' => $tenant->currency,
                    'status' => 'successful',
                    'paid_at' => now(),
                    'gateway_response' => [
                        'tendered' => $payAmount,
                        'change' => ($payGateway === 'cash' || $payMethod === 'cash') ? $changeGiven : 0.0,
                    ],
                ]);
            }

            // 6. Update POS Session running calculated cash balance
            if ($netCashKept > 0) {
                $session->closing_cash_calculated = (float) $session->closing_cash_calculated + $netCashKept;
                $session->save();
            }

            return $order->load(['items.product', 'paymentTransactions', 'posTerminal', 'posSession']);
        });
    }
}
