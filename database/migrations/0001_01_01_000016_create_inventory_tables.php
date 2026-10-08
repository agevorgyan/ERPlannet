<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add has_batches to products table
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_batches')->default(false)->after('track_stock');
        });

        // 2. Stock Levels (Materialized current balance per warehouse & product)
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('quantity_on_hand', 15, 4)->default(0.0000);
            $table->decimal('quantity_reserved', 15, 4)->default(0.0000);
            $table->decimal('quantity_available', 15, 4)->storedAs('quantity_on_hand - quantity_reserved');
            $table->decimal('reorder_point', 15, 4)->default(0.0000);
            $table->decimal('ideal_stock', 15, 4)->default(0.0000);
            $table->timestampTz('updated_at')->useCurrent();

            $table->index(['tenant_id', 'warehouse_id', 'product_id'], 'idx_stock_levels_query');
        });

        DB::statement('CREATE UNIQUE INDEX uq_stock_levels_no_variant ON stock_levels (tenant_id, warehouse_id, product_id) WHERE product_variant_id IS NULL');
        DB::statement('CREATE UNIQUE INDEX uq_stock_levels_with_variant ON stock_levels (tenant_id, warehouse_id, product_id, product_variant_id) WHERE product_variant_id IS NOT NULL');

        // 3. Stock Batches / Lots (Lot tracking, expiration dates, valuation)
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->string('batch_number', 100);
            $table->decimal('quantity_on_hand', 15, 4)->default(0.0000);
            $table->decimal('quantity_reserved', 15, 4)->default(0.0000);
            $table->decimal('cost_price', 15, 4)->default(0.0000);
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status', 50)->default('active'); // 'active', 'quarantine', 'expired', 'exhausted'
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'expiry_date', 'status'], 'idx_stock_batches_expiry');
        });

        DB::statement('CREATE UNIQUE INDEX uq_stock_batches_no_variant ON stock_batches (tenant_id, warehouse_id, product_id, batch_number) WHERE product_variant_id IS NULL');
        DB::statement('CREATE UNIQUE INDEX uq_stock_batches_with_variant ON stock_batches (tenant_id, warehouse_id, product_id, product_variant_id, batch_number) WHERE product_variant_id IS NOT NULL');

        // 4. Stock Movements (Immutable Double-Entry Ledger)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignUuid('stock_batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50); // 'purchase_receipt', 'sale_delivery', 'transfer_out', 'transfer_in', 'adjustment_plus', 'adjustment_minus', 'scrap', 'production_consume', 'production_yield'
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->default(0.0000);
            $table->decimal('balance_before', 15, 4);
            $table->decimal('balance_after', 15, 4);
            $table->string('reference_type', 150)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'warehouse_id', 'product_id', 'created_at'], 'idx_stock_movements_history');
            $table->index(['tenant_id', 'reference_type', 'reference_id'], 'idx_stock_movements_reference');
        });

        // 5. Stock Transfers (Warehouse A -> Warehouse B)
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('transfer_number', 50);
            $table->foreignUuid('source_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignUuid('destination_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('status', 50)->default('draft'); // 'draft', 'in_transit', 'completed', 'cancelled'
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestampTz('shipped_at')->nullable();
            $table->timestampTz('received_at')->nullable();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'transfer_number'], 'uq_stock_transfers_number');
        });

        // 6. Stock Transfer Items
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignUuid('stock_batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('stock_levels');

        if (Schema::hasColumn('products', 'has_batches')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('has_batches');
            });
        }
    }
};
