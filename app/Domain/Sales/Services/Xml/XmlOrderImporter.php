<?php

namespace App\Domain\Sales\Services\Xml;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\XmlImport;
use App\Domain\Sales\Models\XmlImportError;
use App\Domain\Sales\Services\PricingEngine;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use SimpleXMLElement;

class XmlOrderImporter
{
    /** @var array<int, XmlFormatAdapterInterface> */
    protected array $adapters;

    public function __construct(
        protected TenantContext $tenantContext,
        protected PricingEngine $pricingEngine,
        protected CreateOrderAction $createOrderAction
    ) {
        $this->adapters = [
            new ErpXmlFormatAdapter,
            new ArmenianEInvoicingFormatAdapter,
            new GenericOrderXmlFormatAdapter,
        ];
    }

    /**
     * Parse XML safely preventing XML External Entity (XXE) attacks.
     */
    public function parseXmlSafely(string $xmlContent): SimpleXMLElement
    {
        // Enforce strict XXE prevention
        if (preg_match('/<!ENTITY\s+/i', $xmlContent) || preg_match('/<!DOCTYPE\s+[^>]*SYSTEM/i', $xmlContent)) {
            throw new InvalidArgumentException('XXE security violation: XML External Entity (XXE) or custom DOCTYPE declarations are prohibited.');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NONET);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $messages = array_map(fn ($e) => trim($e->message), $errors);
            throw new InvalidArgumentException('Invalid XML format: '.implode('; ', $messages));
        }

        return $xml;
    }

    /**
     * Preview and validate an uploaded XML file without creating orders (dry run).
     */
    public function preview(
        string $xmlContent,
        string $fileName,
        string $branchId,
        ?string $warehouseId = null,
        ?string $userId = null
    ): XmlImport {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new InvalidArgumentException('Tenant context not set.');
        }

        $branch = Branch::where('id', $branchId)->where('is_active', true)->first();
        if (! $branch) {
            $branch = Branch::where('tenant_id', $tenant->id)->where('is_active', true)->first()
                ?? Branch::where('tenant_id', $tenant->id)->firstOrFail();
        }

        $warehouse = null;
        if ($warehouseId) {
            $warehouse = Warehouse::where('id', $warehouseId)->first();
        }

        $checksum = hash('sha256', $xmlContent);
        $xml = $this->parseXmlSafely($xmlContent);

        // Resolve format adapter
        $matchedAdapter = null;
        foreach ($this->adapters as $adapter) {
            if ($adapter->canHandle($xml)) {
                $matchedAdapter = $adapter;
                break;
            }
        }
        $matchedAdapter ??= new GenericOrderXmlFormatAdapter;

        $formatDetected = $matchedAdapter->getFormatName();
        $docType = $matchedAdapter->detectDocumentType($xml);
        $parsed = $matchedAdapter->parse($xml);

        $validationErrors = [];
        $mappedItems = [];
        $totalQuantity = 0.0;
        $externalDocNumber = $parsed['document_number'];

        // 1. Duplicate detection: Check file checksum
        $existingFileImport = XmlImport::where('tenant_id', $tenant->id)
            ->where('checksum', $checksum)
            ->where('status', 'imported')
            ->first();

        if ($existingFileImport) {
            $validationErrors[] = [
                'code' => 'DUPLICATE_FILE',
                'message' => "Warning: An identical file has already been imported on {$existingFileImport->created_at->toFormattedDateString()}.",
            ];
        }

        // 2. Duplicate detection: Check external document number on Orders
        if (! empty($externalDocNumber)) {
            $existingOrder = Order::where('tenant_id', $tenant->id)
                ->where('external_reference', $externalDocNumber)
                ->first();

            if ($existingOrder) {
                $validationErrors[] = [
                    'code' => 'DUPLICATE_ORDER',
                    'message' => "An order with external reference '{$externalDocNumber}' already exists (Order {$existingOrder->order_number}).",
                ];
            }
        }

        // 3. Document type check: alert if trying to import an invoice as a sales order directly
        if ($docType === 'supplier_invoice') {
            $validationErrors[] = [
                'code' => 'DOCUMENT_TYPE_NOTICE',
                'message' => 'Detected document type is Supplier Invoice. It will be converted into a Sales Order only upon explicit confirmation.',
            ];
        }

        // 4. Resolve customer
        $customer = null;
        if (! empty($parsed['customer'])) {
            $custData = $parsed['customer'];
            $custQuery = Customer::query()->where('tenant_id', $tenant->id);

            if (! empty($custData['tax_id'])) {
                $custQuery->where(function ($q) use ($custData) {
                    $q->where('tax_id', $custData['tax_id'])
                        ->orWhereHas('company', fn ($cq) => $cq->where('tax_id', $custData['tax_id']));
                });
            } elseif (! empty($custData['phone'])) {
                $custQuery->where('phone', $custData['phone']);
            } elseif (! empty($custData['name'])) {
                $custQuery->where(function ($q) use ($custData) {
                    $q->where('first_name', 'ilike', "%{$custData['name']}%")
                        ->orWhere('company_name', 'ilike', "%{$custData['name']}%")
                        ->orWhereHas('company', fn ($cq) => $cq->where('company_name', 'ilike', "%{$custData['name']}%"));
                });
            }

            $customer = $custQuery->first();

            // Auto-create customer if missing
            if (! $customer && (! empty($custData['name']) || ! empty($custData['tax_id']))) {
                $custName = ! empty($custData['name']) ? $custData['name'] : 'Customer '.($custData['tax_id'] ?? 'B2B');
                $phone = ! empty($custData['phone']) ? $custData['phone'] : ('+374'.($custData['tax_id'] ?? '00000000'));
                $custCode = 'CUST-'.strtoupper(substr(md5($custData['tax_id'] ?? $custName), 0, 8));

                $customer = Customer::create([
                    'tenant_id' => $tenant->id,
                    'customer_code' => $custCode,
                    'type' => Customer::TYPE_COMPANY,
                    'status' => Customer::STATUS_ACTIVE,
                    'primary_branch_id' => $branch->id,
                    'created_by_user_id' => $userId,
                    'company_name' => $custName,
                    'display_name' => $custName,
                    'tax_id' => $custData['tax_id'] ?? null,
                    'phone' => $phone,
                    'email' => $custData['email'] ?? null,
                    'source' => 'xml_import',
                    'notes' => 'Auto-created during XML invoice import'.(! empty($custData['address']) ? ' ('.$custData['address'].')' : ''),
                ]);
            }
        }

        // 5. Match items and validate product existence
        if (empty($parsed['items'])) {
            $validationErrors[] = [
                'code' => 'EMPTY_ITEMS',
                'message' => 'The XML file contains no valid item or line records.',
            ];
        }

        $itemsForPricing = [];
        foreach ($parsed['items'] as $index => $item) {
            $lineNum = $index + 1;
            $product = null;

            if (! empty($item['sku'])) {
                $product = Product::where('tenant_id', $tenant->id)
                    ->where(function ($q) use ($item) {
                        $q->where('sku', $item['sku'])->orWhere('barcode', $item['sku']);
                    })
                    ->first();
            }

            if (! $product && ! empty($item['name'])) {
                $cleanName = trim($item['name']);
                $product = Product::where('tenant_id', $tenant->id)
                    ->where(function ($q) use ($cleanName) {
                        $q->whereRaw('name::text ILIKE ?', ["%{$cleanName}%"])
                            ->orWhere('sku', 'ilike', "%{$cleanName}%");
                    })
                    ->first();
            }

            // Auto-create product in catalog if not found
            if (! $product && ! empty($item['name'])) {
                $cleanName = trim($item['name']);
                $unitCode = $item['unit'] ?? 'հատ';
                $unit = Unit::where('code', $unitCode)
                    ->orWhere('name->hy', $unitCode)
                    ->orWhere('name->en', $unitCode)
                    ->first();

                if (! $unit && ! empty($unitCode)) {
                    $slugCode = match ($unitCode) {
                        'տուփ', 'տուփեր' => 'box',
                        'հատ' => 'pcs',
                        'կգ' => 'kg',
                        default => substr(preg_replace('/[^a-zA-Z0-9]/', '', $unitCode), 0, 10) ?: 'unit',
                    };

                    $existingByCode = Unit::where('code', $slugCode)->first();
                    if ($existingByCode) {
                        $unit = $existingByCode;
                    } else {
                        $unit = Unit::create([
                            'tenant_id' => $tenant->id,
                            'code' => $slugCode,
                            'name' => [
                                'hy' => $unitCode,
                                'en' => $slugCode,
                            ],
                            'precision' => 0,
                        ]);
                    }
                }

                $unit ??= Unit::where('code', 'pcs')->first() ?? Unit::first();

                $generatedSku = ! empty($item['sku'])
                    ? $item['sku']
                    : ('ARM-'.(! empty($item['classifier_code']) ? $item['classifier_code'].'-' : '').strtoupper(substr(md5($cleanName), 0, 6)));

                $salePrice = (float) ($item['unit_price'] ?? 0.00);
                $costPrice = round($salePrice * 0.75, 2);

                $product = Product::create([
                    'tenant_id' => $tenant->id,
                    'unit_id' => $unit?->id,
                    'sku' => $generatedSku,
                    'name' => [
                        'hy' => $cleanName,
                        'en' => $cleanName,
                    ],
                    'sale_price' => $salePrice,
                    'cost_price' => $costPrice,
                    'hs_code' => $item['classifier_code'] ?? null,
                    'type' => Product::TYPE_FINISHED_PRODUCT,
                    'track_stock' => true,
                    'is_active' => true,
                ]);
            }

            if (! $product) {
                $validationErrors[] = [
                    'line' => $lineNum,
                    'code' => 'PRODUCT_NOT_FOUND',
                    'message' => "Line {$lineNum}: Product not found in catalog for SKU/Code '{$item['sku']}' ('{$item['name']}').",
                ];

                continue;
            }

            $qty = $item['quantity'];
            if ($qty <= 0) {
                $validationErrors[] = [
                    'line' => $lineNum,
                    'code' => 'INVALID_QUANTITY',
                    'message' => "Line {$lineNum}: Quantity must be greater than zero.",
                ];

                continue;
            }

            $totalQuantity += $qty;
            $unitPrice = $item['unit_price'] ?? (float) $product->sale_price;

            $itemsForPricing[] = [
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount' => $item['discount'] ?? 0.00,
                'discount_type' => $item['discount_type'] ?? 'fixed',
                'discount_rate' => $item['discount_rate'] ?? 0.00,
                'tax_rate' => $item['tax_rate'] ?? 0.00,
                'notes' => $item['notes'] ?? null,
            ];

            $lineTotal = $item['line_total'] ?? round(($unitPrice * $qty) - ($item['discount'] ?? 0.00), 2);

            $mappedItems[] = [
                'line' => $lineNum,
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->getLocalizedName(),
                'quantity' => $qty,
                'unit' => $item['unit'] ?? $product->unit?->code ?? 'հատ',
                'unit_price' => $unitPrice,
                'discount' => $item['discount'] ?? 0.00,
                'total' => $lineTotal,
            ];
        }

        // 6. Calculate totals with PricingEngine if items exist
        $estimatedTotals = [
            'subtotal' => 0.00,
            'tax' => 0.00,
            'total' => 0.00,
        ];

        if (! empty($itemsForPricing)) {
            try {
                $pricing = $this->pricingEngine->calculate($itemsForPricing, [
                    'allow_price_override' => true,
                    'delivery_fee' => (float) ($parsed['delivery_fee'] ?? 0.00),
                ]);
                $estimatedTotals = [
                    'subtotal' => $pricing['subtotal'],
                    'tax' => $pricing['tax'],
                    'total' => $pricing['total'],
                ];
            } catch (\Throwable $e) {
                $validationErrors[] = [
                    'code' => 'PRICING_CALCULATION_ERROR',
                    'message' => "Pricing calculation failed: {$e->getMessage()}",
                ];
            }
        }

        // 7. Store XML Import preview record
        $xmlImport = XmlImport::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse?->id,
            'user_id' => $userId,
            'file_name' => $fileName,
            'file_size' => strlen($xmlContent),
            'checksum' => $checksum,
            'document_type' => $docType,
            'format_detected' => $formatDetected,
            'status' => 'previewed',
            'dry_run' => true,
            'total_records' => count($parsed['items']),
            'successful_records' => count($mappedItems),
            'failed_records' => count($validationErrors),
            'external_document_number' => $externalDocNumber,
            'external_document_date' => $parsed['document_date'] ? date('Y-m-d', strtotime($parsed['document_date'])) : null,
            'errors' => $validationErrors,
            'preview_payload' => [
                'document_number' => $externalDocNumber,
                'document_date' => $parsed['document_date'],
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->display_name ?: ($customer->company_name ?: ($customer->first_name ? "{$customer->first_name} {$customer->last_name}" : $customer->company?->company_name)),
                    'tax_id' => $customer->tax_id,
                ] : ($parsed['customer'] ?? null),
                'items' => $mappedItems,
                'items_for_pricing' => $itemsForPricing,
                'totals' => $estimatedTotals,
                'delivery_fee' => (float) ($parsed['delivery_fee'] ?? 0.00),
                'notes' => $parsed['notes'],
            ],
        ]);

        // Record granular errors
        foreach ($validationErrors as $err) {
            XmlImportError::create([
                'tenant_id' => $tenant->id,
                'xml_import_id' => $xmlImport->id,
                'line_number' => $err['line'] ?? null,
                'record_identifier' => $err['code'] ?? 'VALIDATION',
                'error_code' => $err['code'] ?? 'VALIDATION_ERROR',
                'error_message' => $err['message'] ?? 'Validation failure',
                'raw_snippet' => null,
            ]);
        }

        return $xmlImport->fresh(['importErrors', 'branch', 'warehouse']);
    }

    /**
     * Confirm and execute the import of a previously previewed XML file.
     * Enforces complete transactional integrity.
     */
    public function confirm(string $importId, ?string $userId = null): Order
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new InvalidArgumentException('Tenant context not set.');
        }

        $xmlImport = XmlImport::where('tenant_id', $tenant->id)->findOrFail($importId);

        if ($xmlImport->status === 'imported') {
            throw new InvalidArgumentException('This XML file has already been imported.');
        }

        $payload = $xmlImport->preview_payload;
        if (empty($payload) || empty($payload['items_for_pricing'])) {
            throw new InvalidArgumentException('XML import preview contains no valid items to import.');
        }

        // Execute atomically within a DB Transaction
        return DB::transaction(function () use ($xmlImport, $payload, $userId) {
            $customerId = null;
            if (! empty($payload['customer']['id'])) {
                $customerId = $payload['customer']['id'];
            }

            // Create Order using CreateOrderAction
            $order = $this->createOrderAction->execute([
                'branch_id' => $xmlImport->branch_id,
                'warehouse_id' => $xmlImport->warehouse_id,
                'customer_id' => $customerId,
                'source' => 'xml_import',
                'order_type' => 'standard',
                'external_reference' => $xmlImport->external_document_number,
                'delivery_type' => 'pickup',
                'delivery_fee' => (float) ($payload['delivery_fee'] ?? 0.00),
                'allow_price_override' => true,
                'customer_notes' => $payload['notes'] ?? ('Imported from '.($xmlImport->document_type === 'tax_invoice' ? 'Tax Invoice (Հարկային Հաշիվ)' : ($xmlImport->document_type === 'accounting_document' ? 'Accounting Document (Հաշվարկային Հաշիվ)' : 'XML File'))." {$xmlImport->external_document_number}"),
                'items' => $payload['items_for_pricing'],
            ], $userId);

            // Update XmlImport record
            $xmlImport->update([
                'status' => 'completed',
                'dry_run' => false,
                'successful_records' => count($payload['items']),
                'failed_records' => 0,
                'created_orders_ids' => [$order->id],
            ]);

            return $order;
        });
    }
}
