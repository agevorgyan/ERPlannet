<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('warehouse_id')->nullable()->after('branch_id')->constrained('warehouses')->nullOnDelete();
            $table->string('order_type', 50)->default('standard')->after('source'); // standard, preliminary, b2b, catering
            $table->string('external_reference', 100)->nullable()->after('receipt_number');
            $table->string('preparation_status', 50)->default('pending')->after('status'); // pending, preparing, ready, fulfilled
            $table->string('delivery_status', 50)->default('unassigned')->after('preparation_status'); // unassigned, assigned, in_transit, delivered, failed
            $table->string('fiscal_status', 50)->default('not_fiscalized')->after('delivery_status'); // not_fiscalized, fiscalized, failed, refunded
            $table->timestampTz('confirmed_at')->nullable()->after('placed_at');
            $table->timestampTz('completed_at')->nullable()->after('delivered_at');
            $table->jsonb('customer_snapshot')->nullable()->after('customer_address_id');
            $table->foreignUuid('responsible_employee_id')->nullable()->after('pos_session_id')->constrained('users')->nullOnDelete();
            $table->decimal('item_discounts_total', 15, 2)->default(0.00)->after('subtotal');
            $table->decimal('order_discount', 15, 2)->default(0.00)->after('item_discounts_total');
            $table->string('promo_code', 50)->nullable()->after('discount');
            $table->decimal('promo_discount', 15, 2)->default(0.00)->after('promo_code');
            $table->decimal('paid_amount', 15, 2)->default(0.00)->after('total');
            $table->decimal('balance_due', 15, 2)->default(0.00)->after('paid_amount');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->jsonb('metadata')->nullable()->after('internal_notes');

            $table->index(['tenant_id', 'order_type'], 'idx_orders_tenant_type');
            $table->index(['tenant_id', 'preparation_status'], 'idx_orders_tenant_prep');
            $table->index(['tenant_id', 'fiscal_status'], 'idx_orders_tenant_fiscal');
            $table->index(['tenant_id', 'external_reference'], 'idx_orders_tenant_ext_ref');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignUuid('unit_id')->nullable()->after('variant_id')->constrained('units')->nullOnDelete();
            $table->string('unit_name', 50)->nullable()->after('unit_id');
            $table->decimal('original_price', 15, 2)->nullable()->after('unit_price');
            $table->string('discount_type', 20)->default('fixed')->after('discount'); // fixed, percent
            $table->decimal('discount_rate', 5, 2)->default(0.00)->after('discount_type');
            $table->decimal('tax_rate', 5, 2)->default(0.00)->after('discount_rate');
            $table->decimal('tax_amount', 15, 2)->default(0.00)->after('tax_rate');
            $table->decimal('subtotal', 15, 2)->default(0.00)->after('tax_amount');
            $table->decimal('unit_cost', 15, 2)->default(0.00)->after('subtotal');
            $table->jsonb('options_snapshot')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn([
                'unit_id',
                'unit_name',
                'original_price',
                'discount_type',
                'discount_rate',
                'tax_rate',
                'tax_amount',
                'subtotal',
                'unit_cost',
                'options_snapshot',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_tenant_type');
            $table->dropIndex('idx_orders_tenant_prep');
            $table->dropIndex('idx_orders_tenant_fiscal');
            $table->dropIndex('idx_orders_tenant_ext_ref');

            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['responsible_employee_id']);
            $table->dropColumn([
                'warehouse_id',
                'order_type',
                'external_reference',
                'preparation_status',
                'delivery_status',
                'fiscal_status',
                'confirmed_at',
                'completed_at',
                'customer_snapshot',
                'responsible_employee_id',
                'item_discounts_total',
                'order_discount',
                'promo_code',
                'promo_discount',
                'paid_amount',
                'balance_due',
                'cancellation_reason',
                'metadata',
            ]);
        });
    }
};
