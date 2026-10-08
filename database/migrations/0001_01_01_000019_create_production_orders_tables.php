<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Production Orders (Work Orders)
        Schema::create('production_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('order_number', 50); // e.g. PRD-2026-000001
            $table->foreignUuid('recipe_id')->constrained('recipes')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignUuid('source_warehouse_id')->constrained('warehouses')->restrictOnDelete(); // Raw materials warehouse
            $table->foreignUuid('target_warehouse_id')->constrained('warehouses')->restrictOnDelete(); // Finished goods warehouse
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 50)->default('draft'); // 'draft', 'confirmed', 'in_progress', 'quality_check', 'completed', 'rejected', 'cancelled'
            $table->decimal('planned_quantity', 15, 4);
            $table->decimal('actual_quantity', 15, 4)->default(0.0000);
            $table->decimal('waste_quantity', 15, 4)->default(0.0000);
            $table->decimal('unit_cost', 15, 4)->default(0.0000);
            $table->decimal('total_cost', 15, 4)->default(0.0000);
            $table->foreignUuid('stock_batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            $table->date('planned_start_date')->useCurrent();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'order_number'], 'uq_production_orders_number');
            $table->index(['tenant_id', 'status'], 'idx_production_orders_status');
            $table->index(['tenant_id', 'recipe_id'], 'idx_production_orders_recipe');
        });

        // 2. Production Order Line Items (Consumed ingredients)
        Schema::create('production_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete(); // Ingredient
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('planned_quantity', 15, 4);
            $table->decimal('consumed_quantity', 15, 4)->default(0.0000);
            $table->decimal('unit_cost', 15, 4)->default(0.0000);
            $table->decimal('total_cost', 15, 4)->default(0.0000);
            $table->foreignUuid('stock_batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'production_order_id'], 'idx_prd_items_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_items');
        Schema::dropIfExists('production_orders');
    }
};
