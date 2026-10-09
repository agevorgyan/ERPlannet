<?php

namespace Tests\Feature\Phase3;

use App\Domain\Billing\Exceptions\FeatureNotAvailableException;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\Manufacturing\Actions\CreateRecipeAction;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeAndBOMTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Product $matnakash;

    protected Product $flour;

    protected Product $yeast;

    protected Unit $kg;

    protected Unit $pcs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $this->kg = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'KG'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'is_fractional' => true]
        );

        $this->pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'bakery'],
            ['name' => 'Bakery & Bread', 'is_active' => true]
        );

        $this->matnakash = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'BREAD-MATNAKASH'],
            [
                'name' => 'Tonir Matnakash Traditional',
                'category_id' => $cat->id,
                'unit_id' => $this->pcs->id,
                'price' => 300,
                'cost' => 150,
                'type' => 'manufactured',
                'shelf_life_days' => 3,
                'is_active' => true,
            ]
        );

        $this->flour = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'ING-FLOUR-PREMIUM'],
            [
                'name' => 'Wheat Flour Premium Grade',
                'category_id' => $cat->id,
                'unit_id' => $this->kg->id,
                'price' => 450,
                'cost' => 320,
                'type' => 'raw_material',
                'is_active' => true,
            ]
        );

        $this->yeast = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'ING-YEAST-DRY'],
            [
                'name' => 'Baking Yeast Dry',
                'category_id' => $cat->id,
                'unit_id' => $this->kg->id,
                'price' => 2500,
                'cost' => 1800,
                'type' => 'raw_material',
                'is_active' => true,
            ]
        );
    }

    public function test_can_create_recipe_bom_with_ingredients_via_action(): void
    {
        $action = app(CreateRecipeAction::class);

        $recipe = $action->execute([
            'product_id' => $this->matnakash->id,
            'product_variant_id' => null,
            'code' => 'RCP-MATNAKASH-STD',
            'name' => 'Standard Tonir Matnakash Recipe (100 pcs)',
            'yield_quantity' => 100.0,
            'yield_unit_id' => $this->pcs->id,
            'items' => [
                [
                    'product_id' => $this->flour->id,
                    'quantity' => 50.0, // 50 kg for 100 loaves
                    'unit_id' => $this->kg->id,
                    'waste_percentage' => 2.0,
                    'sort_order' => 1,
                    'notes' => 'Sifted flour',
                ],
                [
                    'product_id' => $this->yeast->id,
                    'quantity' => 1.5, // 1.5 kg for 100 loaves
                    'unit_id' => $this->kg->id,
                    'waste_percentage' => 0.0,
                    'sort_order' => 2,
                    'notes' => 'Dissolved in warm water',
                ],
            ],
            'scrap_percentage' => 1.0,
            'labor_cost' => 3500.0,
            'overhead_cost' => 1500.0,
            'instructions' => 'Mix dough for 15 mins, proof for 45 mins, bake in tonir at 240°C for 12 mins.',
            'version' => 'v1.0',
        ]);

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals('RCP-MATNAKASH-STD', $recipe->code);
        $this->assertEquals(100.0, (float) $recipe->yield_quantity);
        $this->assertCount(2, $recipe->items);
        $this->assertEquals(3500.0, (float) $recipe->labor_cost);
        $this->assertEquals(1500.0, (float) $recipe->overhead_cost);

        $flourItem = $recipe->items->where('product_id', $this->flour->id)->first();
        $this->assertNotNull($flourItem);
        $this->assertEquals(50.0, (float) $flourItem->quantity);
        $this->assertEquals(2.0, (float) $flourItem->waste_percentage);
    }

    public function test_can_create_and_list_recipes_via_rest_api(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/recipes', [
                'product_id' => $this->matnakash->id,
                'code' => 'RCP-MATNAKASH-REST',
                'name' => 'API Created Matnakash Recipe',
                'yield_quantity' => 50.0,
                'yield_unit_id' => $this->pcs->id,
                'labor_cost' => 1800.0,
                'overhead_cost' => 900.0,
                'scrap_percentage' => 1.5,
                'instructions' => 'Standard operating procedure for baking',
                'items' => [
                    [
                        'product_id' => $this->flour->id,
                        'quantity' => 25.0,
                        'unit_id' => $this->kg->id,
                        'waste_percentage' => 1.0,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'RCP-MATNAKASH-REST')
            ->assertJsonPath('data.product_id', $this->matnakash->id);

        $recipeId = $response->json('data.id');

        // Fetch single recipe
        $showResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson("/api/v1/recipes/{$recipeId}");

        $showResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'API Created Matnakash Recipe')
            ->assertJsonCount(1, 'data.items');

        // List recipes
        $listResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/recipes');

        $listResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_cannot_create_recipe_if_plan_does_not_have_production_feature(): void
    {
        // Downgrade tenant to starter plan which has feature.production = false
        $starterPlan = Plan::where('code', 'starter')->firstOrFail();
        $sub = Subscription::where('tenant_id', $this->tenant->id)->first();
        if ($sub) {
            $sub->update(['plan_id' => $starterPlan->id]);
        }

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/recipes', [
                'product_id' => $this->matnakash->id,
                'code' => 'RCP-FAIL',
                'name' => 'Blocked Recipe',
                'yield_quantity' => 10.0,
                'yield_unit_id' => $this->pcs->id,
                'items' => [
                    [
                        'product_id' => $this->flour->id,
                        'quantity' => 5.0,
                        'unit_id' => $this->kg->id,
                    ],
                ],
            ]);

        // Should be forbidden due to FeatureNotAvailableException
        $response->assertStatus(403);
    }
}
