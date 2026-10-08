<?php

declare(strict_types=1);

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
        // 1. Tenant Integrations Registry
        Schema::create('tenant_integrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('provider', 60); // 'woocommerce', 'telegram', 'nikita_sms', 'mobipace_sms', 'resend', 'custom_webhook'
            $table->string('name', 150);
            $table->string('status', 30)->default('inactive'); // 'active', 'inactive', 'error', 'syncing'
            $table->text('credentials_encrypted'); // Encrypted JSON payload
            $table->jsonb('settings')->default('{}'); // Driver-specific options (e.g. default_warehouse_id, auto_stock_sync, order_prefix)
            $table->timestampTz('last_sync_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'provider', 'name'], 'uq_tenant_provider_name');
            $table->index(['tenant_id', 'status'], 'idx_tenant_integrations_status');
        });

        // 2. Cross-System Entity Mapping (ERP internal ID <-> External ID)
        Schema::create('tenant_integration_entity_maps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('integration_id')->constrained('tenant_integrations')->cascadeOnDelete();
            $table->string('entity_type', 60); // 'product', 'customer', 'order', 'category'
            $table->uuid('internal_id');
            $table->string('external_id', 120);
            $table->string('checksum', 64)->nullable(); // SHA-256 hash of synced payload to prevent ping-pong loop
            $table->timestampTz('last_synced_at')->useCurrent();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'integration_id', 'entity_type', 'internal_id'], 'uq_entity_map_internal');
            $table->unique(['tenant_id', 'integration_id', 'entity_type', 'external_id'], 'uq_entity_map_external');
            $table->index(['tenant_id', 'integration_id', 'entity_type'], 'idx_entity_map_lookup');
        });

        // 3. Integration Sync Execution Logs
        Schema::create('tenant_integration_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('integration_id')->constrained('tenant_integrations')->cascadeOnDelete();
            $table->string('entity_type', 60);
            $table->string('direction', 20); // 'inbound', 'outbound'
            $table->string('status', 30); // 'success', 'failed', 'partial'
            $table->integer('records_processed')->default(0);
            $table->integer('records_failed')->default(0);
            $table->jsonb('details')->default('{}');
            $table->integer('duration_ms')->default(0);
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'integration_id', 'created_at'], 'idx_sync_logs_timeline');
        });

        // 4. Outbound Webhook Subscriptions
        Schema::create('tenant_webhook_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('target_url', 500);
            $table->text('secret_encrypted');
            $table->jsonb('events')->default('[]'); // array of event names: e.g. ['order.created', 'order.status_updated']
            $table->boolean('is_active')->default(true);
            $table->jsonb('headers')->default('{}');
            $table->integer('retry_count_max')->default(5);
            $table->timestampsTz();

            $table->index(['tenant_id', 'is_active'], 'idx_webhook_subs_active');
        });

        // 5. Outbound Webhook Delivery Records
        Schema::create('tenant_webhook_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->constrained('tenant_webhook_subscriptions')->cascadeOnDelete();
            $table->string('event_name', 100);
            $table->jsonb('payload');
            $table->string('status', 30)->default('pending'); // 'pending', 'delivered', 'failed'
            $table->integer('attempts')->default(0);
            $table->integer('response_status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->timestampTz('last_attempt_at')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'status', 'created_at'], 'idx_webhook_deliveries_status');
        });

        // 6. Notification Templates (SMS, Telegram, Email)
        Schema::create('tenant_notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('channel', 30); // 'sms', 'telegram', 'email'
            $table->string('event', 60); // 'order_created', 'order_dispatched', 'pos_eod_report', 'low_stock_warning'
            $table->text('template_body');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['tenant_id', 'channel', 'event'], 'uq_tenant_channel_event');
            $table->index(['tenant_id', 'channel', 'is_active'], 'idx_notif_templates_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_notification_templates');
        Schema::dropIfExists('tenant_webhook_deliveries');
        Schema::dropIfExists('tenant_webhook_subscriptions');
        Schema::dropIfExists('tenant_integration_sync_logs');
        Schema::dropIfExists('tenant_integration_entity_maps');
        Schema::dropIfExists('tenant_integrations');
    }
};
