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
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price_monthly', 15, 2)->default(0.00);
            $table->decimal('price_yearly', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('AMD');
            $table->integer('trial_days')->default(14);
            $table->integer('sort_order')->default(0);
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('name');
            $table->string('type', 50)->default('boolean'); // 'boolean', 'limit'
            $table->string('module', 50);
            $table->text('description')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('module');
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->foreignUuid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignUuid('feature_id')->constrained('features')->cascadeOnDelete();
            $table->string('value'); // 'true', 'false', '5', '1000', 'unlimited'
            $table->timestampsTz();

            $table->primary(['plan_id', 'feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('features');
        Schema::dropIfExists('plans');
    }
};
