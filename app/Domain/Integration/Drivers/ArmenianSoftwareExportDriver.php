<?php

declare(strict_types=1);

namespace App\Domain\Integration\Drivers;

use App\Domain\Integration\Contracts\AccountingExportInterface;
use App\Domain\Integration\Models\TenantIntegration;
use App\Domain\Sales\Models\Order;
use App\Domain\Warehouse\Models\StockLevel;

class ArmenianSoftwareExportDriver implements AccountingExportInterface
{
    public function getProviderName(): string
    {
        return 'armenian_software';
    }

    public function testConnection(TenantIntegration $integration): array
    {
        return [
            'success' => true,
            'message' => 'Armenian Software (ՀԾ) export driver is ready.',
        ];
    }

    public function exportInvoices(TenantIntegration $integration, array $filters = []): array
    {
        $tenantId = $integration->tenant_id;
        $orders = Order::where('tenant_id', $tenantId)
            ->with(['customer', 'items.product'])
            ->latest()
            ->limit($filters['limit'] ?? 100)
            ->get();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ASDocuments type="Invoices" system="ERPlannet"/>');

        foreach ($orders as $order) {
            $inv = $xml->addChild('Document');
            $inv->addAttribute('Type', 'SalesInvoice');
            $inv->addAttribute('Number', $order->order_number);
            $inv->addAttribute('Date', $order->created_at->format('Y-m-d'));
            $inv->addAttribute('Currency', $order->currency ?? 'AMD');
            $inv->addAttribute('Total', (string) ($order->total ?? 0));

            $customerNode = $inv->addChild('Partner');
            $customerNode->addAttribute('Name', $order->customer->first_name ?? 'Guest');
            $customerNode->addAttribute('TaxCode', $order->customer->tax_id ?? '');

            $itemsNode = $inv->addChild('Items');
            foreach ($order->items as $item) {
                $itemNode = $itemsNode->addChild('Item');
                $itemNode->addAttribute('SKU', $item->product->sku ?? '');
                $itemNode->addAttribute('Name', is_array($item->product->name ?? '') ? ($item->product->name['hy'] ?? '') : (string) ($item->product->name ?? ''));
                $itemNode->addAttribute('Quantity', (string) $item->quantity);
                $itemNode->addAttribute('UnitPrice', (string) $item->unit_price);
                $itemNode->addAttribute('LineTotal', (string) ($item->total ?? 0));
            }
        }

        $content = $xml->asXML() ?: '';

        return [
            'format' => 'xml',
            'filename' => 'AS_Invoices_'.date('Ymd_His').'.xml',
            'content' => $content,
            'count' => $orders->count(),
        ];
    }

    public function exportInventory(TenantIntegration $integration, array $filters = []): array
    {
        $tenantId = $integration->tenant_id;
        $balances = StockLevel::where('tenant_id', $tenantId)
            ->with(['product', 'warehouse'])
            ->get();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ASDocuments type="StockBalances" system="ERPlannet"/>');

        foreach ($balances as $b) {
            $row = $xml->addChild('StockItem');
            $row->addAttribute('Warehouse', $b->warehouse->name ?? 'Default');
            $row->addAttribute('SKU', $b->product->sku ?? '');
            $row->addAttribute('Quantity', (string) $b->quantity_on_hand);
            $row->addAttribute('Reserved', (string) $b->quantity_reserved);
        }

        return [
            'format' => 'xml',
            'filename' => 'AS_Stock_'.date('Ymd_His').'.xml',
            'content' => $xml->asXML() ?: '',
            'count' => $balances->count(),
        ];
    }
}
