<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Idempotency Keys table for duplicate request prevention
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('idempotency_key', 255);
            $table->string('resource_type', 100);
            $table->uuid('resource_id')->nullable();
            $table->jsonb('response_payload')->nullable();
            $table->integer('status_code')->default(200);
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['tenant_id', 'idempotency_key'], 'uq_idempotency_tenant_key');
            $table->index(['tenant_id', 'resource_type'], 'idx_idempotency_tenant_res');
        });

        // 2. POS Z-Reports (End-of-Day Shift Close Reports)
        Schema::create('pos_z_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('pos_session_id')->constrained('pos_sessions')->cascadeOnDelete();
            $table->foreignUuid('pos_terminal_id')->constrained('pos_terminals')->cascadeOnDelete();
            $table->foreignUuid('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('z_report_number', 50);
            $table->timestampTz('opened_at');
            $table->timestampTz('closed_at');
            $table->decimal('opening_cash', 12, 2)->default(0.00);
            $table->decimal('total_sales_amount', 12, 2)->default(0.00);
            $table->decimal('total_cash_sales', 12, 2)->default(0.00);
            $table->decimal('total_card_sales', 12, 2)->default(0.00);
            $table->decimal('total_other_sales', 12, 2)->default(0.00);
            $table->decimal('total_tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_refunds_amount', 12, 2)->default(0.00);
            $table->decimal('total_discounts_amount', 12, 2)->default(0.00);
            $table->decimal('cash_in_amount', 12, 2)->default(0.00);
            $table->decimal('cash_out_amount', 12, 2)->default(0.00);
            $table->decimal('expected_cash_in_drawer', 12, 2)->default(0.00);
            $table->decimal('closing_cash_declared', 12, 2)->default(0.00);
            $table->decimal('cash_difference', 12, 2)->default(0.00);
            $table->integer('sales_count')->default(0);
            $table->integer('refunds_count')->default(0);
            $table->integer('void_count')->default(0);
            $table->jsonb('tax_breakdown')->nullable();
            $table->jsonb('payments_breakdown')->nullable();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'z_report_number'], 'uq_pos_z_reports_number');
            $table->index(['tenant_id', 'pos_session_id'], 'idx_pos_z_reports_session');
            $table->index(['tenant_id', 'pos_terminal_id'], 'idx_pos_z_reports_terminal');
        });

        // 3. POS Returns & Refunds
        Schema::create('pos_refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->foreignUuid('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('refund_number', 50);
            $table->decimal('amount', 12, 2);
            $table->string('refund_method', 30)->default('cash'); // cash, card, store_credit
            $table->string('reason', 255);
            $table->string('status', 30)->default('completed'); // completed, cancelled
            $table->jsonb('items_payload')->nullable();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'refund_number'], 'uq_pos_refunds_number');
            $table->index(['tenant_id', 'order_id'], 'idx_pos_refunds_order');
        });

        // 4. Fiscal Receipts (Decoupled from internal receipts)
        Schema::create('fiscal_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->string('provider', 50)->default('mock_armenia_src');
            $table->string('fiscal_number', 100);
            $table->string('crn', 50); // Cash Register Number (ՀԴՄ գործարանային համար)
            $table->string('status', 30)->default('registered'); // registered, cancelled, refunded, failed
            $table->decimal('total_amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->text('qr_payload')->nullable();
            $table->jsonb('provider_response')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'order_id'], 'idx_fiscal_receipts_order');
            $table->index(['tenant_id', 'fiscal_number'], 'idx_fiscal_receipts_number');
        });

        // 5. COD Settlements & Lifecycle
        Schema::create('cod_settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('delivery_shipment_id')->constrained('delivery_shipments')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('delivery_driver_id')->nullable()->constrained('delivery_drivers')->nullOnDelete();
            $table->foreignUuid('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('expected'); // expected, collected, courier_holding, submitted, verified, settled
            $table->decimal('expected_amount', 12, 2)->default(0.00);
            $table->decimal('collected_amount', 12, 2)->default(0.00);
            $table->decimal('submitted_amount', 12, 2)->nullable();
            $table->decimal('verified_amount', 12, 2)->nullable();
            $table->decimal('discrepancy_amount', 12, 2)->default(0.00);
            $table->text('discrepancy_reason')->nullable();
            $table->timestampTz('settled_at')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'delivery_shipment_id'], 'idx_cod_settlements_shipment');
            $table->index(['tenant_id', 'status'], 'idx_cod_settlements_status');
        });

        // 6. Delivery Driver Shifts
        Schema::create('delivery_driver_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('delivery_driver_id')->constrained('delivery_drivers')->cascadeOnDelete();
            $table->timestampTz('shift_start')->useCurrent();
            $table->timestampTz('shift_end')->nullable();
            $table->string('status', 30)->default('active'); // active, completed
            $table->string('vehicle_type', 50)->default('car');
            $table->string('vehicle_license_plate', 50)->nullable();
            $table->decimal('starting_odometer', 10, 2)->nullable();
            $table->decimal('ending_odometer', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'delivery_driver_id', 'status'], 'idx_driver_shifts_status');
        });

        // 7. Delivery Routes and Stops
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('delivery_driver_id')->nullable()->constrained('delivery_drivers')->nullOnDelete();
            $table->string('route_number', 50);
            $table->date('date');
            $table->string('status', 30)->default('planned'); // planned, in_progress, completed, cancelled
            $table->integer('total_stops')->default(0);
            $table->integer('completed_stops')->default(0);
            $table->timestampsTz();

            $table->unique(['tenant_id', 'route_number'], 'uq_delivery_routes_number');
            $table->index(['tenant_id', 'date', 'status'], 'idx_delivery_routes_date_status');
        });

        Schema::create('delivery_route_stops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('delivery_route_id')->constrained('delivery_routes')->cascadeOnDelete();
            $table->foreignUuid('delivery_shipment_id')->constrained('delivery_shipments')->cascadeOnDelete();
            $table->integer('stop_sequence')->default(1);
            $table->string('status', 30)->default('pending'); // pending, arrived, completed, failed
            $table->timestampTz('estimated_arrival_at')->nullable();
            $table->timestampTz('actual_arrival_at')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'delivery_route_id', 'stop_sequence'], 'idx_route_stops_sequence');
        });

        // 8. Delivery Events (Audit log of state machine transitions & GPS events)
        Schema::create('delivery_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('delivery_shipment_id')->nullable()->constrained('delivery_shipments')->cascadeOnDelete();
            $table->foreignUuid('delivery_driver_id')->nullable()->constrained('delivery_drivers')->nullOnDelete();
            $table->string('event_type', 50); // assigned, dispatched, in_transit, arrived, delivered, failed, returned, gps_ping
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('speed', 6, 2)->nullable();
            $table->integer('battery_level')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'delivery_shipment_id'], 'idx_delivery_events_shipment');
            $table->index(['tenant_id', 'created_at'], 'idx_delivery_events_created');
        });

        // 9. Extra columns for shipment failure, return & idempotency
        Schema::table('delivery_shipments', function (Blueprint $table) {
            $table->text('failure_reason')->nullable();
            $table->timestampTz('returned_at')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('idempotency_key', 255)->nullable();
            $table->index(['tenant_id', 'idempotency_key'], 'idx_orders_idempotency');
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('idempotency_key', 255)->nullable();
            $table->decimal('refunded_amount', 12, 2)->default(0.00);
            $table->timestampTz('webhook_received_at')->nullable();
            $table->index(['tenant_id', 'idempotency_key'], 'idx_payment_transactions_idempotency');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_payment_transactions_idempotency');
            $table->dropColumn(['idempotency_key', 'refunded_amount', 'webhook_received_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_idempotency');
            $table->dropColumn('idempotency_key');
        });

        Schema::table('delivery_shipments', function (Blueprint $table) {
            $table->dropColumn(['failure_reason', 'returned_at']);
        });

        Schema::dropIfExists('delivery_events');
        Schema::dropIfExists('delivery_route_stops');
        Schema::dropIfExists('delivery_routes');
        Schema::dropIfExists('delivery_driver_shifts');
        Schema::dropIfExists('cod_settlements');
        Schema::dropIfExists('fiscal_receipts');
        Schema::dropIfExists('pos_refunds');
        Schema::dropIfExists('pos_z_reports');
        Schema::dropIfExists('idempotency_keys');
    }
};
