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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('customer_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->string('order_number', 50); // ORD-2026-000001
            $table->string('status', 50)->default('new'); // new, confirmed, processing, packed, delivery, delivered, cancelled
            $table->string('source', 50)->default('direct'); // direct, phone, web, pos, woocommerce
            $table->string('delivery_type', 50)->default('delivery'); // delivery, pickup, dine_in
            $table->string('currency', 3)->default('AMD');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('delivery_fee', 15, 2)->default(0.00);
            $table->decimal('tax', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->string('payment_status', 50)->default('unpaid'); // unpaid, partially_paid, paid, refunded
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestampTz('placed_at')->useCurrent();
            $table->timestampTz('scheduled_for')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'order_number'], 'uq_orders_tenant_number');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'placed_at']);
            $table->index(['tenant_id', 'customer_id']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name', 255); // Snapshot at purchase time
            $table->string('product_sku', 100);
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2);
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'order_id']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->text('comment')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'order_id', 'created_at']);
        });

        // Add order_id to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignUuid('order_id')->nullable()->after('invoice_id')->constrained('orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });

        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
