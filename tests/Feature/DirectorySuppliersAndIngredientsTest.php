<?php

namespace Tests\Feature;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DirectorySuppliersAndIngredientsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Warehouse $warehouse;

    protected Unit $unit;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->unit = Unit::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->category = Category::where('tenant_id', $this->tenant->id)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);
    }

    public function test_can_create_supplier_with_couriers_and_attached_products(): void
    {
        $existingProduct = Product::where('tenant_id', $this->tenant->id)->firstOrFail();

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/suppliers', [
                'company_name' => 'Արմենիա Ֆուդ Տրեյդ ՍՊԸ',
                'legal_name' => '«Արմենիա Ֆուդ Տրեյդ» ՍՊԸ',
                'tax_id' => '02938475',
                'phone' => '+37493123456',
                'email' => 'sales@armfood.am',
                'website' => 'https://armfood.am',
                'legal_address' => 'ք․ Երևան, Տերյան 10',
                'shipping_address' => 'ք․ Երևան, Դավիթ Բեկի 100/1',
                'is_active' => true,
                'couriers' => [
                    [
                        'name' => 'Արմեն Պետրոսյան',
                        'phone' => '+37494112233',
                        'vehicle_model' => 'Mercedes Sprinter',
                        'license_plate' => '35 AA 555',
                    ],
                    [
                        'name' => 'Գևորգ Սարգսյան',
                        'phone' => '+37491778899',
                        'vehicle_model' => 'Ford Transit',
                        'license_plate' => '77 GG 777',
                    ],
                ],
                'product_ids' => [$existingProduct->id],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.company_name', 'Արմենիա Ֆուդ Տրեյդ ՍՊԸ')
            ->assertJsonPath('data.website', 'https://armfood.am')
            ->assertJsonPath('data.legal_address', 'ք․ Երևան, Տերյան 10')
            ->assertJsonPath('data.shipping_address', 'ք․ Երևան, Դավիթ Բեկի 100/1');

        $supplierId = $response->json('data.id');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplierId,
            'company_name' => 'Արմենիա Ֆուդ Տրեյդ ՍՊԸ',
            'tax_id' => '02938475',
            'website' => 'https://armfood.am',
        ]);

        $this->assertDatabaseHas('supplier_couriers', [
            'supplier_id' => $supplierId,
            'name' => 'Արմեն Պետրոսյան',
            'license_plate' => '35 AA 555',
        ]);

        $this->assertDatabaseHas('supplier_products', [
            'supplier_id' => $supplierId,
            'product_id' => $existingProduct->id,
        ]);
    }

    public function test_can_toggle_suspend_supplier(): void
    {
        $supplier = Supplier::where('tenant_id', $this->tenant->id)->firstOrFail();
        $initialStatus = $supplier->is_active;

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/suppliers/{$supplier->id}/toggle-suspend");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_active', ! $initialStatus);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'is_active' => ! $initialStatus,
        ]);
    }

    public function test_supplier_trash_soft_delete_and_restore_and_force_delete(): void
    {
        $supplier = Supplier::create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Թեստային Մատակարար Զամբյուղի Համար ՓԲԸ',
            'tax_id' => '99887766',
            'phone' => '+37410998877',
            'is_active' => true,
        ]);

        // 1. Soft delete (move to trash)
        $delResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->deleteJson("/api/v1/suppliers/{$supplier->id}");

        $delResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);

        // 2. Query trash list
        $trashList = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/suppliers?status=trash');

        $trashList->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertTrue(collect($trashList->json('data'))->contains('id', $supplier->id));

        // 3. Restore from trash
        $restoreResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/suppliers/{$supplier->id}/restore");

        $restoreResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotSoftDeleted('suppliers', ['id' => $supplier->id]);

        // 4. Move to trash again and Force delete
        $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->deleteJson("/api/v1/suppliers/{$supplier->id}");

        $forceResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->deleteJson("/api/v1/suppliers/{$supplier->id}/force");

        $forceResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_can_create_and_list_ingredients_with_calculations_and_where_used(): void
    {
        $supplier = Supplier::where('tenant_id', $this->tenant->id)->firstOrFail();

        $createResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/ingredients', [
                'name' => 'Շաքարավազ Սպիտակ Բյուրեղային',
                'sku' => 'ING-SUGAR-CRYSTAL',
                'barcode' => '485000998877',
                'hs_code' => '1701 99 100 0',
                'category_id' => $this->category->id,
                'unit_id' => $this->unit->id,
                'cost_price' => 450.00,
                'quantity' => 100,
                'discount_percent' => 5.00,
                'vat_rate' => 20.00,
                'packaging' => 'Պարկ 50կգ',
                'transaction_type' => 'local_purchase',
                'min_stock_level' => 30.00,
                'warehouse_id' => $this->warehouse->id,
                'supplier_ids' => [$supplier->id],
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', 'ING-SUGAR-CRYSTAL')
            ->assertJsonPath('data.type', Product::TYPE_INGREDIENT);

        $ingredientId = $createResponse->json('data.id');

        // Check listing and calculated values
        $listResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/ingredients?search=SUGAR');

        $listResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $item = collect($listResponse->json('data'))->firstWhere('id', $ingredientId);
        $this->assertNotNull($item);
        $this->assertEquals(450.00, $item['cost_price']);
        $this->assertEquals(5.00, $item['discount_percent']);
        $this->assertEquals('Պարկ 50կգ', $item['packaging']);
        $this->assertEquals(100, $item['current_stock']);
        $this->assertFalse($item['is_low_stock']);
    }

    public function test_ingredient_low_stock_detection_and_filtering(): void
    {
        $lowItem = Product::create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unit->id,
            'type' => Product::TYPE_INGREDIENT,
            'sku' => 'ING-LOW-VANILLA',
            'name' => ['hy' => 'Վանիլին Բնական'],
            'cost_price' => 1200.00,
            'sale_price' => 1500.00,
            'min_stock_level' => 50.00,
            'track_stock' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/ingredients?low_stock=1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertTrue(collect($response->json('data'))->contains('id', $lowItem->id));
    }

    public function test_can_import_invoices_from_csv(): void
    {
        $csvContent = implode("\n", [
            'Անվանում,SKU,Շտրիխկոդ,ԱՏԳ ԱԱ,Չափման միավոր,Քանակ,Գին,Զեղչ,ԱԱՀ,Տարա',
            'Կակաոյի Փոշի Պրեմիում,ING-CACAO-01,485000112233,1805 00 000 0,կգ,25,2800,10,20,Տուփ 25կգ',
            'Կոկոսի Քերուկ,ING-COCONUT-01,485000445566,0801 11 000 0,կգ,40,1900,0,20,Պարկ 20կգ',
        ]);

        $file = UploadedFile::fake()->createWithContent('invoice.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/ingredients/import-invoices', [
                'file' => $file,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('imported_count', 2);

        $this->assertDatabaseHas('products', [
            'tenant_id' => $this->tenant->id,
            'sku' => 'ING-CACAO-01',
            'type' => Product::TYPE_INGREDIENT,
        ]);

        $this->assertDatabaseHas('products', [
            'tenant_id' => $this->tenant->id,
            'sku' => 'ING-COCONUT-01',
            'type' => Product::TYPE_INGREDIENT,
        ]);
    }

    public function test_can_import_invoices_from_xml(): void
    {
        $xmlContent = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<Invoice>
    <SupplierName>Էկո Ֆարմ ՍՊԸ</SupplierName>
    <SupplierTaxId>01298374</SupplierTaxId>
    <Items>
        <Item>
            <Name>Մեղր Լեռնային Բնական</Name>
            <SKU>ING-HONEY-01</SKU>
            <Barcode>485000778899</Barcode>
            <HSCode>0409 00 000 0</HSCode>
            <Unit>կգ</Unit>
            <Quantity>50</Quantity>
            <Price>3200</Price>
            <Discount>5</Discount>
            <VAT>20</VAT>
            <Packaging>Դույլ 10կգ</Packaging>
        </Item>
    </Items>
</Invoice>
XML;

        $file = UploadedFile::fake()->createWithContent('invoice.xml', $xmlContent);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/ingredients/import-invoices', [
                'file' => $file,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('imported_count', 1);

        $this->assertDatabaseHas('products', [
            'tenant_id' => $this->tenant->id,
            'sku' => 'ING-HONEY-01',
            'type' => Product::TYPE_INGREDIENT,
        ]);

        $this->assertDatabaseHas('suppliers', [
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Էկո Ֆարմ ՍՊԸ',
            'tax_id' => '01298374',
        ]);
    }
}
