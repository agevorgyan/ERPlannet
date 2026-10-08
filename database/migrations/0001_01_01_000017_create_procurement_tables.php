<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Suppliers / Vendors
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('company_name', 255);
            $table->string('legal_name', 255)->nullable();
            $table->string('tax_id', 100)->nullable(); // HVHH in Armenia
            $table->string('contact_person', 150)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50);
            $table->text('address')->nullable();
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account', 100)->nullable();
            $table->string('currency', 3)->default('AMD');
            $table->integer('payment_terms_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'is_active'], 'idx_suppliers_tenant_active');
        });

        DB::statement('CREATE UNIQUE INDEX uq_suppliers_tenant_tax ON suppliers (tenant_id, tax_id) WHERE tax_id IS NOT NULL AND deleted_at IS NULL');

        // 2. Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('po_number', 50); // e.g. PO-2026-000001
            $table->foreignUuid('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 50)->default('draft'); // 'draft', 'ordered', 'partial_received', 'received', 'cancelled'
            $table->date('order_date')->useCurrent();
            $table->date('expected_delivery_date')->nullable();
            $table->timestampTz('received_at')->nullable();
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4)->default(0.0000);
            $table->string('currency', 3)->default('AMD');
            $table->string('payment_status', 50)->default('unpaid'); // 'unpaid', 'partially_paid', 'paid'
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'po_number'], 'uq_purchase_orders_number');
            $table->index(['tenant_id', 'supplier_id', 'status'], 'idx_purchase_orders_supplier');
        });

        // 3. Purchase Order Line Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('quantity_ordered', 15, 4);
            $table->decimal('quantity_received', 15, 4)->default(0.0000);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('total', 15, 4);
            $table->timestampsTz();

            $table->index(['tenant_id', 'purchase_order_id'], 'idx_po_items_po');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
