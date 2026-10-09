<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('address_line_1', 255)->nullable()->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('uq_customers_tenant_phone');
            $table->index(['tenant_id', 'phone'], 'idx_customers_tenant_phone');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_tenant_phone');
            $table->unique(['tenant_id', 'phone'], 'uq_customers_tenant_phone');
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('address_line_1', 255)->nullable(false)->change();
        });
    }
};
