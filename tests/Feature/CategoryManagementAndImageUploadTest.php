<?php

namespace Tests\Feature;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryManagementAndImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Unit $unit;

    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->unit = Unit::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);
    }

    public function test_media_upload_endpoint_stores_image_and_returns_public_url(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('bakery_item.jpg', 640, 480);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/media/upload', [
                'file' => $file,
                'folder' => 'products',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Պատկերը հաջողությամբ վերբեռնվեց:',
            ])
            ->assertJsonStructure([
                'data' => ['url', 'path', 'filename', 'size', 'mime_type'],
            ]);

        $url = $response->json('data.url');
        $path = $response->json('data.path');
        $this->assertStringStartsWith('/storage/uploads/products/', $url);
        Storage::disk('public')->assertExists($path);
    }

    public function test_can_create_category_with_direct_image_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('desserts.png', 400, 400);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/categories', [
                'name' => [
                    'hy' => 'Աղանդերներ & Թխվածքաբլիթներ',
                    'en' => 'Desserts & Pastries',
                    'ru' => 'Десерты и выпечка',
                ],
                'slug' => 'desserts-pastries',
                'description' => 'Բոլոր տեսակի թարմ պատրաստված աղանդերներ',
                'sort_order' => 5,
                'is_active' => true,
                'image' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Կատեգորիան հաջողությամբ ստեղծվեց:',
            ]);

        $categoryId = $response->json('data.id');
        $category = Category::findOrFail($categoryId);

        $this->assertEquals('desserts-pastries', $category->slug);
        $this->assertEquals('Աղանդերներ & Թխվածքաբլիթներ', $category->name['hy']);
        $this->assertEquals('Desserts & Pastries', $category->name['en']);
        $this->assertEquals(5, $category->sort_order);
        $this->assertTrue($category->is_active);
        $this->assertNotNull($category->image_url);
        $this->assertStringStartsWith('/storage/uploads/categories/', $category->image_url);
    }

    public function test_can_create_subcategory_with_parent_id(): void
    {
        $parent = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => ['hy' => 'Խմիչքներ', 'en' => 'Beverages'],
            'slug' => 'beverages-root',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/categories', [
                'parent_id' => $parent->id,
                'name' => ['hy' => 'Սառը Սուրճեր', 'en' => 'Cold Coffees'],
                'slug' => 'cold-coffees',
                'sort_order' => 2,
                'is_active' => true,
                'image_url' => 'https://example.com/cold-coffee.jpg',
            ]);

        $response->assertStatus(201);
        $childId = $response->json('data.id');

        $child = Category::with('parent')->findOrFail($childId);
        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertEquals('Խմիչքներ', $child->parent->name['hy']);
        $this->assertEquals('https://example.com/cold-coffee.jpg', $child->image_url);
    }

    public function test_can_update_category_and_change_image(): void
    {
        Storage::fake('public');

        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => ['hy' => 'Նախնական Խումբ', 'en' => 'Initial Group'],
            'slug' => 'initial-group',
            'image_url' => '/storage/old_image.jpg',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $newFile = UploadedFile::fake()->image('updated_cover.png', 500, 500);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => ['hy' => 'Թարմացված Խումբ', 'en' => 'Updated Group'],
                'sort_order' => 10,
                'image' => $newFile,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Կատեգորիան հաջողությամբ թարմացվեց:',
            ]);

        $category->refresh();
        $this->assertEquals('Թարմացված Խումբ', $category->name['hy']);
        $this->assertEquals(10, $category->sort_order);
        $this->assertStringStartsWith('/storage/uploads/categories/', $category->image_url);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => ['hy' => 'Ջնջման ենթակա', 'en' => 'To Delete'],
            'slug' => 'to-delete',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Կատեգորիան հեռացվել է:',
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_product_creation_and_update_with_uploaded_image_url(): void
    {
        $category = Category::firstOrCreate([
            'tenant_id' => $this->tenant->id,
            'slug' => 'pizza-test-cat',
        ], [
            'name' => ['hy' => 'Պիցցաներ'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/products', [
                'name' => ['hy' => 'Մարգարիտա Պիցցա', 'en' => 'Margherita Pizza'],
                'type' => 'finished_product',
                'sku' => 'PRD-PIZZA-MARG-01',
                'category_id' => $category->id,
                'unit_id' => $this->unit->id,
                'sale_price' => 3500,
                'cost_price' => 1200,
                'images' => ['/storage/uploads/products/margherita.jpg'],
            ]);

        $response->assertStatus(201);
        $prodId = $response->json('data.id');

        $product = Product::findOrFail($prodId);
        $this->assertIsArray($product->images);
        $this->assertContains('/storage/uploads/products/margherita.jpg', $product->images);

        // Update product with secondary image
        $updateResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->putJson("/api/v1/products/{$prodId}", [
                'images' => ['/storage/uploads/products/margherita_v2.jpg'],
            ]);

        $updateResponse->assertStatus(200);
        $product->refresh();
        $this->assertEquals(['/storage/uploads/products/margherita_v2.jpg'], $product->images);
    }

    public function test_ingredient_creation_and_update_with_uploaded_image_url(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/ingredients', [
                'name' => 'Մոցարելա Պանիր',
                'unit_id' => $this->unit->id,
                'sku' => 'ING-MOZZ-01',
                'cost_price' => 2800,
                'images' => ['/storage/uploads/ingredients/mozzarella.jpg'],
            ]);

        $response->assertStatus(201);
        $ingId = $response->json('data.id');

        $ingredient = Product::findOrFail($ingId);
        $this->assertIsArray($ingredient->images);
        $this->assertContains('/storage/uploads/ingredients/mozzarella.jpg', $ingredient->images);

        // Update ingredient image
        $updateResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->putJson("/api/v1/ingredients/{$ingId}", [
                'images' => ['/storage/uploads/ingredients/mozzarella_fresh.jpg'],
            ]);

        $updateResponse->assertStatus(200);
        $ingredient->refresh();
        $this->assertEquals(['/storage/uploads/ingredients/mozzarella_fresh.jpg'], $ingredient->images);
    }

    public function test_can_filter_categories_by_product_and_ingredient_type(): void
    {
        $prodCat = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => ['hy' => 'Ապուրներ', 'en' => 'Soups'],
            'slug' => 'soups-test',
            'type' => Category::TYPE_PRODUCT,
            'is_active' => true,
        ]);

        $ingCat = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => ['hy' => 'Կաթնամթերքի Հումք', 'en' => 'Dairy Raw Materials'],
            'slug' => 'dairy-raw-test',
            'type' => Category::TYPE_INGREDIENT,
            'is_active' => true,
        ]);

        // Filter products only
        $prodResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/categories?type=product');

        $prodResponse->assertStatus(200);
        $prodData = collect($prodResponse->json('data'));
        $this->assertTrue($prodData->pluck('id')->contains($prodCat->id));
        $this->assertFalse($prodData->pluck('id')->contains($ingCat->id));

        // Filter ingredients only
        $ingResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/categories?type=ingredient');

        $ingResponse->assertStatus(200);
        $ingData = collect($ingResponse->json('data'));
        $this->assertTrue($ingData->pluck('id')->contains($ingCat->id));
        $this->assertFalse($ingData->pluck('id')->contains($prodCat->id));
    }

    public function test_can_create_ingredient_category_with_type_field(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/categories', [
                'name' => [
                    'hy' => 'Համեմունքներ & Հավելումներ',
                    'en' => 'Spices & Seasonings',
                ],
                'type' => Category::TYPE_INGREDIENT,
                'slug' => 'spices-seasonings',
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => Category::TYPE_INGREDIENT,
                ],
            ]);

        $created = Category::findOrFail($response->json('data.id'));
        $this->assertEquals(Category::TYPE_INGREDIENT, $created->type);
    }

    public function test_welcome_blade_contains_category_management_and_image_uploaders(): void
    {
        $response = $this->get('/?tenant=gourmet');

        $response->assertStatus(200);

        // Assert Directory navigation has Categories
        $response->assertSee('view-directory-categories', false);
        $response->assertSee('Կատեգորիաներ', false);

        // Assert Category modal and dropzone exists
        $response->assertSee('directory-category-modal', false);
        $response->assertSee('cat-image-file', false);
        $response->assertSee('cat-image-preview', false);
        $response->assertSee('cat-image-url', false);

        // Assert Category Type radio selectors exist
        $response->assertSee('cat-type-product', false);
        $response->assertSee('cat-type-ingredient', false);
        $response->assertSee('Ապրանքային Խումբ', false);
        $response->assertSee('Բաղադրիչների Խումբ', false);

        // Assert Category KPI badges and tabs for separated types
        $response->assertSee('cat-kpi-product', false);
        $response->assertSee('cat-kpi-ingredient', false);
        $response->assertSee('cat-tab-count-product', false);
        $response->assertSee('cat-tab-count-ingredient', false);

        // Assert Product modal image uploader exists
        $response->assertSee('prod-image-file', false);
        $response->assertSee('prod-image-preview', false);
        $response->assertSee('prod-image-url', false);

        // Assert Ingredient modal image uploader exists
        $response->assertSee('ing-image-file', false);
        $response->assertSee('ing-image-preview', false);
        $response->assertSee('ing-image-url', false);
    }
}
