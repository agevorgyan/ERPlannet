<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. POS Workstations / Terminals hardware bridge
        Schema::create('pos_workstations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('code', 50);
            $table->string('identifier', 100); // Browser fingerprint or hardware ID
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['tenant_id', 'code'], 'uq_workstations_tenant_code');
            $table->index(['tenant_id', 'branch_id'], 'idx_workstations_tenant_branch');
        });

        // 2. Printers Configuration
        Schema::create('printers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('workstation_id')->nullable()->constrained('pos_workstations')->nullOnDelete();
            $table->string('name', 100);
            $table->string('printer_type', 30)->default('thermal'); // thermal, office, virtual
            $table->string('connection_type', 30)->default('browser'); // browser, escpos_network, escpos_usb_bridge, system_pdf
            $table->string('interface_type', 30)->default('browser');
            $table->string('protocol', 30)->default('esc_pos');
            $table->string('ip_address', 50)->nullable();
            $table->integer('port')->default(9100);
            $table->string('paper_width', 20)->default('80mm'); // 58mm, 80mm, a4, a5
            $table->string('character_set', 30)->default('UTF-8');
            $table->boolean('is_default')->default(false);
            $table->boolean('supports_cash_drawer')->default(false);
            $table->boolean('supports_cutter')->default(true);
            $table->string('status', 30)->default('online'); // online, offline, error
            $table->jsonb('settings')->nullable(); // margins, density, copies, triggers
            $table->timestampsTz();

            $table->index(['tenant_id', 'branch_id'], 'idx_printers_tenant_branch');
            $table->index(['tenant_id', 'is_default'], 'idx_printers_tenant_default');
        });

        // 3. Document Templates (Visual Designer)
        Schema::create('document_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100)->nullable();
            $table->string('code', 100)->nullable();
            $table->string('document_type', 50); // pos_receipt, order_receipt, kitchen_ticket, delivery_document, b2b_delivery_note, order_confirmation, payment_receipt, refund_receipt, a4_document
            $table->string('paper_size', 20)->default('80mm'); // 58mm, 80mm, a4, a5
            $table->string('paper_width', 20)->default('80mm');
            $table->text('content')->nullable();
            $table->jsonb('config')->nullable();
            $table->integer('active_version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->jsonb('layout_config')->nullable(); // Visual designer block schema, fonts, widths, sections, fields, placeholders
            $table->jsonb('sample_data')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'slug'], 'uq_doc_templates_tenant_slug');
            $table->index(['tenant_id', 'document_type'], 'idx_doc_templates_tenant_type');
        });

        // 4. Document Template Versions
        Schema::create('document_template_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('document_template_id')->constrained('document_templates')->cascadeOnDelete();
            $table->integer('version_number')->default(1);
            $table->integer('version')->default(1);
            $table->text('content')->nullable();
            $table->jsonb('config')->nullable();
            $table->jsonb('layout_config')->nullable();
            $table->foreignUuid('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('changelog', 255)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['document_template_id', 'version_number'], 'idx_template_versions_num');
            $table->index(['tenant_id', 'document_template_id'], 'idx_template_versions_template');
        });

        // 5. Print Jobs Queue
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('printer_id')->nullable()->constrained('printers')->nullOnDelete();
            $table->foreignUuid('workstation_id')->nullable()->constrained('pos_workstations')->nullOnDelete();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('document_type', 50);
            $table->foreignUuid('document_template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->foreignUuid('template_version_id')->nullable()->constrained('document_template_versions')->nullOnDelete();
            $table->integer('copies')->default(1);
            $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('queued'); // queued, processing, completed, failed, cancelled
            $table->string('idempotency_key', 255)->nullable();
            $table->boolean('is_reprint')->default(false);
            $table->integer('reprint_count')->default(0);
            $table->text('error_message')->nullable();
            $table->mediumText('payload_rendered')->nullable(); // HTML or ESC/POS payload
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'status'], 'idx_print_jobs_tenant_status');
            $table->index(['tenant_id', 'order_id'], 'idx_print_jobs_tenant_order');
            $table->index(['tenant_id', 'idempotency_key'], 'idx_print_jobs_tenant_idempotency');
        });

        // 6. Print Job Events
        Schema::create('print_job_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('print_job_id')->constrained('print_jobs')->cascadeOnDelete();
            $table->string('event_type', 50); // created, dispatched, printed, failed, reprinted
            $table->text('message')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'print_job_id'], 'idx_print_events_job');
        });

        // 7. B2B Delivery Notes (Накладная / Բեռնագիր)
        Schema::create('b2b_delivery_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('document_number', 50); // DN-2026-000001
            $table->date('document_date');
            $table->string('supplier_name', 255);
            $table->string('supplier_tax_id', 50);
            $table->text('supplier_address')->nullable();
            $table->string('customer_name', 255);
            $table->string('customer_tax_id', 50)->nullable();
            $table->text('customer_address')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->jsonb('items_snapshot'); // Complete immutable lines snapshot
            $table->string('delivered_by_name', 150)->nullable();
            $table->string('received_by_name', 150)->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->integer('reprint_count')->default(0);
            $table->string('status', 30)->default('issued'); // issued, cancelled, amended
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'document_number'], 'uq_b2b_delivery_notes_tenant_num');
            $table->index(['tenant_id', 'order_id'], 'idx_b2b_delivery_notes_order');
            $table->index(['tenant_id', 'status'], 'idx_b2b_delivery_notes_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_delivery_notes');
        Schema::dropIfExists('print_job_events');
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('document_template_versions');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('printers');
        Schema::dropIfExists('pos_workstations');
    }
};
