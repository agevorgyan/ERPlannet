<?php

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceImportService
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    /**
     * Parse and import ingredients from an invoice file (.xml, .csv, .xls, .xlsx).
     *
     * @return array{imported_count: int, items: array, errors: array}
     */
    public function import(UploadedFile $file, ?string $supplierId = null, ?string $warehouseId = null): array
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if ($extension === 'xml') {
            $rows = $this->parseXml($file->getRealPath());
        } elseif (in_array($extension, ['csv', 'txt'])) {
            $rows = $this->parseCsv($file->getRealPath());
        } elseif (in_array($extension, ['xls', 'xlsx'])) {
            $rows = $this->parseExcel($file->getRealPath(), $extension);
        } else {
            throw new \InvalidArgumentException("Չաջակցվող ֆայլի ֆորմատ: .{$extension}։ Թույլատրվում են միայն .xml, .xls, .xlsx, .csv ֆայլեր։");
        }

        if (empty($rows)) {
            throw new \RuntimeException('Ֆայլում տվյալներ չեն հայտնաբերվել կամ ֆորմատն անընթեռնելի է։');
        }

        $defaultWarehouse = $warehouseId
            ? Warehouse::find($warehouseId)
            : Warehouse::where('tenant_id', $tenant->id)->first();

        $defaultSupplier = $supplierId
            ? Supplier::find($supplierId)
            : null;

        $imported = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                try {
                    $item = $this->processRow($row, $tenant->id, $defaultSupplier, $defaultWarehouse);
                    if ($item) {
                        $imported[] = $item;
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Տող #'.($index + 1).': '.$e->getMessage();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'imported_count' => count($imported),
            'items' => $imported,
            'errors' => $errors,
        ];
    }

    /**
     * Process a normalized item row and create/update product & stock.
     */
    protected function processRow(array $row, string $tenantId, ?Supplier $defaultSupplier, ?Warehouse $defaultWarehouse): ?Product
    {
        $name = trim($row['name'] ?? '');
        if ($name === '') {
            return null;
        }

        // 1. Resolve Unit
        $unitName = trim($row['unit'] ?? 'կգ');
        $unit = $this->resolveUnit($tenantId, $unitName);

        // 2. Resolve Category & Subcategory
        $categoryName = trim($row['category'] ?? 'Հումք և նյութեր');
        $subcategoryName = trim($row['subcategory'] ?? '');
        $category = $this->resolveCategory($tenantId, $categoryName);
        $subcategory = null;
        if ($subcategoryName !== '') {
            $subcategory = $this->resolveCategory($tenantId, $subcategoryName, $category->id);
        }

        // 3. Resolve SKU & Barcode
        $sku = trim($row['sku'] ?? '');
        if ($sku === '') {
            $sku = 'ING-'.strtoupper(Str::random(6));
        }

        $barcode = trim($row['barcode'] ?? '');
        $hsCode = trim($row['hs_code'] ?? '');
        $costPrice = max(0, (float) ($row['cost_price'] ?? $row['price'] ?? 0));
        $quantity = max(0, (float) ($row['quantity'] ?? 0));
        $vatRate = isset($row['vat_rate']) ? (float) $row['vat_rate'] : 20.00;
        $discountPercent = isset($row['discount_percent']) ? (float) $row['discount_percent'] : 0.00;
        $packaging = trim($row['packaging'] ?? 'Առանց տարայի');
        $transactionType = trim($row['transaction_type'] ?? 'local_purchase');
        $minStockLevel = (float) ($row['min_stock_level'] ?? 5.00);

        // 4. Create or Update Product
        $product = null;
        if ($sku !== '') {
            $product = Product::where('tenant_id', $tenantId)->where('sku', $sku)->first();
        }
        if (! $product && $barcode !== '') {
            $product = Product::where('tenant_id', $tenantId)->where('barcode', $barcode)->first();
        }
        if (! $product) {
            $product = Product::where('tenant_id', $tenantId)
                ->where(function ($q) use ($name) {
                    $q->where('name->hy', $name)
                        ->orWhere('name->en', $name)
                        ->orWhere('name->ru', $name);
                })
                ->first();
        }

        if (! $product) {
            $product = Product::create([
                'tenant_id' => $tenantId,
                'category_id' => $category?->id,
                'subcategory_id' => $subcategory?->id,
                'unit_id' => $unit->id,
                'type' => Product::TYPE_INGREDIENT,
                'sku' => $sku,
                'barcode' => $barcode ?: null,
                'hs_code' => $hsCode ?: null,
                'name' => [
                    'hy' => $name,
                    'en' => $row['name_en'] ?? $name,
                    'ru' => $row['name_ru'] ?? $name,
                ],
                'description' => ! empty($row['description']) ? ['hy' => $row['description']] : null,
                'cost_price' => $costPrice,
                'sale_price' => $costPrice * 1.25, // default markup if sold
                'currency' => 'AMD',
                'vat_rate' => $vatRate,
                'discount_percent' => $discountPercent,
                'packaging' => $packaging,
                'transaction_type' => $transactionType,
                'min_stock_level' => $minStockLevel,
                'track_stock' => true,
                'is_produced' => false,
                'is_active' => true,
            ]);
        } else {
            $updateData = [
                'cost_price' => $costPrice > 0 ? $costPrice : $product->cost_price,
                'vat_rate' => $vatRate,
                'discount_percent' => $discountPercent,
            ];
            if ($hsCode !== '') {
                $updateData['hs_code'] = $hsCode;
            }
            if ($barcode !== '' && empty($product->barcode)) {
                $updateData['barcode'] = $barcode;
            }
            if ($packaging !== '') {
                $updateData['packaging'] = $packaging;
            }
            if ($subcategory) {
                $updateData['subcategory_id'] = $subcategory->id;
            }
            $product->update($updateData);
        }

        // 5. Attach Supplier
        $supplier = null;
        if (! empty($row['supplier_tax_id']) || ! empty($row['supplier_name'])) {
            $supplierQuery = Supplier::where('tenant_id', $tenantId);
            if (! empty($row['supplier_tax_id'])) {
                $supplierQuery->where('tax_id', trim($row['supplier_tax_id']));
            } elseif (! empty($row['supplier_name'])) {
                $supplierQuery->where('company_name', 'ilike', '%'.trim($row['supplier_name']).'%');
            }
            $supplier = $supplierQuery->first();
            if (! $supplier && ! empty($row['supplier_name'])) {
                $supplier = Supplier::create([
                    'tenant_id' => $tenantId,
                    'company_name' => trim($row['supplier_name']),
                    'tax_id' => ! empty($row['supplier_tax_id']) ? trim($row['supplier_tax_id']) : null,
                    'phone' => '+37400000000',
                    'is_active' => true,
                ]);
            }
        } elseif ($defaultSupplier) {
            $supplier = $defaultSupplier;
        }

        if ($supplier) {
            $product->suppliers()->syncWithoutDetaching([
                $supplier->id => [
                    'tenant_id' => $tenantId,
                    'supply_price' => $costPrice,
                ],
            ]);
        }

        // 6. Update Stock Level if warehouse & quantity > 0
        if ($defaultWarehouse && $quantity > 0) {
            $stockLevel = StockLevel::where('tenant_id', $tenantId)
                ->where('warehouse_id', $defaultWarehouse->id)
                ->where('product_id', $product->id)
                ->first();

            if ($stockLevel) {
                $stockLevel->quantity_on_hand += $quantity;
                $stockLevel->updated_at = now();
                $stockLevel->save();
            } else {
                StockLevel::create([
                    'tenant_id' => $tenantId,
                    'warehouse_id' => $defaultWarehouse->id,
                    'product_id' => $product->id,
                    'quantity_on_hand' => $quantity,
                    'quantity_reserved' => 0,
                    'reorder_point' => $minStockLevel,
                    'updated_at' => now(),
                ]);
            }
        }

        return $product;
    }

    /**
     * Resolve or create unit by name/symbol.
     */
    protected function resolveUnit(string $tenantId, string $name): Unit
    {
        $normalized = mb_strtolower(trim($name));
        $unit = Unit::where('tenant_id', $tenantId)
            ->where(function ($q) use ($normalized) {
                $q->whereRaw('name::text ILIKE ?', ['%'.$normalized.'%'])
                    ->orWhere('code', $normalized);
            })
            ->first();

        if ($unit) {
            return $unit;
        }

        return Unit::create([
            'tenant_id' => $tenantId,
            'name' => [
                'hy' => $name,
                'en' => $name,
                'ru' => $name,
            ],
            'code' => mb_substr($name, 0, 10),
            'precision' => 2,
        ]);
    }

    /**
     * Resolve or create category.
     */
    protected function resolveCategory(string $tenantId, string $name, ?string $parentId = null): Category
    {
        $query = Category::where('tenant_id', $tenantId)
            ->whereRaw('name::text ILIKE ?', ['%'.trim($name).'%']);

        if ($parentId) {
            $query->where('parent_id', $parentId);
        }

        $category = $query->first();
        if ($category) {
            return $category;
        }

        return Category::create([
            'tenant_id' => $tenantId,
            'parent_id' => $parentId,
            'name' => [
                'hy' => $name,
                'en' => $name,
                'ru' => $name,
            ],
            'slug' => Str::slug($name).'-'.Str::random(4),
            'is_active' => true,
        ]);
    }

    /**
     * Parse XML invoices (supporting standard Armenian e-invoicing XML or structured invoices).
     */
    protected function parseXml(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            return [];
        }

        $items = [];
        $supplierName = (string) ($xml->SupplierName ?? $xml->SellerName ?? $xml->Seller->Name ?? '');
        $supplierTaxId = (string) ($xml->SupplierTaxId ?? $xml->SellerTIN ?? $xml->Seller->TIN ?? '');

        // Search for items in various XML structures
        $itemNodes = [];
        if (isset($xml->Items->Item)) {
            $itemNodes = $xml->Items->Item;
        } elseif (isset($xml->Item)) {
            $itemNodes = $xml->Item;
        } elseif (isset($xml->Goods->Good)) {
            $itemNodes = $xml->Goods->Good;
        } elseif (isset($xml->GoodsItem)) {
            $itemNodes = $xml->GoodsItem;
        } elseif (isset($xml->Rows->Row)) {
            $itemNodes = $xml->Rows->Row;
        } elseif (isset($xml->InvoiceLines->InvoiceLine)) {
            $itemNodes = $xml->InvoiceLines->InvoiceLine;
        } else {
            // Traverse all children recursively to find repeated item elements
            $itemNodes = $xml->xpath('//Item | //Good | //GoodsItem | //Row | //Product | //LineItem');
        }

        foreach ($itemNodes as $node) {
            $row = [
                'name' => (string) ($node->Name ?? $node->GoodsName ?? $node->ProductName ?? $node->Title ?? $node->Description ?? ''),
                'sku' => (string) ($node->SKU ?? $node->Code ?? $node->ItemCode ?? $node->Articul ?? ''),
                'barcode' => (string) ($node->Barcode ?? $node->BarCode ?? $node->EAN ?? $node->Ean13 ?? ''),
                'hs_code' => (string) ($node->HSCode ?? $node->HsCode ?? $node->FEACN ?? $node->AtgAa ?? ''),
                'unit' => (string) ($node->Unit ?? $node->UnitName ?? $node->Measure ?? 'կգ'),
                'quantity' => (float) ($node->Quantity ?? $node->Qty ?? $node->Count ?? $node->Amount ?? 0),
                'cost_price' => (float) ($node->Price ?? $node->UnitPrice ?? $node->CostPrice ?? $node->Tariff ?? 0),
                'discount_percent' => (float) ($node->Discount ?? $node->DiscountPercent ?? 0),
                'vat_rate' => (float) ($node->VAT ?? $node->VatRate ?? $node->TaxPercent ?? 20.00),
                'packaging' => (string) ($node->Packaging ?? $node->Container ?? ''),
                'category' => (string) ($node->Category ?? $node->GroupName ?? 'Հումք և նյութեր'),
                'subcategory' => (string) ($node->Subcategory ?? $node->SubGroupName ?? ''),
                'transaction_type' => (string) ($node->TransactionType ?? 'local_purchase'),
                'min_stock_level' => (float) ($node->MinStock ?? $node->MinQuantity ?? 5.00),
                'supplier_name' => (string) ($node->SupplierName ?? $supplierName),
                'supplier_tax_id' => (string) ($node->SupplierTaxId ?? $supplierTaxId),
            ];

            if (! empty($row['name'])) {
                $items[] = $row;
            }
        }

        return $items;
    }

    /**
     * Parse CSV files.
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        // Detect BOM for UTF-8
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") {
            fseek($handle, 3);
        }

        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $headers = fgetcsv($handle, 0, $delimiter, '"', '\\');
        if (! $headers) {
            fclose($handle);

            return [];
        }

        $map = $this->mapCsvHeaders($headers);
        $items = [];

        while (($data = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            if (empty(array_filter($data))) {
                continue;
            }

            $row = [];
            foreach ($map as $key => $index) {
                $row[$key] = $data[$index] ?? null;
            }

            if (! empty($row['name'])) {
                $items[] = $row;
            }
        }

        fclose($handle);

        return $items;
    }

    /**
     * Parse Excel files (.xlsx via ZipArchive or XML Spreadsheet 2003 / .xls).
     */
    protected function parseExcel(string $path, string $extension): array
    {
        if ($extension === 'xlsx') {
            return $this->parseXlsx($path);
        }

        // For .xls, check if it's XML Spreadsheet 2003
        $content = file_get_contents($path, false, null, 0, 1024);
        if (str_contains($content, '<?xml') || str_contains($content, '<Workbook')) {
            return $this->parseSpreadsheetXml($path);
        }

        // If it's a binary BIFF .xls or plain text fallback
        return $this->parseCsv($path);
    }

    /**
     * Parse modern .xlsx files using native ZipArchive without external dependencies.
     */
    protected function parseXlsx(string $path): array
    {
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            return [];
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml) {
            $xml = simplexml_load_string($stringsXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    $sharedStrings[] = (string) ($si->t ?? $si->r->t ?? '');
                }
            }
        }

        // 2. Read sheet1.xml
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! $sheetXml) {
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        if (! $xml || ! isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $type = (string) $cell['t'];
                $val = (string) $cell->v;
                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                }
                $rowData[] = $val;
            }
            if (! empty(array_filter($rowData))) {
                $rows[] = $rowData;
            }
        }

        if (empty($rows)) {
            return [];
        }

        $headers = array_shift($rows);
        $map = $this->mapCsvHeaders($headers);
        $items = [];

        foreach ($rows as $r) {
            $row = [];
            foreach ($map as $key => $index) {
                $row[$key] = $r[$index] ?? null;
            }
            if (! empty($row['name'])) {
                $items[] = $row;
            }
        }

        return $items;
    }

    /**
     * Parse SpreadsheetML 2003 XML (.xml / .xls).
     */
    protected function parseSpreadsheetXml(string $path): array
    {
        $content = file_get_contents($path);
        $xml = simplexml_load_string($content);
        if (! $xml) {
            return [];
        }

        $rows = [];
        $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $rowNodes = $xml->xpath('//ss:Row');

        foreach ($rowNodes as $row) {
            $cells = [];
            foreach ($row->xpath('ss:Cell') as $cell) {
                $data = $cell->xpath('ss:Data');
                $cells[] = (string) ($data[0] ?? '');
            }
            if (! empty(array_filter($cells))) {
                $rows[] = $cells;
            }
        }

        if (empty($rows)) {
            return [];
        }

        $headers = array_shift($rows);
        $map = $this->mapCsvHeaders($headers);
        $items = [];

        foreach ($rows as $r) {
            $row = [];
            foreach ($map as $key => $index) {
                $row[$key] = $r[$index] ?? null;
            }
            if (! empty($row['name'])) {
                $items[] = $row;
            }
        }

        return $items;
    }

    /**
     * Map table headers to internal field names.
     */
    protected function mapCsvHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $header) {
            $h = mb_strtolower(trim((string) $header));
            $h = str_replace(['_', '-', '.', '/', '(', ')', '%'], ' ', $h);
            $h = trim(preg_replace('/\s+/', ' ', $h));

            if (str_contains($h, 'շտրիխ') || str_contains($h, 'բարկոդ') || str_contains($h, 'barcode') || str_contains($h, 'ean')) {
                $map['barcode'] = $index;
            } elseif (str_contains($h, 'sku') || str_contains($h, 'արտիկուլ') || $h === 'կոդ' || $h === 'code' || str_contains($h, 'ապրանքի կոդ') || str_contains($h, 'ապրանքային կոդ')) {
                $map['sku'] = $index;
            } elseif (str_contains($h, 'անվանում') || str_contains($h, 'ապրանք') || str_contains($h, 'name') || str_contains($h, 'title')) {
                $map['name'] = $index;
            } elseif (str_contains($h, 'ատգ') || str_contains($h, 'hs') || str_contains($h, 'feacn')) {
                $map['hs_code'] = $index;
            } elseif (str_contains($h, 'գին') || str_contains($h, 'price') || str_contains($h, 'cost') || str_contains($h, 'արժեք')) {
                $map['cost_price'] = $index;
            } elseif (str_contains($h, 'չափ') || $h === 'միավոր' || $h === 'չ մ' || $h === 'չ/մ' || $h === 'unit' || str_contains($h, 'չափման')) {
                $map['unit'] = $index;
            } elseif (str_contains($h, 'քանակ') || str_contains($h, 'qty') || str_contains($h, 'quantity') || str_contains($h, 'count')) {
                $map['quantity'] = $index;
            } elseif (str_contains($h, 'զեղչ') || str_contains($h, 'discount')) {
                $map['discount_percent'] = $index;
            } elseif (str_contains($h, 'աահ') || str_contains($h, 'vat') || str_contains($h, 'tax')) {
                $map['vat_rate'] = $index;
            } elseif (str_contains($h, 'տարա') || str_contains($h, 'փաթեթ') || str_contains($h, 'pack')) {
                $map['packaging'] = $index;
            } elseif (str_contains($h, 'գործարք') || str_contains($h, 'տեսակ') || str_contains($h, 'transaction')) {
                $map['transaction_type'] = $index;
            } elseif (str_contains($h, 'նվազագույն') || str_contains($h, 'min')) {
                $map['min_stock_level'] = $index;
            } elseif (str_contains($h, 'ենթակատեգորիա') || str_contains($h, 'ենթախումբ') || str_contains($h, 'subcat')) {
                $map['subcategory'] = $index;
            } elseif (str_contains($h, 'կատեգորիա') || str_contains($h, 'խումբ') || str_contains($h, 'cat')) {
                $map['category'] = $index;
            } elseif (str_contains($h, 'հվհհ') || str_contains($h, 'tin')) {
                $map['supplier_tax_id'] = $index;
            } elseif (str_contains($h, 'մատակարար') || str_contains($h, 'supplier') || str_contains($h, 'seller')) {
                $map['supplier_name'] = $index;
            }
        }

        return $map;
    }
}
