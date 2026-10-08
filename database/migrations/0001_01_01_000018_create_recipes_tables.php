<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add shelf_life_days to products table
        Schema::table('products', function (Blueprint $table) {
            $table->integer('shelf_life_days')->nullable()->after('track_stock');
        });

        // 2. Recipes / Bill of Materials (BOM)
        Schema::create('recipes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('code', 50); // e.g. RCP-MATNAKASH-V1
            $table->string('name', 255);
            $table->string('version', 20)->default('1.0');
            $table->decimal('yield_quantity', 15, 4); // Target batch output
            $table->foreignUuid('yield_unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('scrap_percentage', 5, 2)->default(0.00); // % expected waste/loss
            $table->decimal('labor_cost', 15, 4)->default(0.0000);
            $table->decimal('overhead_cost', 15, 4)->default(0.0000);
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'code'], 'uq_recipes_tenant_code');
            $table->index(['tenant_id', 'product_id', 'is_active'], 'idx_recipes_tenant_product');
        });

        // 3. Recipe Components / Ingredients
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete(); // Raw material
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('waste_percentage', 5, 2)->default(0.00);
            $table->integer('sort_order')->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');

        if (Schema::hasColumn('products', 'shelf_life_days')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('shelf_life_days');
            });
        }
    }
};
