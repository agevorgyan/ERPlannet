<?php

namespace App\Domain\Sales\Services\Xml;

use SimpleXMLElement;

class GenericOrderXmlFormatAdapter implements XmlFormatAdapterInterface
{
    public function canHandle(SimpleXMLElement $xml): bool
    {
        return true; // Fallback adapter
    }

    public function getFormatName(): string
    {
        return 'generic_order_xml';
    }

    public function detectDocumentType(SimpleXMLElement $xml): string
    {
        $root = strtolower($xml->getName());
        if (str_contains($root, 'invoice')) {
            return 'supplier_invoice';
        }

        return 'customer_order';
    }

    public function parse(SimpleXMLElement $xml): array
    {
        $docNum = (string) ($xml->id ?? $xml->number ?? $xml->order_id ?? '');
        $docDate = (string) ($xml->date ?? $xml->created_at ?? date('Y-m-d'));

        $customer = null;
        if (isset($xml->customer)) {
            $customer = [
                'name' => (string) ($xml->customer->name ?? ''),
                'tax_id' => (string) ($xml->customer->tax_id ?? $xml->customer->tin ?? ''),
                'phone' => (string) ($xml->customer->phone ?? ''),
                'email' => (string) ($xml->customer->email ?? ''),
                'address' => (string) ($xml->customer->address ?? ''),
            ];
        }

        $items = [];
        $itemsNode = $xml->items ?? $xml->products ?? $xml;
        foreach ($itemsNode->children() as $child) {
            $sku = (string) ($child->sku ?? $child->code ?? '');
            $name = (string) ($child->name ?? $child->title ?? '');
            $qty = (float) ($child->quantity ?? $child->qty ?? 1);
            $price = isset($child->price) ? (float) $child->price : null;

            if ($sku !== '' || $name !== '') {
                $items[] = [
                    'sku' => $sku,
                    'name' => $name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount' => (float) ($child->discount ?? 0.00),
                    'tax_rate' => (float) ($child->tax ?? 0.00),
                    'notes' => (string) ($child->notes ?? '') ?: null,
                ];
            }
        }

        return [
            'document_number' => $docNum ?: null,
            'document_date' => $docDate ?: null,
            'customer' => $customer,
            'items' => $items,
            'notes' => (string) ($xml->notes ?? '') ?: null,
        ];
    }
}
