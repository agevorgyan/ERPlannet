<?php

namespace Tests\Feature\BusinessCore;

use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected string $token;
    protected Unit $unitKg;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Armenian Bakery',
            'slug' => 'armbakery',
            'subdomain' => 'armbakery',
            'currency' => 'AMD',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Karen',
            'email' => 'karen@bakery.am',
            'password' => Hash::make('password123'),
            'is_owner' => true,
        ]);

        $this->token = $this->user->createToken('token')->plainTextToken;

        // Plan with products limit = 2
        $plan = Plan::create(['code' => 'plan_2_prods', 'name' => '2 Products', 'price_monthly' => 15000, 'price_yearly' => 150000]);
        $feat = Feature::create(['code' => 'limit.products', 'name' => 'Products', 'type' => 'limit', 'module' => 'catalog']);
        $plan->features()->attach($feat->id, ['value' => '2']);

        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->unitKg = Unit::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'kg',
            'name' => ['hy' => 'կգ', 'en' => 'kg', 'ru' => 'кг'],
            'precision' => 3,
        ]);

        $this->category = Category::create([
            'tenant_id' => $this->tenant->id,
            'slug' => 'bread',
            'name' => ['hy' => 'Հացաբուլկեղեն', 'en' => 'Bakery', 'ru' => 'Выпечка'],
        ]);
    }

    public function test_tenant_can_create_product_with_multilingual_name_and_variants(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'armbakery',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'unit_id' => $this->unitKg->id,
            'sku' => 'MATNAKASH-01',
            'barcode' => '4850001112223',
            'name' => [
                'hy' => 'Մատնաքաշ',
                'en' => 'Matnakash Bread',
                'ru' => 'Матнакаш',
            ],
            'cost_price' => 150.00,
            'sale_price' => 250.00,
            'track_stock' => true,
            'is_produced' => true,
            'variants' => [
                [
                    'sku' => 'MATNAKASH-500G',
                    'name' => ['hy' => '500 գրամ'],
                    'sale_price' => 250.00,
                    'attributes' => ['weight' => '500g'],
                ],
                [
                    'sku' => 'MATNAKASH-1KG',
                    'name' => ['hy' => '1 կգ'],
                    'sale_price' => 450.00,
                    'attributes' => ['weight' => '1kg'],
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', 'MATNAKASH-01')
            ->assertJsonFragment(['sku' => 'MATNAKASH-500G'])
            ->assertJsonFragment(['sku' => 'MATNAKASH-1KG']);

        $this->assertDatabaseHas('products', [
            'tenant_id' => $this->tenant->id,
            'sku' => 'MATNAKASH-01',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'tenant_id' => $this->tenant->id,
            'sku' => 'MATNAKASH-500G',
        ]);
    }

    public function test_product_quota_limit_is_strictly_enforced(): void
    {
        // Allowed limit is 2
        Product::create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unitKg->id,
            'sku' => 'P1',
            'name' => ['hy' => 'Product 1'],
            'sale_price' => 100,
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unitKg->id,
            'sku' => 'P2',
            'name' => ['hy' => 'Product 2'],
            'sale_price' => 200,
        ]);

        // Attempting to create 3rd product
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'armbakery',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/products', [
            'unit_id' => $this->unitKg->id,
            'sku' => 'P3',
            'name' => 'Product 3',
            'sale_price' => 300,
        ]);

        $response->assertStatus(402);
    }
}
