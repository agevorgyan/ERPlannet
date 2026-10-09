<?php

namespace Tests\Feature;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTechnicalCardAndDirectoryTest extends TestCase
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

    public function test_can_create_product_with_all_extended_attributes(): void
    {
        $payload = [
            'name' => [
                'hy' => 'Պեպերոնի Պիցցա Պրեմիում',
                'en' => 'Pepperoni Pizza Premium',
                'ru' => 'Пицца Пепперони Премиум',
            ],
            'type' => 'finished_product',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'sku' => 'PRD-PEP-PREM-01',
            'barcode' => '4850001234567',
            'hs_code' => '1905 90 900 0',
            'net_quantity' => '520 գրամ',
            'packaging' => 'Տուփ 32սմ',
            'description' => [
                'hy' => 'Դասական իտալական խմոր, պեպերոնի երշիկ, մոցարելա և տոմատի սոուս:',
            ],
            'sale_price' => 3800,
            'special_price' => 3200,
            'cost_price' => 1450,
            'has_vat' => true,
            'vat_rate' => 20,
            'allow_discount' => true,
            'allow_price_edit' => false,
            'allow_modifiers' => true,
            'is_ungrouped_in_order' => true,
            'is_stop_list' => false,
            'is_excise' => false,
            'is_marked' => false,
            'track_stock' => true,
            'min_stock_level' => 5,
            'initial_stock' => 20,
            'calories' => 285.5,
            'nutritional_info' => [
                'protein' => 11.2,
                'fat' => 14.5,
                'carbs' => 29.8,
            ],
            'allergens' => ['gluten', 'milk'],
            'dietary_tags' => ['kosher'],
            'time_availability' => [
                'from' => '10:00',
                'to' => '23:00',
            ],
            'discount_hours' => [
                'from' => '21:00',
                'to' => '23:00',
            ],
            'shelf_life_info' => '24 ժամ +2°C-ից +6°C',
            'variants' => [
                [
                    'name' => ['hy' => 'Մեծ 36սմ'],
                    'sku' => 'PRD-PEP-PREM-01-LG',
                    'barcode' => '4850001234574',
                    'sale_price' => 4500,
                    'cost_price' => 1800,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/products', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.sku', 'PRD-PEP-PREM-01');
        $response->assertJsonPath('data.allow_modifiers', true);
        $response->assertJsonPath('data.is_ungrouped_in_order', true);
        $response->assertJsonPath('data.net_quantity', '520 գրամ');
        $response->assertJsonPath('data.special_price', '3200.00');

        $this->assertDatabaseHas('products', [
            'tenant_id' => $this->tenant->id,
            'sku' => 'PRD-PEP-PREM-01',
            'type' => 'finished_product',
            'allow_modifiers' => true,
            'is_ungrouped_in_order' => true,
            'net_quantity' => '520 գրամ',
        ]);

        $productId = $response->json('data.id');
        $this->assertDatabaseHas('stock_levels', [
            'tenant_id' => $this->tenant->id,
            'product_id' => $productId,
            'quantity_on_hand' => 20,
        ]);
    }

    public function test_can_save_technical_card_with_ingredients_and_semi_finished_components_and_calculates_weighted_cost(): void
    {
        // 1. Raw Ingredient component
        $flour = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'sku' => 'ING-FLOUR-01',
            'type' => 'ingredient',
            'name' => ['hy' => 'Ալյուր Բ/Տ'],
            'cost_price' => 400,
            'sale_price' => 0,
            'track_stock' => true,
        ]);

        // 2. Semi-finished product component
        $dough = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'sku' => 'SEMI-DOUGH-01',
            'type' => 'semi_finished',
            'name' => ['hy' => 'Խմոր Կիսաֆաբրիկատ'],
            'cost_price' => 600,
            'sale_price' => 0,
            'track_stock' => true,
        ]);

        // 3. Finished Product
        $pizza = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'sku' => 'PRD-PIZZA-BOM-TEST',
            'type' => 'finished_product',
            'name' => ['hy' => 'Մարգարիտա Պիցցա'],
            'cost_price' => 0,
            'sale_price' => 3000,
            'track_stock' => true,
        ]);

        // Save Technical Card
        // Yield: 2 pizzas
        // Component 1: Flour (0.5 kg net, 10% waste => gross 0.55 * 400 = 220)
        // Component 2: Dough (0.8 kg net, 0% waste => gross 0.8 * 600 = 480)
        // Raw Materials = 220 + 480 = 700
        // Scrap % = 5% => scrap cost = 700 * 0.05 = 35
        // Labor = 200, Overhead = 100
        // Total = 700 + 35 + 200 + 100 = 1035
        // Unit Cost = 1035 / 2 = 517.5
        $tcPayload = [
            'code' => 'RCP-PIZZA-MARG-V1',
            'name' => 'Մարգարիտա Տեխնոլոգիական Քարտ',
            'version' => '1.0',
            'yield_quantity' => 2.0,
            'yield_unit_id' => $this->unit->id,
            'scrap_percentage' => 5.0,
            'labor_cost' => 200,
            'overhead_cost' => 100,
            'instructions' => 'Խառնել, հասունացնել, թխել 350°C ջերմաստիճանում:',
            'apply_to_cost_price' => true,
            'items' => [
                [
                    'product_id' => $flour->id,
                    'quantity' => 0.5,
                    'unit_id' => $this->unit->id,
                    'waste_percentage' => 10.0,
                    'cost_per_unit' => 400,
                ],
                [
                    'product_id' => $dough->id,
                    'quantity' => 0.8,
                    'unit_id' => $this->unit->id,
                    'waste_percentage' => 0.0,
                    'cost_per_unit' => 600,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/products/{$pizza->id}/technical-card", $tcPayload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('breakdown.raw_material_cost', 700);
        $response->assertJsonPath('breakdown.scrap_cost', 35);
        $response->assertJsonPath('breakdown.labor_cost', 200);
        $response->assertJsonPath('breakdown.overhead_cost', 100);
        $response->assertJsonPath('breakdown.total_cost', 1035);
        $response->assertJsonPath('breakdown.unit_cost', 517.5);

        // Verify product cost_price was synced
        $pizza->refresh();
        $this->assertEquals(517.5, (float) $pizza->cost_price);
        $this->assertTrue($pizza->is_produced);

        // Get technical card
        $getResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson("/api/v1/products/{$pizza->id}/technical-card");

        $getResponse->assertStatus(200);
        $getResponse->assertJsonPath('has_recipe', true);
        $getResponse->assertJsonPath('data.code', 'RCP-PIZZA-MARG-V1');
    }

    public function test_production_run_automatically_deducts_components_and_yields_finished_product_stock(): void
    {
        // 1. Raw Ingredient
        $ingredient = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'sku' => 'ING-CHEESE-01',
            'type' => 'ingredient',
            'name' => ['hy' => 'Մոցարելա Պանիր'],
            'cost_price' => 3000,
            'sale_price' => 0,
            'track_stock' => true,
        ]);

        // 2. Finished Product
        $finishedProduct = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'sku' => 'PRD-KHACHAPURI-01',
            'type' => 'finished_product',
            'name' => ['hy' => 'Խաչապուրի Իմերեթական'],
            'cost_price' => 900,
            'sale_price' => 2000,
            'track_stock' => true,
        ]);

        // Put initial stock of ingredient in warehouse
        StockLevel::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $ingredient->id,
            'quantity_on_hand' => 10.0,
            'quantity_reserved' => 0,
            'reorder_point' => 1.0,
        ]);

        // Setup active recipe: yield = 1, consumes 0.25 kg cheese (gross: 0.25)
        $recipe = Recipe::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $finishedProduct->id,
            'code' => 'RCP-KHACH-01',
            'name' => 'Խաչապուրի Տեխ. Քարտ',
            'yield_quantity' => 1.0,
            'yield_unit_id' => $this->unit->id,
            'scrap_percentage' => 0,
            'labor_cost' => 150,
            'overhead_cost' => 0,
            'is_active' => true,
        ]);

        $recipe->items()->create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $ingredient->id,
            'quantity' => 0.25,
            'gross_quantity' => 0.25,
            'unit_id' => $this->unit->id,
            'waste_percentage' => 0,
            'cost_per_unit' => 3000,
            'sort_order' => 1,
        ]);

        // Produce 4 finished items
        // Consumption should be: 4 * 0.25 = 1.0 kg cheese
        // Remaining cheese should be: 10.0 - 1.0 = 9.0 kg
        // Finished product stock should be: 4.0
        $producePayload = [
            'quantity' => 4.0,
            'warehouse_id' => $this->warehouse->id,
            'source_warehouse_id' => $this->warehouse->id,
            'notes' => 'Արտադրական փորձարկում',
        ];

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/products/{$finishedProduct->id}/produce", $producePayload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.produced_quantity', 4);
        $response->assertJsonPath('data.current_stock', 4);

        // Assert ingredient stock was deducted
        $ingredientStock = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $ingredient->id)
            ->value('quantity_on_hand');
        $this->assertEquals(9.0, (float) $ingredientStock);

        // Assert finished product stock was created/incremented
        $productStock = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $finishedProduct->id)
            ->value('quantity_on_hand');
        $this->assertEquals(4.0, (float) $productStock);
    }

    public function test_catalog_view_renders_successfully_under_directory_menu(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Տեղեկագիր');
        $response->assertSee('view-catalog');
        $response->assertSee('create-product-modal');
        $response->assertSee('product-technical-card-modal');
        $response->assertSee('product-quick-produce-modal');
        $response->assertSee('Модификаторы');
        $response->assertSee('Стоп-лист');
        $response->assertSee('Подакцизный товар');
        $response->assertSee('Маркированный товар');
        $response->assertSee('EU Allergens Tagging');
    }
}
