<?php

namespace App\Domain\Printing\Services;

use App\Domain\Printing\Models\DocumentTemplate;
use App\Domain\Sales\Models\B2bDeliveryNote;
use App\Domain\Sales\Models\Order;

class DocumentTemplateRenderer
{
    /**
     * Allowed placeholder dictionary with resolvers.
     *
     * @var array<string, string>
     */
    protected const SAFE_PLACEHOLDERS = [
        'organization.name' => 'Organization legal name',
        'organization.tax_id' => 'Tax Identification Number (ՀՎՀՀ / TIN)',
        'branch.name' => 'Branch location name',
        'branch.address' => 'Branch physical address',
        'branch.phone' => 'Branch contact phone',
        'customer.name' => 'Customer name or company',
        'customer.tax_id' => 'Customer Tax ID / TIN',
        'customer.phone' => 'Customer contact phone',
        'customer.address' => 'Customer delivery or legal address',
        'order.number' => 'Order human-readable number',
        'order.created_at' => 'Order creation date and time',
        'order.scheduled_at' => 'Requested fulfillment date',
        'order.subtotal' => 'Subtotal before discounts and taxes',
        'order.discount_total' => 'Total discount amount applied',
        'order.delivery_fee' => 'Delivery fee amount',
        'order.tax_total' => 'Total tax / VAT amount',
        'order.grand_total' => 'Payable grand total',
        'order.amount_paid' => 'Total amount received / paid',
        'order.balance_due' => 'Remaining outstanding balance',
        'order.payment_method' => 'Payment method(s) used',
        'order.responsible_employee' => 'Responsible cashier or manager',
        'order.notes' => 'Customer or fulfillment notes',
        'delivery_note.number' => 'B2B Delivery note number',
        'delivery_note.date' => 'Delivery note date',
        'delivery_note.delivered_by' => 'Delivered by representative',
        'delivery_note.received_by' => 'Received by representative',
    ];

    /**
     * Render a document template for an order or B2B delivery note.
     *
     * @param  array<string, mixed>  $customContext
     * @return string HTML rendered content
     */
    public function render(
        DocumentTemplate|array $template,
        ?Order $order = null,
        mixed $deliveryNote = null,
        array $customContext = []
    ): string {
        if (is_array($deliveryNote)) {
            $customContext = array_merge($deliveryNote, $customContext);
            $deliveryNote = null;
        }

        $context = $this->buildContext($order, $deliveryNote, $customContext);

        if ($template instanceof DocumentTemplate && ! empty($template->content)) {
            return $this->substitute($template->content, $context);
        }
        if (is_array($template) && ! empty($template['content'])) {
            return $this->substitute($template['content'], $context);
        }

        $layoutConfig = $template instanceof DocumentTemplate ? $template->layout_config : ($template['layout_config'] ?? $template);
        $paperSize = $template instanceof DocumentTemplate ? $template->paper_size : ($template['paper_size'] ?? '80mm');
        $docType = $template instanceof DocumentTemplate ? $template->document_type : ($template['document_type'] ?? 'pos_receipt');

        return $this->generateHtml($layoutConfig, $paperSize, $docType, $context);
    }

    /**
     * Get placeholder registry with sample values for designer UI.
     *
     * @return array<string, string>
     */
    public function getPlaceholderRegistry(): array
    {
        return self::SAFE_PLACEHOLDERS;
    }

    /**
     * Build placeholder replacements context safely without arbitrary evaluation.
     */
    public function buildContext(?Order $order, ?B2bDeliveryNote $deliveryNote, array $custom = []): array
    {
        $tenant = $order?->tenant ?? $deliveryNote?->tenant ?? auth()->user()?->tenant;
        $branch = $order?->branch ?? $deliveryNote?->branch;
        $customer = $order?->customer;

        $items = [];
        if ($order) {
            foreach ($order->items as $item) {
                $items[] = [
                    'sku' => $item->product_sku,
                    'name' => $item->product_name,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit_name ?? 'pcs',
                    'unit_price' => (float) $item->unit_price,
                    'discount' => (float) $item->discount,
                    'tax' => (float) $item->tax_amount,
                    'total' => (float) $item->total,
                ];
            }
        } elseif ($deliveryNote && ! empty($deliveryNote->items_snapshot)) {
            $items = $deliveryNote->items_snapshot;
        } elseif (! empty($custom['items'])) {
            $items = $custom['items'];
        } else {
            // Default sample items for visual designer preview
            $items = [
                ['sku' => 'PEL-001', 'name' => 'Տավարի Պելմենի 1կգ', 'quantity' => 2, 'unit' => 'կգ', 'unit_price' => 2800.00, 'discount' => 0.00, 'tax' => 560.00, 'total' => 5600.00],
                ['sku' => 'KUF-002', 'name' => 'Իշխան Քյուֆթա 500գ', 'quantity' => 1, 'unit' => 'հատ', 'unit_price' => 3200.00, 'discount' => 200.00, 'tax' => 300.00, 'total' => 3000.00],
            ];
        }

        $paymentsList = [];
        if ($order && $order->relationLoaded('paymentTransactions')) {
            foreach ($order->paymentTransactions as $pt) {
                if ($pt->status === 'successful') {
                    $paymentsList[] = ucfirst($pt->gateway).': '.number_format((float) $pt->amount, 2).' AMD';
                }
            }
        }

        $paymentMethod = ! empty($paymentsList) ? implode(', ', $paymentsList) : ($custom['payment_method'] ?? 'Cash (Կանխիկ)');

        return array_merge([
            'organization.name' => $tenant?->name ?? 'ERPlannet Enterprise LLC',
            'organization.tax_id' => $tenant?->tax_id ?? '02891234',
            'branch.name' => $branch?->name ?? 'Գլխավոր Մասնաճյուղ (Central)',
            'branch.address' => $branch?->address ?? 'ք. Երևան, Թումանյան 24',
            'branch.phone' => $branch?->phone ?? '+374 10 12-34-56',
            'customer.name' => $order?->customer_snapshot['name'] ?? ($customer ? ($customer->first_name.' '.$customer->last_name) : 'Հաճախորդ (Retail)'),
            'customer.tax_id' => $order?->customer_snapshot['tax_id'] ?? ($customer?->tax_id ?? 'N/A'),
            'customer.phone' => $order?->customer_snapshot['phone'] ?? ($customer?->phone ?? '+374 99 00-00-00'),
            'customer.address' => $order?->customer_snapshot['address']['formatted'] ?? ($order?->address?->formatted_address ?? 'ք. Երևան'),
            'order.number' => $order?->order_number ?? ($custom['order_number'] ?? 'ORD-2026-000123'),
            'order.created_at' => $order?->placed_at ? $order->placed_at->format('d.m.Y H:i') : date('d.m.Y H:i'),
            'order.scheduled_at' => $order?->scheduled_for ? $order->scheduled_for->format('d.m.Y H:i') : 'Անմիջապես',
            'order.subtotal' => number_format((float) ($order?->subtotal ?? 8800.00), 2).' AMD',
            'order.discount_total' => number_format((float) ($order?->discount ?? 200.00), 2).' AMD',
            'order.delivery_fee' => number_format((float) ($order?->delivery_fee ?? 0.00), 2).' AMD',
            'order.tax_total' => number_format((float) ($order?->tax ?? 860.00), 2).' AMD',
            'order.grand_total' => number_format((float) ($order?->total ?? 8600.00), 2).' ֏',
            'order.amount_paid' => number_format((float) ($order?->paid_amount ?? 8600.00), 2).' AMD',
            'order.balance_due' => number_format((float) ($order?->balance_due ?? 0.00), 2).' AMD',
            'order.payment_method' => $paymentMethod,
            'order.responsible_employee' => $order?->responsibleEmployee?->name ?? 'Գանձապահ / Աննա Պ.',
            'order.notes' => $order?->customer_notes ?? 'Շնորհակալություն գնումների համար',
            'delivery_note.number' => $deliveryNote?->document_number ?? 'DN-2026-000045',
            'delivery_note.date' => $deliveryNote?->document_date ? $deliveryNote->document_date->format('d.m.Y') : date('d.m.Y'),
            'delivery_note.delivered_by' => $deliveryNote?->delivered_by_name ?? 'Առաքիչ / Կարեն Հ.',
            'delivery_note.received_by' => $deliveryNote?->received_by_name ?? 'Ստացող / Ստորագրություն',
            'items' => $items,
        ], $custom);
    }

    /**
     * Safely substitute placeholders in a text string.
     */
    public function substitute(string $text, array $context): string
    {
        return preg_replace_callback('/\{\{([a-zA-Z0-9_\.]+)\}\}/', function ($matches) use ($context) {
            $key = $matches[1];
            if (isset($context[$key])) {
                return htmlspecialchars((string) $context[$key], ENT_QUOTES, 'UTF-8');
            }

            return $matches[0];
        }, $text);
    }

    /**
     * Generate HTML document tailored to paper width and layout settings.
     */
    protected function generateHtml(array $layout, string $paperSize, string $docType, array $context): string
    {
        $isThermal = in_array($paperSize, ['58mm', '80mm'], true);
        $widthCss = match ($paperSize) {
            '58mm' => '58mm',
            '80mm' => '80mm',
            'a5' => '148mm',
            'a4' => '210mm',
            default => '80mm',
        };

        $fontSize = $layout['font_size'] ?? ($isThermal ? '12px' : '14px');
        $fontFamily = $layout['font_family'] ?? "'Plus Jakarta Sans', 'Noto Sans Armenian', -apple-system, sans-serif";

        $headerTitle = $this->substitute($layout['header']['title'] ?? 'ՎԱՃԱՌՔԻ ԿՏՐՈՆ / RECEIPT', $context);
        $headerSubtitle = $this->substitute($layout['header']['subtitle'] ?? '{{organization.name}}', $context);
        $footerText = $this->substitute($layout['footer']['text'] ?? 'Շնորհակալություն գնումների համար: Ապրանքները ենթակա են վերադարձի 14 օրում:', $context);

        $showLogo = ! empty($layout['header']['show_logo']);
        $showBranch = $layout['sections']['branch_info'] ?? true;
        $showCustomer = $layout['sections']['customer_info'] ?? true;
        $showItems = $layout['sections']['items_table'] ?? true;
        $showTotals = $layout['sections']['totals'] ?? true;
        $showPayments = $layout['sections']['payments'] ?? true;
        $showSignatures = $layout['sections']['signatures'] ?? (! $isThermal);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="hy">
        <head>
            <meta charset="utf-8">
            <title><?= htmlspecialchars($headerTitle) ?></title>
            <style>
                @page {
                    size: <?= $paperSize ?> auto;
                    margin: <?= $isThermal ? '2mm 3mm' : '12mm 15mm' ?>;
                }
                * {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                body {
                    font-family: <?= $fontFamily ?>;
                    font-size: <?= $fontSize ?>;
                    color: #0f172a;
                    background: #ffffff;
                    line-height: 1.35;
                    -webkit-print-color-adjust: exact;
                }
                .doc-container {
                    width: 100%;
                    max-width: <?= $widthCss ?>;
                    margin: 0 auto;
                    padding: <?= $isThermal ? '4px' : '16px' ?>;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-left { text-align: left; }
                .font-bold { font-weight: 700; }
                .font-semibold { font-weight: 600; }
                .divider {
                    border-top: <?= $isThermal ? '1px dashed #000' : '1px solid #cbd5e1' ?>;
                    margin: 6px 0;
                }
                .double-divider {
                    border-top: <?= $isThermal ? '2px double #000' : '2px solid #0f172a' ?>;
                    margin: 8px 0;
                }
                .header-section {
                    text-align: center;
                    margin-bottom: 8px;
                }
                .doc-title {
                    font-size: <?= $isThermal ? '14px' : '18px' ?>;
                    font-weight: 800;
                    letter-spacing: 0.02em;
                    text-transform: uppercase;
                }
                .doc-subtitle {
                    font-size: <?= $isThermal ? '11px' : '13px' ?>;
                    color: #334155;
                    margin-top: 2px;
                }
                .meta-table {
                    width: 100%;
                    margin-bottom: 6px;
                    font-size: <?= $isThermal ? '11px' : '12px' ?>;
                }
                .meta-table td {
                    padding: 2px 0;
                    vertical-align: top;
                }
                .meta-table td.label {
                    color: #475569;
                    width: 40%;
                }
                .meta-table td.value {
                    font-weight: 600;
                    text-align: right;
                }
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 6px 0;
                    font-size: <?= $isThermal ? '11px' : '12px' ?>;
                }
                .items-table th {
                    border-bottom: 1px solid #0f172a;
                    padding: 4px 2px;
                    font-weight: 700;
                }
                .items-table td {
                    padding: 4px 2px;
                    border-bottom: <?= $isThermal ? '1px dotted #ccc' : '1px solid #e2e8f0' ?>;
                }
                .totals-section {
                    width: 100%;
                    margin: 6px 0;
                    font-size: <?= $isThermal ? '11px' : '13px' ?>;
                }
                .totals-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 2px 0;
                }
                .grand-total {
                    font-size: <?= $isThermal ? '15px' : '16px' ?>;
                    font-weight: 800;
                    border-top: 1px solid #0f172a;
                    border-bottom: 1px solid #0f172a;
                    padding: 4px 0;
                    margin-top: 4px;
                }
                .signatures-wrap {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 24px;
                    padding-top: 12px;
                }
                .sign-box {
                    width: 45%;
                    border-top: 1px solid #0f172a;
                    padding-top: 4px;
                    font-size: 11px;
                    text-align: center;
                }
                .footer-text {
                    text-align: center;
                    font-size: <?= $isThermal ? '10px' : '11px' ?>;
                    color: #475569;
                    margin-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="doc-container">
                <!-- Header -->
                <div class="header-section">
                    <?php if ($showLogo && ! empty($layout['header']['logo_url'])) { ?>
                        <div style="margin-bottom: 4px;"><img src="<?= htmlspecialchars($layout['header']['logo_url']) ?>" style="max-height: 40px;" alt="Logo"></div>
                    <?php } ?>
                    <div class="doc-title"><?= $headerTitle ?></div>
                    <div class="doc-subtitle"><?= $headerSubtitle ?></div>
                    <?php if ($showBranch) { ?>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                            <?= htmlspecialchars($context['branch.name'].' | '.$context['branch.address']) ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="divider"></div>

                <!-- Document Meta -->
                <table class="meta-table">
                    <tr>
                        <td class="label">Համար / No:</td>
                        <td class="value"><?= htmlspecialchars($context['order.number']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Ամսաթիվ / Date:</td>
                        <td class="value"><?= htmlspecialchars($context['order.created_at']) ?></td>
                    </tr>
                    <?php if ($showCustomer && ! empty($context['customer.name'])) { ?>
                    <tr>
                        <td class="label">Հաճախորդ / Client:</td>
                        <td class="value"><?= htmlspecialchars($context['customer.name']) ?></td>
                    </tr>
                    <?php if (! empty($context['customer.tax_id']) && $context['customer.tax_id'] !== 'N/A') { ?>
                    <tr>
                        <td class="label">ՀՎՀՀ / TIN:</td>
                        <td class="value"><?= htmlspecialchars($context['customer.tax_id']) ?></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                    <?php if (! empty($context['order.responsible_employee'])) { ?>
                    <tr>
                        <td class="label">Աշխատակից / Staff:</td>
                        <td class="value"><?= htmlspecialchars($context['order.responsible_employee']) ?></td>
                    </tr>
                    <?php } ?>
                </table>

                <div class="divider"></div>

                <!-- Line Items Table -->
                <?php if ($showItems) { ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="text-left" style="<?= $isThermal ? 'width: 50%;' : 'width: 45%;' ?>">Անվանում</th>
                            <th class="text-right" style="width: 15%;">Քնկ</th>
                            <th class="text-right" style="width: 18%;">Գին</th>
                            <th class="text-right" style="width: 22%;">Ընդամենը</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($context['items'] as $it) { ?>
                        <tr>
                            <td class="text-left">
                                <div class="font-semibold"><?= htmlspecialchars($it['name']) ?></div>
                                <?php if (! empty($it['sku']) && ! $isThermal) { ?>
                                    <div style="font-size: 9px; color: #64748b;"><?= htmlspecialchars($it['sku']) ?></div>
                                <?php } ?>
                            </td>
                            <td class="text-right font-medium"><?= $it['quantity'] ?> <?= htmlspecialchars($it['unit'] ?? '') ?></td>
                            <td class="text-right"><?= number_format((float) $it['unit_price'], 0) ?></td>
                            <td class="text-right font-semibold"><?= number_format((float) $it['total'], 0) ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } ?>

                <!-- Totals Section -->
                <?php if ($showTotals) { ?>
                <div class="totals-section">
                    <div class="totals-row">
                        <span>Ենթահանրագումար / Subtotal:</span>
                        <span class="font-semibold"><?= $context['order.subtotal'] ?></span>
                    </div>
                    <?php if ((float) str_replace(['AMD', ',', ' '], '', $context['order.discount_total']) > 0) { ?>
                    <div class="totals-row" style="color: #dc2626;">
                        <span>Զեղչ / Discount:</span>
                        <span class="font-semibold">-<?= $context['order.discount_total'] ?></span>
                    </div>
                    <?php } ?>
                    <?php if ((float) str_replace(['AMD', ',', ' '], '', $context['order.delivery_fee']) > 0) { ?>
                    <div class="totals-row">
                        <span>Առաքում / Delivery:</span>
                        <span class="font-semibold"><?= $context['order.delivery_fee'] ?></span>
                    </div>
                    <?php } ?>
                    <?php if ((float) str_replace(['AMD', ',', ' '], '', $context['order.tax_total']) > 0) { ?>
                    <div class="totals-row">
                        <span>ԱԱՀ (20%) / VAT:</span>
                        <span class="font-semibold"><?= $context['order.tax_total'] ?></span>
                    </div>
                    <?php } ?>
                    <div class="totals-row grand-total">
                        <span>ԸՆԴԱՄԵՆԸ / TOTAL:</span>
                        <span><?= $context['order.grand_total'] ?></span>
                    </div>
                </div>
                <?php } ?>

                <!-- Payments Section -->
                <?php if ($showPayments) { ?>
                <div style="font-size: <?= $isThermal ? '10px' : '11px' ?>; margin: 4px 0; color: #334155;">
                    <div><strong>Վճարում / Payment:</strong> <?= htmlspecialchars($context['order.payment_method']) ?></div>
                    <div><strong>Վճարված / Paid:</strong> <?= $context['order.amount_paid'] ?></div>
                    <?php if ((float) str_replace(['AMD', ',', ' '], '', $context['order.balance_due']) > 0) { ?>
                    <div style="color: #ea580c;"><strong>Մնացորդ / Balance Due:</strong> <?= $context['order.balance_due'] ?></div>
                    <?php } ?>
                </div>
                <?php } ?>

                <!-- Signatures Section for B2B Delivery Notes / Invoices -->
                <?php if ($showSignatures) { ?>
                <div class="signatures-wrap">
                    <div class="sign-box">
                        <div class="font-semibold">Հանձնեց / Delivered by:</div>
                        <div style="margin-top: 14px;"><?= htmlspecialchars($context['delivery_note.delivered_by']) ?></div>
                    </div>
                    <div class="sign-box">
                        <div class="font-semibold">Ստացավ / Received by:</div>
                        <div style="margin-top: 14px;"><?= htmlspecialchars($context['delivery_note.received_by']) ?></div>
                    </div>
                </div>
                <?php } ?>

                <!-- Footer Text -->
                <?php if (! empty($footerText)) { ?>
                <div class="footer-text"><?= htmlspecialchars($footerText) ?></div>
                <?php } ?>
            </div>
        </body>
        </html>
        <?php
        return (string) ob_get_clean();
    }
}
