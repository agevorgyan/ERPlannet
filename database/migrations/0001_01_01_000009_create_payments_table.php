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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('gateway', 50); // 'ameriabank', 'idram', 'stripe', 'bank_transfer', 'cash'
            $table->string('transaction_id', 255)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('AMD');
            $table->string('status', 50)->default('pending'); // 'pending', 'successful', 'failed', 'refunded'
            $table->jsonb('gateway_response')->default('{}');
            $table->jsonb('payment_method_details')->default('{}');
            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('refunded_at')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'gateway', 'status']);
            $table->index(['gateway', 'transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
