<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xml_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name', 255);
            $table->unsignedBigInteger('file_size');
            $table->string('checksum', 64);
            $table->string('document_type', 50)->default('customer_order'); // customer_order, supplier_invoice, catalog_feed
            $table->string('format_detected', 50)->default('erplannet_xml'); // erplannet_xml, armenia_e_invoicing, generic_order_xml
            $table->string('status', 30)->default('pending'); // pending, previewed, imported, failed
            $table->boolean('dry_run')->default(false);
            $table->integer('total_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->string('external_document_number', 100)->nullable();
            $table->date('external_document_date')->nullable();
            $table->jsonb('errors')->nullable();
            $table->jsonb('preview_payload')->nullable();
            $table->jsonb('created_orders_ids')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'checksum'], 'idx_xml_imports_tenant_checksum');
            $table->index(['tenant_id', 'status'], 'idx_xml_imports_tenant_status');
            $table->index(['tenant_id', 'created_at'], 'idx_xml_imports_tenant_created');
        });

        Schema::create('xml_import_errors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('xml_import_id')->constrained('xml_imports')->cascadeOnDelete();
            $table->integer('line_number')->nullable();
            $table->string('record_identifier', 100)->nullable();
            $table->string('error_code', 50)->default('VALIDATION_ERROR');
            $table->text('error_message');
            $table->text('raw_snippet')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'xml_import_id'], 'idx_xml_import_errors_tenant_import');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xml_import_errors');
        Schema::dropIfExists('xml_imports');
    }
};
