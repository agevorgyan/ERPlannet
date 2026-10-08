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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('tax_id', 100)->nullable(); // HVHH
            $table->string('email', 255)->nullable();
            $table->string('phone', 50);
            $table->string('source', 50)->default('direct'); // direct, web, phone, woocommerce, pos
            $table->jsonb('tags')->default('[]');
            $table->text('notes')->nullable();
            $table->decimal('total_spent', 15, 2)->default(0.00);
            $table->integer('orders_count')->default(0);
            $table->timestampTz('last_ordered_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'phone'], 'uq_customers_tenant_phone');
            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'total_spent']);
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('title', 100)->default('Default'); // Home, Office
            $table->string('city', 100)->default('Yerevan');
            $table->string('address_line_1', 255);
            $table->string('address_line_2', 255)->nullable();
            $table->string('floor', 20)->nullable();
            $table->string('apartment', 20)->nullable();
            $table->string('entry_code', 50)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestampsTz();

            $table->index(['tenant_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
