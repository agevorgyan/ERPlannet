<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. POS Terminals
        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('device_uid', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['tenant_id', 'code'], 'uq_pos_terminals_code');
            $table->index(['tenant_id', 'branch_id'], 'idx_pos_terminals_branch');
        });

        // 2. POS Shift Sessions
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('pos_terminal_id')->constrained('pos_terminals')->cascadeOnDelete();
            $table->foreignUuid('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('session_number', 50);
            $table->decimal('opening_cash', 12, 2)->default(0.00);
            $table->decimal('closing_cash_declared', 12, 2)->nullable();
            $table->decimal('closing_cash_calculated', 12, 2)->default(0.00);
            $table->decimal('cash_difference', 12, 2)->nullable();
            $table->string('status', 30)->default('open'); // open, closed
            $table->timestampTz('opened_at')->useCurrent();
            $table->timestampTz('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'session_number'], 'uq_pos_sessions_number');
            $table->index(['tenant_id', 'pos_terminal_id', 'status'], 'idx_pos_sessions_terminal_status');
        });

        // 3. POS Cash Movements (Cash In / Out)
        Schema::create('pos_cash_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('pos_session_id')->constrained('pos_sessions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 30); // cash_in, cash_out
            $table->decimal('amount', 12, 2);
            $table->string('reason', 255);
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'pos_session_id'], 'idx_pos_cash_session');
        });

        // 4. Update orders table to link POS session and receipt number
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('pos_terminal_id')->nullable()->constrained('pos_terminals')->nullOnDelete();
            $table->foreignUuid('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->string('receipt_number', 50)->nullable();

            $table->index(['tenant_id', 'pos_session_id'], 'idx_orders_pos_session');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pos_terminal_id']);
            $table->dropForeign(['pos_session_id']);
            $table->dropColumn(['pos_terminal_id', 'pos_session_id', 'receipt_number']);
        });

        Schema::dropIfExists('pos_cash_movements');
        Schema::dropIfExists('pos_sessions');
        Schema::dropIfExists('pos_terminals');
    }
};
