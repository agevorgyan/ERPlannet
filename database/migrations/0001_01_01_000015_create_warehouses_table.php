<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('type', 50)->default('standard'); // 'standard', 'production', 'retail', 'cold_storage'
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->jsonb('settings')->default('{}');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['tenant_id', 'code'], 'uq_warehouses_tenant_code');
            $table->index(['tenant_id', 'is_active'], 'idx_warehouses_tenant_active');
            $table->index(['tenant_id', 'branch_id'], 'idx_warehouses_tenant_branch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
