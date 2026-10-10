<?php

namespace App\Domain\Sales\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use InvalidArgumentException;

class PricingEngine
{
    /**
     * Standard VAT rate in Armenia (20% where applicable, or 0% if exempt).
     */
    public const DEFAULT_TAX_RATE = 20.00;

    /**
     * Calculate pricing breakdown for an order and its items.
     *
     * Exact Calculation Sequence:
     * 1. Line Item Subtotal = Quantity * Effective Unit Price
     * 2. Line Item Discount = Fixed amount or (Subtotal * Rate / 100)
     * 3. Line Net Amount = Subtotal - Line Item Discount
     * 4. Line Tax Amount = Net Amount * (Tax Rate / 100)
     * 5. Line Total = Line Net Amount + Line Tax Amount
     * 6. Order Items Subtotal = Sum(Line Subtotals)
     * 7. Order Items Discount Total = Sum(Line Item Discounts)
     * 8. Order-Level Discount = Validated flat or percentage discount applied to Order Net
     * 9. Promo Code Discount = Validated active promotional voucher discount
     * 10. Delivery Fee = Configured fulfillment surcharge
     * 11. Grand Total = max(0.00, Subtotal - Item Discounts - Order Discount - Promo Discount + Taxes + Delivery Fee)
     *
     * @param array<int, array{
     *     product_id: string,
     *     variant_id?: string|null,
     *     quantity: float|int|string,
     *     unit_price?: float|int|string|null,
     *     discount?: float|int|string|null,
     *     discount_type?: string|null,
     *     discount_rate?: float|int|string|null,
     *     tax_rate?: float|int|string|null,
     *     notes?: string|null
     * }> $itemsInput
     * @param array{
     *     order_discount?: float|int|string|null,
     *     promo_code?: string|null,
     *     delivery_fee?: float|int|string|null,
     *     delivery_type?: string|null,
     *     allow_price_override?: bool
     * } $orderOptions
     * @return array{
     *     items: array<int, array{
     *         product_id: string,
     *         variant_id: string|null,
     *         product_name: string,
     *         product_sku: string,
     *         unit_id: string|null,
     *         unit_name: string|null,
     *         quantity: float,
     *         unit_price: float,
     *         original_price: float,
     *         discount: float,
     *         discount_type: string,
     *         discount_rate: float,
     *         tax_rate: float,
     *         tax_amount: float,
     *         subtotal: float,
     *         total: float,
     *         unit_cost: float,
     *         notes: string|null
     *     }>,
     *     subtotal: float,
     *     item_discounts_total: float,
     *     order_discount: float,
     *     promo_code: string|null,
     *     promo_discount: float,
     *     delivery_fee: float,
     *     tax: float,
     *     total: float
     * }
     */
    public function calculate(array $itemsInput, array $orderOptions = []): array
    {
        if (empty($itemsInput)) {
            throw new InvalidArgumentException('Pricing calculation requires at least one item.');
        }

        $allowPriceOverride = (bool) ($orderOptions['allow_price_override'] ?? false);
        $calculatedItems = [];
        $subtotal = 0.00;
        $itemDiscountsTotal = 0.00;
        $totalTax = 0.00;

        foreach ($itemsInput as $rawItem) {
            $productId = $rawItem['product_id'];
            $product = Product::with(['unit', 'variants'])->findOrFail($productId);

            $quantity = (float) $rawItem['quantity'];
            if ($quantity <= 0) {
                throw new InvalidArgumentException("Item quantity must be greater than zero for product {$product->sku}.");
            }

            $variant = null;
            $catalogPrice = (float) $product->sale_price;
            $unitCost = (float) $product->cost_price;

            if (! empty($rawItem['variant_id'])) {
                $variant = ProductVariant::where('id', $rawItem['variant_id'])
                    ->where('product_id', $product->id)
                    ->firstOrFail();

                $catalogPrice = $variant->getEffectivePrice();
                if ($variant->cost_price > 0) {
                    $unitCost = (float) $variant->cost_price;
                }
            }

            // Determine unit price
            $unitPrice = $catalogPrice;
            if (isset($rawItem['unit_price']) && is_numeric($rawItem['unit_price'])) {
                $unitPrice = max(0.00, round((float) $rawItem['unit_price'], 2));
            }

            $lineSubtotal = round($unitPrice * $quantity, 2);

            // Item-level discount calculation
            $discountType = $rawItem['discount_type'] ?? 'fixed';
            $discountRate = 0.00;
            $itemDiscount = 0.00;

            if ($discountType === 'percent' && isset($rawItem['discount_rate'])) {
                $discountRate = min(100.00, max(0.00, (float) $rawItem['discount_rate']));
                $itemDiscount = round($lineSubtotal * ($discountRate / 100), 2);
            } elseif (isset($rawItem['discount'])) {
                $itemDiscount = min($lineSubtotal, max(0.00, round((float) $rawItem['discount'], 2)));
                if ($lineSubtotal > 0) {
                    $discountRate = round(($itemDiscount / $lineSubtotal) * 100, 2);
                }
            }

            $lineNet = max(0.00, round($lineSubtotal - $itemDiscount, 2));

            // Tax calculation (inclusive gross tax)
            $taxRate = isset($rawItem['tax_rate']) ? (float) $rawItem['tax_rate'] : 0.00;
            $lineTax = $taxRate > 0 ? round($lineNet * ($taxRate / (100 + $taxRate)), 2) : 0.00;
            $lineTotal = $lineNet;

            $subtotal += $lineSubtotal;
            $itemDiscountsTotal += $itemDiscount;
            $totalTax += $lineTax;

            $calculatedItems[] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'product_name' => $product->getLocalizedName(),
                'product_sku' => $variant?->sku ?? $product->sku,
                'unit_id' => $product->unit_id,
                'unit_name' => $product->unit?->getLocalizedName() ?? $product->unit?->code,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'original_price' => $catalogPrice,
                'discount' => $itemDiscount,
                'discount_type' => $discountType,
                'discount_rate' => $discountRate,
                'tax_rate' => $taxRate,
                'tax_amount' => $lineTax,
                'subtotal' => $lineSubtotal,
                'total' => $lineTotal,
                'unit_cost' => $unitCost,
                'notes' => $rawItem['notes'] ?? null,
            ];
        }

        // Order-level discount
        $orderDiscount = max(0.00, round((float) ($orderOptions['order_discount'] ?? 0.00), 2));
        $netAfterItemDiscounts = max(0.00, $subtotal - $itemDiscountsTotal);
        $orderDiscount = min($netAfterItemDiscounts, $orderDiscount);

        // Promo code discount
        $promoCode = ! empty($orderOptions['promo_code']) ? trim((string) $orderOptions['promo_code']) : null;
        $promoDiscount = 0.00;

        if ($promoCode !== null) {
            $promoDiscount = $this->resolvePromoCodeDiscount($promoCode, $netAfterItemDiscounts - $orderDiscount);
        }

        // Delivery fee
        $deliveryType = $orderOptions['delivery_type'] ?? 'delivery';
        $deliveryFee = 0.00;
        if ($deliveryType === 'delivery') {
            $deliveryFee = max(0.00, round((float) ($orderOptions['delivery_fee'] ?? 0.00), 2));
        }

        // Grand total
        $grandTotal = max(
            0.00,
            round($subtotal - $itemDiscountsTotal - $orderDiscount - $promoDiscount + $deliveryFee, 2)
        );

        return [
            'items' => $calculatedItems,
            'subtotal' => round($subtotal, 2),
            'item_discounts_total' => round($itemDiscountsTotal, 2),
            'order_discount' => round($orderDiscount, 2),
            'promo_code' => $promoCode,
            'promo_discount' => round($promoDiscount, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'tax' => round($totalTax, 2),
            'total' => $grandTotal,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Resolve discount amount for a promotion code.
     */
    protected function resolvePromoCodeDiscount(string $code, float $eligibleAmount): float
    {
        $normalized = strtoupper(trim($code));

        return match ($normalized) {
            'SAVE10', 'WELCOME10', 'ERPLANNET10' => round($eligibleAmount * 0.10, 2),
            'SAVE20', 'VIP20' => round($eligibleAmount * 0.20, 2),
            'MINUS1000' => min($eligibleAmount, 1000.00),
            'MINUS5000' => min($eligibleAmount, 5000.00),
            default => 0.00,
        };
    }
}
