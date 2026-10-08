<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignUuid('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->string('gateway', 50); // idram, telcell, ameria, cash, stripe, bank_transfer
            $table->string('payment_method', 50)->default('card'); // cash, card, qr, transfer
            $table->string('transaction_id', 150);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('AMD');
            $table->string('status', 30)->default('pending'); // pending, successful, failed, refunded
            $table->jsonb('payer_details')->nullable();
            $table->jsonb('gateway_response')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'order_id'], 'idx_payment_transactions_order');
            $table->index(['tenant_id', 'transaction_id'], 'idx_payment_transactions_tx_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
