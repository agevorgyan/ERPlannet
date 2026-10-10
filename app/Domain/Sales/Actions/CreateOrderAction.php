<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Branch\Models\Branch;
use App\Domain\CRM\Models\Customer;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Sales\Services\OrderNumberGenerator;
use App\Domain\Sales\Services\OrderStatusStateMachine;
use App\Domain\Sales\Services\PricingEngine;
use App\Domain\Warehouse\Actions\ReserveStockAction;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateOrderAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected EntitlementManagerInterface $entitlementManager,
        protected OrderNumberGenerator $numberGenerator,
        protected PricingEngine $pricingEngine,
        protected OrderStatusStateMachine $stateMachine
    ) {}

    /**
     * @param array{
     *     branch_id: string,
     *     warehouse_id?: string|null,
     *     customer_id?: string|null,
     *     customer_address_id?: string|null,
     *     source?: string,
     *     order_type?: string,
     *     external_reference?: string|null,
     *     delivery_type?: string,
     *     delivery_fee?: float|int|string|null,
     *     discount?: float|int|string|null,
     *     order_discount?: float|int|string|null,
     *     promo_code?: string|null,
     *     customer_notes?: string|null,
     *     internal_notes?: string|null,
     *     scheduled_for?: string|null,
     *     status?: string|null,
     *     responsible_employee_id?: string|null,
     *     pos_terminal_id?: string|null,
     *     pos_session_id?: string|null,
     *     allow_price_override?: bool,
     *     items: array<int, array{
     *         product_id: string,
     *         variant_id?: string|null,
     *         quantity: float|int|string,
     *         unit_price?: float|int|string|null,
     *         discount?: float|int|string|null,
     *         discount_type?: string|null,
     *         discount_rate?: float|int|string|null,
     *         tax_rate?: float|int|string|null,
     *         notes?: string|null
     *     }>
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

        // 3. Resolve Warehouse
        $warehouseId = $data['warehouse_id'] ?? null;
        if (! $warehouseId) {
            $defaultWarehouse = Warehouse::where('branch_id', $branch->id)->where('is_active', true)->first();
            $warehouseId = $defaultWarehouse?->id;
        }

        // 4. Validate Customer if provided
        $customer = null;
        if (! empty($data['customer_id'])) {
            $customer = Customer::with(['defaultAddress', 'company'])->findOrFail($data['customer_id']);
        }

        if (empty($data['items'])) {
            throw new InvalidArgumentException('Order must contain at least one item.');
        }

        // 5. Centralized Pricing Engine Calculation
        $orderDiscount = $data['order_discount'] ?? ($data['discount'] ?? 0.00);
        $pricing = $this->pricingEngine->calculate($data['items'], [
            'order_discount' => $orderDiscount,
            'promo_code' => $data['promo_code'] ?? null,
            'delivery_fee' => $data['delivery_fee'] ?? 0.00,
            'delivery_type' => $data['delivery_type'] ?? 'delivery',
            'allow_price_override' => (bool) ($data['allow_price_override'] ?? false),
        ]);

        return DB::transaction(function () use ($data, $tenant, $branch, $warehouseId, $customer, $pricing, $userId) {
            // 6. Generate Order Number
            $orderNumber = $this->numberGenerator->generate();

            $initialStatus = $data['status'] ?? 'new';
            $validInitial = ['draft', 'new', 'confirmed'];
            if (! in_array($initialStatus, $validInitial, true)) {
                $initialStatus = 'new';
            }

            // 7. Create Order
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouseId,
                'customer_id' => $customer?->id,
                'customer_address_id' => $data['customer_address_id'] ?? null,
                'order_number' => $orderNumber,
                'status' => $initialStatus,
                'source' => $data['source'] ?? 'direct',
                'order_type' => $data['order_type'] ?? 'standard',
                'external_reference' => $data['external_reference'] ?? null,
                'delivery_type' => $data['delivery_type'] ?? 'delivery',
                'currency' => $tenant->currency ?? 'AMD',
                'subtotal' => $pricing['subtotal'],
                'item_discounts_total' => $pricing['item_discounts_total'],
                'order_discount' => $pricing['order_discount'],
                'discount' => round($pricing['item_discounts_total'] + $pricing['order_discount'] + $pricing['promo_discount'], 2),
                'promo_code' => $pricing['promo_code'],
                'promo_discount' => $pricing['promo_discount'],
                'delivery_fee' => $pricing['delivery_fee'],
                'tax' => $pricing['tax'],
                'total' => $pricing['total'],
                'paid_amount' => 0.00,
                'balance_due' => $pricing['total'],
                'pos_terminal_id' => $data['pos_terminal_id'] ?? null,
                'pos_session_id' => $data['pos_session_id'] ?? null,
                'responsible_employee_id' => $data['responsible_employee_id'] ?? $userId,
                'payment_status' => 'unpaid',
                'customer_notes' => $data['customer_notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'scheduled_for' => $data['scheduled_for'] ?? null,
                'placed_at' => now(),
                'confirmed_at' => $initialStatus === 'confirmed' ? now() : null,
            ]);

            // 8. Snapshot Customer
            if ($customer) {
                $order->snapshotCustomer($customer);
            }

            // 9. Create Order Items
            foreach ($pricing['items'] as $itemData) {
                OrderItem::create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'variant_id' => $itemData['variant_id'],
                    'unit_id' => $itemData['unit_id'],
                    'unit_name' => $itemData['unit_name'],
                    'product_name' => $itemData['product_name'],
                    'product_sku' => $itemData['product_sku'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'original_price' => $itemData['original_price'],
                    'discount' => $itemData['discount'],
                    'discount_type' => $itemData['discount_type'],
                    'discount_rate' => $itemData['discount_rate'],
                    'tax_rate' => $itemData['tax_rate'],
                    'tax_amount' => $itemData['tax_amount'],
                    'subtotal' => $itemData['subtotal'],
                    'total' => $itemData['total'],
                    'unit_cost' => $itemData['unit_cost'],
                    'notes' => $itemData['notes'],
                    'created_at' => now(),
                ]);
            }

            // 10. Log Initial Status
            OrderStatusHistory::create([
                'tenant_id' => $tenant->id,
                'order_id' => $order->id,
                'user_id' => $userId,
                'from_status' => null,
                'to_status' => $initialStatus,
                'comment' => "Order created via {$order->source}.",
            ]);

            // 11. Reserve inventory if created directly in confirmed status
            if ($initialStatus === 'confirmed' && $order->warehouse_id) {
                $order->load('items');
                $order->update(['metadata' => ['stock_reserved' => true]]);
                foreach ($order->items as $item) {
                    try {
                        app(ReserveStockAction::class)->execute(
                            warehouseId: $order->warehouse_id,
                            productId: $item->product_id,
                            productVariantId: $item->variant_id,
                            quantity: (float) $item->quantity
                        );
                    } catch (\Throwable $e) {
                        // Stock reservation logged
                    }
                }
            }

            // 12. Consume Quota
            $this->entitlementManager->consume('limit.orders_monthly', 1);

            return $order->load(['items.product', 'items.unit', 'branch', 'warehouse', 'customer', 'statusHistories']);
        });
    }
}
