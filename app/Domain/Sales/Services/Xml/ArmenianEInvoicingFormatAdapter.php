<?php

namespace App\Domain\Sales\Services\Xml;

use SimpleXMLElement;

class ArmenianEInvoicingFormatAdapter implements XmlFormatAdapterInterface
{
    public function canHandle(SimpleXMLElement $xml): bool
    {
        $root = strtolower($xml->getName());
        $namespaces = $xml->getNamespaces(true);

        foreach ($namespaces as $ns) {
            if (str_contains($ns, 'taxservice.am')) {
                return true;
            }
        }

        if (in_array($root, [
            'exporteddata',
            'signeddata',
            'exportedaccdocdata',
            'signedaccdocdata',
            'signabledata',
            'accountingdocument',
            'electronicinvoice',
            'taxinvoice',
            'einvoice',
            'invoice',
            'document',
        ], true)) {
            return true;
        }

        return ! empty($xml->xpath('//*[local-name()="SignedAccDocData"] | //*[local-name()="SignedData"] | //*[local-name()="SignableData"] | //*[local-name()="GoodsInfo"]'));
    }

    public function getFormatName(): string
    {
        return 'armenia_e_invoicing';
    }

    public function detectDocumentType(SimpleXMLElement $xml): string
    {
        $root = strtolower($xml->getName());
        $xsiType = strtolower((string) ($xml->xpath('//@xsi:type | //@type')[0] ?? ''));

        if ($root === 'exporteddata' || str_contains($xsiType, 'invoice') || ! empty($xml->xpath('//*[local-name()="InvoiceMetadata"]'))) {
            return 'tax_invoice';
        }

        if ($root === 'exportedaccdocdata' || str_contains($xsiType, 'accountingdocument') || ! empty($xml->xpath('//*[local-name()="AccDocMetadata"]'))) {
            return 'accounting_document';
        }

        return 'tax_invoice';
    }

    public function parse(SimpleXMLElement $xml): array
    {
        // 1. Document Number (Series + Number, or InvoiceNumber)
        $seriesNodes = $xml->xpath('//*[local-name()="InvoiceNumber"]/*[local-name()="Series"]');
        $numberNodes = $xml->xpath('//*[local-name()="InvoiceNumber"]/*[local-name()="Number"]');
        $invoiceNodes = $xml->xpath('//*[local-name()="InvoiceNumber"] | //*[local-name()="SeriesNumber"] | //*[local-name()="Number"]');

        $docNum = '';
        if (! empty($seriesNodes) || ! empty($numberNodes)) {
            $docNum = trim((string) ($seriesNodes[0] ?? '').(string) ($numberNodes[0] ?? ''));
        } elseif (! empty($invoiceNodes)) {
            $docNum = trim((string) $invoiceNodes[0]);
        }

        // 2. Document Date (SupplyDate for Tax Invoices, DeliveryDate for Accounting Docs)
        $dateNodes = $xml->xpath('//*[local-name()="SupplyDate"] | //*[local-name()="DeliveryDate"] | //*[local-name()="SubmissionDate"] | //*[local-name()="InvoiceDate"] | //*[local-name()="Date"]');
        $rawDate = ! empty($dateNodes) ? (string) $dateNodes[0] : '';
        $docDate = date('Y-m-d');
        if (! empty($rawDate)) {
            $parsedTs = strtotime($rawDate);
            if ($parsedTs !== false) {
                $docDate = date('Y-m-d', $parsedTs);
            }
        }

        // 3. Customer / Buyer Info
        $buyerName = (string) ($xml->xpath('//*[local-name()="BuyerInfo"]//*[local-name()="Taxpayer"]/*[local-name()="Name"]')[0] ?? '');
        $buyerTin = (string) ($xml->xpath('//*[local-name()="BuyerInfo"]//*[local-name()="Taxpayer"]/*[local-name()="TIN"]')[0] ?? '');
        $buyerAddress = (string) ($xml->xpath('//*[local-name()="BuyerInfo"]//*[local-name()="Taxpayer"]/*[local-name()="Address"]')[0] ?? '');
        $deliveryLoc = (string) ($xml->xpath('//*[local-name()="BuyerInfo"]/*[local-name()="DeliveryLocation"]')[0] ?? '');

        if (empty($buyerName)) {
            $buyerName = (string) ($xml->xpath('//*[local-name()="BuyerName"] | //*[local-name()="Buyer"]/*[local-name()="Name"] | //*[local-name()="Buyer"]/*[local-name()="CompanyName"]')[0] ?? '');
        }
        if (empty($buyerTin)) {
            $buyerTin = (string) ($xml->xpath('//*[local-name()="BuyerTIN"] | //*[local-name()="Buyer"]/*[local-name()="TIN"] | //*[local-name()="Buyer"]/*[local-name()="TaxId"]')[0] ?? '');
        }
        if (empty($buyerAddress)) {
            $buyerAddress = (string) ($xml->xpath('//*[local-name()="BuyerAddress"] | //*[local-name()="Buyer"]/*[local-name()="Address"]')[0] ?? '');
        }

        $customer = null;
        if (! empty($buyerName) || ! empty($buyerTin)) {
            $customer = [
                'name' => $buyerName ?: null,
                'tax_id' => $buyerTin ?: null,
                'phone' => (string) ($xml->xpath('//*[local-name()="BuyerPhone"] | //*[local-name()="Buyer"]/*[local-name()="Phone"]')[0] ?? ''),
                'email' => (string) ($xml->xpath('//*[local-name()="BuyerEmail"] | //*[local-name()="Buyer"]/*[local-name()="Email"]')[0] ?? ''),
                'address' => $deliveryLoc ?: $buyerAddress,
            ];
        }

        // 4. Goods / Line Items
        $goodsNodes = $xml->xpath('//*[local-name()="GoodsInfo"]/*[local-name()="Good"] | //*[local-name()="GoodsList"]/*[local-name()="Item"] | //*[local-name()="Good"] | //*[local-name()="Item"]');
        $items = [];

        foreach ($goodsNodes as $g) {
            $desc = (string) ($g->xpath('./*[local-name()="Description"]')[0] ?? $g->xpath('./*[local-name()="Name"]')[0] ?? '');
            $cleanName = trim(ltrim($desc, "- \t\n\r\0\x0B"));
            $classifierCode = (string) ($g->xpath('./*[local-name()="ClassifierCode"]')[0] ?? '');
            $sku = (string) ($g->xpath('./*[local-name()="ProductCode"]')[0] ?? $g->xpath('./*[local-name()="Code"]')[0] ?? $g->xpath('./*[local-name()="Sku"]')[0] ?? '');
            $unit = (string) ($g->xpath('./*[local-name()="Unit"]')[0] ?? 'հատ');
            $qty = (float) ($g->xpath('./*[local-name()="Amount"]')[0] ?? $g->xpath('./*[local-name()="Quantity"]')[0] ?? $g->xpath('./*[local-name()="Count"]')[0] ?? 1);
            if ($qty <= 0) {
                $qty = 1.0;
            }

            $pricePerUnit = (float) ($g->xpath('./*[local-name()="PricePerUnit"]')[0] ?? 0);
            $price = (float) ($g->xpath('./*[local-name()="Price"]')[0] ?? 0);
            $totalPrice = (float) ($g->xpath('./*[local-name()="TotalPrice"]')[0] ?? $g->xpath('./*[local-name()="TotalAmount"]')[0] ?? 0);
            $discount = (float) ($g->xpath('./*[local-name()="Discount"]')[0] ?? 0);
            $dealType = (string) ($g->xpath('./*[local-name()="DealType"]')[0] ?? '');
            $vatRate = (float) ($g->xpath('./*[local-name()="VATRate"]')[0] ?? $g->xpath('./*[local-name()="TaxRate"]')[0] ?? 0);

            // Determine effective unit price and discount
            if ($discount > 0) {
                $effectiveUnitPrice = $pricePerUnit > 0 ? $pricePerUnit : ($qty > 0 && $totalPrice > 0 ? round($totalPrice / $qty / (1 - $discount / 100), 2) : 0.0);
                $discountAmount = round(($effectiveUnitPrice * $qty) * ($discount / 100), 2);
                $effectiveDiscountRate = $discount;
                $lineTotal = $totalPrice > 0 ? $totalPrice : round(($effectiveUnitPrice * $qty) - $discountAmount, 2);
            } else {
                // If tax invoice or standard line without discount:
                // When VAT is present and pricing is tax-inclusive: effective unit price is gross (totalPrice / qty)
                if ($totalPrice > 0 && $qty > 0) {
                    $effectiveUnitPrice = round($totalPrice / $qty, 2);
                    $lineTotal = $totalPrice;
                } elseif ($pricePerUnit > 0) {
                    $effectiveUnitPrice = $pricePerUnit;
                    $lineTotal = round($pricePerUnit * $qty, 2);
                } elseif ($price > 0) {
                    $effectiveUnitPrice = $price;
                    $lineTotal = round($price * $qty, 2);
                } else {
                    $effectiveUnitPrice = 0.0;
                    $lineTotal = 0.0;
                }
                $discountAmount = 0.00;
                $effectiveDiscountRate = 0.00;
            }

            // Deal type tax handling (SPECIAL_TAX_SYSTEM = 0% VAT in Armenia)
            $effectiveTaxRate = ($dealType === 'SPECIAL_TAX_SYSTEM') ? 0.00 : $vatRate;
            $lineTotal = $totalPrice > 0 ? $totalPrice : round(($effectiveUnitPrice * $qty) - $discountAmount, 2);

            if (! empty($sku) || ! empty($cleanName)) {
                $items[] = [
                    'sku' => $sku,
                    'name' => $cleanName ?: $desc,
                    'raw_description' => $desc,
                    'classifier_code' => $classifierCode ?: null,
                    'unit' => $unit,
                    'quantity' => $qty,
                    'unit_price' => $effectiveUnitPrice,
                    'discount' => $discountAmount,
                    'discount_type' => $effectiveDiscountRate > 0 ? 'percent' : 'fixed',
                    'discount_rate' => $effectiveDiscountRate,
                    'tax_rate' => $effectiveTaxRate,
                    'line_total' => $lineTotal,
                    'notes' => (string) ($g->xpath('./*[local-name()="Comment"]')[0] ?? '') ?: null,
                ];
            }
        }

        return [
            'document_number' => $docNum ?: null,
            'document_date' => $docDate ?: null,
            'customer' => $customer,
            'items' => $items,
            'notes' => (string) ($xml->xpath('//*[local-name()="Remarks"]')[0] ?? '') ?: null,
        ];
    }
}
