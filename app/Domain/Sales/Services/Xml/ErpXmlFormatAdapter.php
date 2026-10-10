<?php

namespace App\Domain\Sales\Services\Xml;

use SimpleXMLElement;

class ErpXmlFormatAdapter implements XmlFormatAdapterInterface
{
    public function canHandle(SimpleXMLElement $xml): bool
    {
        $root = strtolower($xml->getName());

        return in_array($root, ['erporder', 'erporders', 'erp_orders', 'order', 'orders', 'erpdocument'], true);
    }

    public function getFormatName(): string
    {
        return 'erplannet_xml';
    }

    public function detectDocumentType(SimpleXMLElement $xml): string
    {
        if (isset($xml->DocumentType)) {
            $type = strtolower((string) $xml->DocumentType);
            if (str_contains($type, 'invoice')) {
                return 'supplier_invoice';
            }
        }

        return 'customer_order';
    }

    public function parse(SimpleXMLElement $xml): array
    {
        $orderNode = isset($xml->Order) ? $xml->Order : $xml;

        $docNum = (string) ($orderNode->DocumentNumber ?? $orderNode->OrderNumber ?? $orderNode->Number ?? '');
        $docDate = (string) ($orderNode->DocumentDate ?? $orderNode->OrderDate ?? $orderNode->Date ?? date('Y-m-d'));

        $customer = null;
        if (isset($orderNode->CustomerTaxId) || isset($orderNode->CustomerName)) {
            $customer = [
                'name' => (string) ($orderNode->CustomerName ?? ''),
                'tax_id' => (string) ($orderNode->CustomerTaxId ?? ''),
                'phone' => (string) ($orderNode->CustomerPhone ?? ''),
                'email' => (string) ($orderNode->CustomerEmail ?? ''),
                'address' => (string) ($orderNode->CustomerAddress ?? ''),
            ];
        } elseif (isset($orderNode->Customer)) {
            $customer = [
                'name' => (string) ($orderNode->Customer->Name ?? $orderNode->Customer->CompanyName ?? ''),
                'tax_id' => (string) ($orderNode->Customer->TaxId ?? $orderNode->Customer->TIN ?? ''),
                'phone' => (string) ($orderNode->Customer->Phone ?? ''),
                'email' => (string) ($orderNode->Customer->Email ?? ''),
                'address' => (string) ($orderNode->Customer->Address ?? ''),
            ];
        }

        $items = [];
        $itemsNode = $orderNode->Lines ?? $orderNode->Items ?? $orderNode->OrderItems ?? $orderNode;
        foreach ($itemsNode->children() as $child) {
            $name = strtolower($child->getName());
            if (! in_array($name, ['item', 'orderitem', 'line'], true)) {
                continue;
            }

            $sku = (string) ($child->ProductSku ?? $child->Sku ?? $child->Code ?? '');
            $itemName = (string) ($child->ProductName ?? $child->Name ?? '');
            $qty = (float) ($child->Quantity ?? $child->Qty ?? 1);
            $unitPrice = isset($child->UnitPrice) ? (float) $child->UnitPrice : null;
            $discount = (float) ($child->Discount ?? 0.00);
            $taxRate = (float) ($child->TaxRate ?? 0.00);
            $notes = (string) ($child->Notes ?? '');

            if (! empty($sku) || ! empty($itemName)) {
                $items[] = [
                    'sku' => $sku,
                    'name' => $itemName,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax_rate' => $taxRate,
                    'notes' => $notes ?: null,
                ];
            }
        }

        $deliveryFee = (float) ($orderNode->DeliveryFee ?? 0.00);

        return [
            'document_number' => $docNum ?: null,
            'document_date' => $docDate ?: null,
            'customer' => $customer,
            'items' => $items,
            'delivery_fee' => $deliveryFee,
            'notes' => (string) ($orderNode->Notes ?? $orderNode->Comment ?? '') ?: null,
        ];
    }
}
