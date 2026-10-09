<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\CRM\Models\Customer;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Sales\Services\OrderNumberGenerator;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateOrderAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected EntitlementManagerInterface $entitlementManager,
        protected OrderNumberGenerator $numberGenerator
    ) {}

    /**
     * @param array{
     *     branch_id: string,
     *     customer_id?: string|null,
     *     customer_address_id?: string|null,
     *     delivery_type?: string,
     *     delivery_fee?: float,
     *     discount?: float,
     *     customer_notes?: string|null,
     *     internal_notes?: string|null,
     *     scheduled_for?: string|null,
     *     items: array<array{product_id: string, variant_id?: string|null, quantity: float, notes?: string|null}>
     * } $data
     */
    public function execute(array $data, ?string $userId = null): Order
    {
        // 1. Quota Check
        $this->entitlementManager->assertCan('limit.orders_monthly', 1);

        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new InvalidArgumentException('No active tenant context.');
        }

        // 2. Validate Branch
        $branch = Branch::where('id', $data['branch_id'])->where('is_active', true)->firstOrFail();

        // 3. Validate Customer if provided
        if (! empty($data['customer_id'])) {
            Customer::findOrFail($data['customer_id']);
        }

        if (empty($data['items'])) {
            throw new InvalidArgumentException('Order must contain at least one item.');
        }

        return DB::transaction(function () use ($data, $tenant, $branch, $userId) {
            $subtotal = 0.00;
            $itemsToCreate = [];

            // 4. Resolve Products & Calculate Subtotal securely from DB
            foreach ($data['items'] as $itemData) {
                $product = Product::where('id', $itemData['product_id'])->where('is_active', true)->firstOrFail();

                $unitPrice = (float) $product->sale_price;
                $variant = null;

                if (! empty($itemData['variant_id'])) {
                    $variant = ProductVariant::where('id', $itemData['variant_id'])
                        ->where('product_id', $product->id)
                        ->where('is_active', true)
                        ->firstOrFail();

                    $unitPrice = $variant->getEffectivePrice();
                }

                $qty = (float) $itemData['quantity'];
                $itemTotal = round($unitPrice * $qty, 2);
                $subtotal += $itemTotal;

                $itemsToCreate[] = [
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'product_name' => $product->getLocalizedName(),
                    'product_sku' => $variant?->sku ?? $product->sku,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => 0.00,
                    'total' => $itemTotal,
                    'notes' => $itemData['notes'] ?? null,
                ];
            }

            $discount = (float) ($data['discount'] ?? 0.00);
            $deliveryFee = (float) ($data['delivery_fee'] ?? 0.00);
            $tax = 0.00; // Can be configured by tenant settings
            $total = max(0.00, round(($subtotal - $discount + $deliveryFee + $tax), 2));

            // 5. Generate Order Number
            $orderNumber = $this->numberGenerator->generate();

            // 6. Create Order
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_address_id' => $data['customer_address_id'] ?? null,
                'order_number' => $orderNumber,
                'status' => 'new',
                'source' => $data['source'] ?? 'direct',
                'delivery_type' => $data['delivery_type'] ?? 'delivery',
                'currency' => $tenant->currency,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'tax' => $tax,
                'total' => $total,
                'payment_status' => 'unpaid',
                'customer_notes' => $data['customer_notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'scheduled_for' => $data['scheduled_for'] ?? null,
            ]);

            // 7. Create Order Items
            foreach ($itemsToCreate as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            // 8. Log initial status
            OrderStatusHistory::create([
                'tenant_id' => $tenant->id,
                'order_id' => $order->id,
                'user_id' => $userId,
                'from_status' => null,
                'to_status' => 'new',
                'comment' => 'Order created.',
            ]);

            // 9. Consume quota
            $this->entitlementManager->consume('limit.orders_monthly', 1);

            return $order->load(['items', 'branch', 'customer', 'statusHistories']);
        });
    }
}
