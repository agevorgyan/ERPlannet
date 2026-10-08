<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable();
            $table->string('slug', 100);
            $table->jsonb('name'); // {"hy": "...", "en": "...", "ru": "..."}
            $table->jsonb('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('image_url', 500)->nullable();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'slug'], 'uq_categories_tenant_slug');
            $table->index(['tenant_id', 'parent_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('code', 20); // 'kg', 'g', 'pcs', 'l', 'box'
            $table->jsonb('name'); // {"hy": "կգ", "en": "kg", "ru": "кг"}
            $table->integer('precision')->default(0); // decimal places (0 for pcs, 3 for kg)
            $table->timestampsTz();

            $table->unique(['tenant_id', 'code'], 'uq_units_tenant_code');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();
            $table->string('sku', 100);
            $table->string('barcode', 100)->nullable();
            $table->jsonb('name'); // {"hy": "...", "en": "...", "ru": "..."}
            $table->jsonb('description')->nullable();
            $table->decimal('cost_price', 15, 2)->default(0.00);
            $table->decimal('sale_price', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('AMD');
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_produced')->default(false); // food manufacturing / recipes
            $table->boolean('is_active')->default(true);
            $table->jsonb('images')->default('[]');
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'sku'], 'uq_products_tenant_sku');
            $table->index(['tenant_id', 'barcode']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 100);
            $table->string('barcode', 100)->nullable();
            $table->jsonb('name');
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2);
            $table->jsonb('attributes')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['tenant_id', 'sku'], 'uq_variants_tenant_sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
    }
};
