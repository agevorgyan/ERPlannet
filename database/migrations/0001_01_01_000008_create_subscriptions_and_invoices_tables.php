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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('plans')->restrictOnDelete();
            $table->string('status', 50); // trialing, active, past_due, canceled, grace_period
            $table->string('billing_cycle', 20)->default('monthly'); // monthly, yearly
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->timestampTz('trial_ends_at')->nullable();
            $table->timestampTz('canceled_at')->nullable();
            $table->timestampTz('grace_period_ends_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->string('feature_code', 100);
            $table->bigInteger('used_count')->default(0);
            $table->timestampTz('reset_at')->nullable();
            $table->timestampsTz();

            $table->unique(['subscription_id', 'feature_code'], 'uq_sub_feature_usage');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('invoice_number', 100)->unique();
            $table->string('status', 50)->default('open'); // draft, open, paid, void, uncollectible
            $table->string('currency', 3)->default('AMD');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->date('due_date');
            $table->timestampTz('paid_at')->nullable();
            $table->jsonb('billing_details')->default('{}');
            $table->string('pdf_path', 500)->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscription_usages');
        Schema::dropIfExists('subscriptions');
    }
};
