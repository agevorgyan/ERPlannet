<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Quality Assurance / ISO 22000 Inspections
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignUuid('inspector_id')->constrained('users')->restrictOnDelete();
            $table->string('inspection_number', 50); // e.g. QA-2026-000001
            $table->string('status', 50)->default('passed'); // 'passed', 'deviation_accepted', 'failed'
            $table->string('standard_applied', 100)->default('ISO 22000:2018'); // 'HACCP', 'ISO 22000:2018', 'GMP'
            $table->decimal('overall_score', 5, 2)->nullable(); // e.g. 98.50%
            $table->text('notes')->nullable();
            $table->timestampTz('inspected_at')->useCurrent();
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['tenant_id', 'inspection_number'], 'uq_qa_inspections_number');
            $table->index(['tenant_id', 'production_order_id'], 'idx_qa_inspections_order');
        });

        // 2. Inspection Checkpoints & Parameters
        Schema::create('quality_inspection_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('quality_inspection_id')->constrained('quality_inspections')->cascadeOnDelete();
            $table->string('parameter_name', 150); // Core Temp, Moisture, pH, Visual
            $table->string('critical_control_point', 50)->nullable(); // CCP-1, CCP-2
            $table->string('target_value', 100)->nullable();
            $table->decimal('min_value', 12, 4)->nullable();
            $table->decimal('max_value', 12, 4)->nullable();
            $table->string('actual_value', 100);
            $table->string('unit', 30)->nullable(); // °C, %, pH
            $table->boolean('is_passed')->default(true);
            $table->text('deviation_notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'quality_inspection_id'], 'idx_qa_items_inspection');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_inspection_items');
        Schema::dropIfExists('quality_inspections');
    }
};
