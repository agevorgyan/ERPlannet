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
        // 1. Customer Acquisition Sources Catalog
        if (! Schema::hasTable('customer_sources')) {
            Schema::create('customer_sources', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('code', 50);
                $table->jsonb('name'); // localized: {"hy": "...", "en": "...", "ru": "..."}
                $table->string('icon', 50)->default('fa-solid fa-bullhorn');
                $table->string('color', 30)->default('#4F46E5');
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestampsTz();

                $table->unique(['tenant_id', 'code'], 'uq_sources_tenant_code');
            });
        }

        // 2. Expand customers table with CRM and Segmentation Architecture
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_code', 50)->nullable()->after('tenant_id');
            $table->string('type', 20)->default('individual')->after('customer_code'); // individual, company
            $table->string('status', 20)->default('active')->after('type'); // active, inactive, blocked, archived
            $table->foreignUuid('primary_branch_id')->nullable()->after('status')->constrained('branches')->nullOnDelete();
            $table->foreignUuid('last_order_branch_id')->nullable()->after('primary_branch_id')->constrained('branches')->nullOnDelete();
            $table->foreignUuid('created_by_user_id')->nullable()->after('last_order_branch_id')->constrained('users')->nullOnDelete();
            $table->foreignUuid('assigned_manager_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
            $table->foreignUuid('acquisition_source_id')->nullable()->after('assigned_manager_id')->constrained('customer_sources')->nullOnDelete();
            $table->foreignUuid('last_order_source_id')->nullable()->after('acquisition_source_id')->constrained('customer_sources')->nullOnDelete();

            $table->string('primary_phone', 50)->nullable()->after('phone');
            $table->string('primary_email', 255)->nullable()->after('email');
            $table->string('display_name', 255)->nullable()->after('company_name');

            $table->integer('canceled_orders_count')->default(0)->after('orders_count');
            $table->integer('returned_orders_count')->default(0)->after('canceled_orders_count');
            $table->decimal('average_order_value', 15, 2)->default(0.00)->after('total_spent');
            $table->decimal('lifetime_value', 15, 2)->default(0.00)->after('average_order_value');
            $table->timestampTz('first_ordered_at')->nullable()->after('last_ordered_at');
            $table->decimal('average_order_frequency_days', 8, 2)->default(0.00)->after('first_ordered_at');
            $table->integer('customer_score')->default(50)->after('average_order_frequency_days'); // 0-100 RFM score

            $table->string('loyalty_tier', 50)->default('basic')->after('customer_score'); // basic, bronze, silver, gold, vip
            $table->decimal('custom_discount_percent', 5, 2)->default(0.00)->after('loyalty_tier');

            $table->boolean('marketing_sms_consent')->default(false)->after('notes');
            $table->boolean('marketing_email_consent')->default(false)->after('marketing_sms_consent');
            $table->boolean('marketing_calls_consent')->default(false)->after('marketing_email_consent');
            $table->timestampTz('consent_recorded_at')->nullable()->after('marketing_calls_consent');

            $table->index(['tenant_id', 'customer_code'], 'idx_customers_code');
            $table->index(['tenant_id', 'type'], 'idx_customers_type');
            $table->index(['tenant_id', 'status'], 'idx_customers_status');
        });

        // 3. Customer Individuals Profile (B2C)
        if (! Schema::hasTable('customer_individuals')) {
            Schema::create('customer_individuals', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('first_name', 100);
                $table->string('last_name', 100)->nullable();
                $table->string('middle_name', 100)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('gender', 20)->nullable(); // male, female, other
                $table->string('preferred_language', 10)->default('hy');
                $table->timestampsTz();

                $table->unique(['tenant_id', 'customer_id'], 'uq_indiv_tenant_customer');
            });
        }

        // 4. Customer Companies Profile (B2B, HoReCa, Retail)
        if (! Schema::hasTable('customer_companies')) {
            Schema::create('customer_companies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('legal_name', 255);
                $table->string('trade_name', 255)->nullable();
                $table->string('tax_id', 100)->nullable(); // HVHH
                $table->string('registration_country', 10)->default('AM');
                $table->string('legal_address', 255)->nullable();
                $table->string('physical_address', 255)->nullable();
                $table->string('website', 255)->nullable();
                $table->string('director_name', 150)->nullable();
                $table->string('accountant_name', 150)->nullable();
                $table->string('purchasing_manager_name', 150)->nullable();
                $table->string('contract_number', 100)->nullable();
                $table->date('contract_date')->nullable();
                $table->decimal('credit_limit', 15, 2)->default(0.00);
                $table->decimal('outstanding_balance', 15, 2)->default(0.00);
                $table->integer('payment_terms_days')->default(0);
                $table->boolean('is_postpaid_allowed')->default(false);
                $table->jsonb('bank_account_details')->nullable();
                $table->timestampsTz();

                $table->unique(['tenant_id', 'customer_id'], 'uq_company_tenant_customer');
                $table->index(['tenant_id', 'tax_id'], 'idx_company_tax_id');
            });
        }

        // 5. Customer Contacts (Multiple contact persons for B2B/B2C)
        if (! Schema::hasTable('customer_contacts')) {
            Schema::create('customer_contacts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('name', 150);
                $table->string('position', 100)->nullable();
                $table->string('phone', 50);
                $table->string('email', 255)->nullable();
                $table->boolean('is_primary')->default(false);
                $table->text('notes')->nullable();
                $table->timestampsTz();

                $table->index(['tenant_id', 'customer_id']);
            });
        }

        // 6. Expand customer_addresses table
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('country', 10)->default('AM')->after('title');
            $table->string('province', 100)->default('Երևան')->after('country');
            $table->string('postal_code', 20)->nullable()->after('city');
            $table->string('street', 200)->nullable()->after('postal_code');
            $table->string('building', 50)->nullable()->after('street');
            $table->string('entrance', 20)->nullable()->after('building');
            $table->string('door_code', 50)->nullable()->after('apartment');
            $table->text('delivery_instructions')->nullable()->after('door_code');
            $table->boolean('is_last_used')->default(false)->after('is_default');
        });

        // 7. Customer Loyalty Accounts
        if (! Schema::hasTable('customer_loyalty_accounts')) {
            Schema::create('customer_loyalty_accounts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('card_number', 50);
                $table->string('barcode', 100)->nullable();
                $table->string('qr_code_token', 100)->nullable();
                $table->string('current_tier', 50)->default('basic');
                $table->decimal('points_balance', 12, 2)->default(0.00);
                $table->decimal('lifetime_points_earned', 12, 2)->default(0.00);
                $table->decimal('lifetime_points_spent', 12, 2)->default(0.00);
                $table->decimal('active_discount_percent', 5, 2)->default(0.00);
                $table->timestampTz('expires_at')->nullable();
                $table->boolean('is_frozen')->default(false);
                $table->timestampsTz();

                $table->unique(['tenant_id', 'card_number'], 'uq_loyalty_tenant_card');
                $table->unique(['customer_id'], 'uq_loyalty_customer');
            });
        }

        // 8. Customer Loyalty Transactions (Ledger)
        if (! Schema::hasTable('customer_loyalty_transactions')) {
            Schema::create('customer_loyalty_transactions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('loyalty_account_id')->constrained('customer_loyalty_accounts')->cascadeOnDelete();
                $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->string('type', 30); // earn, redeem, refund, manual_adj, expire, birthday_gift
                $table->decimal('points_delta', 12, 2);
                $table->decimal('balance_after', 12, 2);
                $table->decimal('currency_value', 12, 2)->default(0.00);
                $table->string('reason', 255);
                $table->foreignUuid('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestampsTz();

                $table->index(['tenant_id', 'customer_id']);
            });
        }

        // 9. Customer Activities (CRM Timeline)
        if (! Schema::hasTable('customer_activities')) {
            Schema::create('customer_activities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('type', 50); // order_placed, order_delivered, call, sms, whatsapp, note, loyalty, address_added
                $table->string('title', 255);
                $table->text('content')->nullable();
                $table->string('reference_type', 100)->nullable();
                $table->uuid('reference_id')->nullable();
                $table->jsonb('metadata')->default('{}');
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestampsTz();

                $table->index(['tenant_id', 'customer_id', 'type']);
            });
        }

        // 10. Customer Notes
        if (! Schema::hasTable('customer_notes')) {
            Schema::create('customer_notes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignUuid('author_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('category', 50)->default('general'); // general, preference, complaint, financial
                $table->text('content');
                $table->boolean('is_pinned')->default(false);
                $table->timestampsTz();

                $table->index(['tenant_id', 'customer_id']);
            });
        }

        // 11. Customer Merge Audit Logs
        if (! Schema::hasTable('customer_merge_logs')) {
            Schema::create('customer_merge_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('primary_customer_id')->constrained('customers')->cascadeOnDelete();
                $table->uuid('merged_customer_id');
                $table->foreignUuid('merged_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->jsonb('snapshot_data');
                $table->text('notes')->nullable();
                $table->timestampsTz();

                $table->index(['tenant_id', 'primary_customer_id']);
            });
        }

        // 12. Add delivery_address_snapshot to orders
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'delivery_address_snapshot')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->jsonb('delivery_address_snapshot')->nullable()->after('customer_address_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'delivery_address_snapshot')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('delivery_address_snapshot');
            });
        }

        Schema::dropIfExists('customer_merge_logs');
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('customer_activities');
        Schema::dropIfExists('customer_loyalty_transactions');
        Schema::dropIfExists('customer_loyalty_accounts');

        if (Schema::hasTable('customer_addresses')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                $table->dropColumn([
                    'country',
                    'province',
                    'postal_code',
                    'street',
                    'building',
                    'entrance',
                    'door_code',
                    'delivery_instructions',
                    'is_last_used',
                ]);
            });
        }

        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customer_companies');
        Schema::dropIfExists('customer_individuals');

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn([
                    'customer_code',
                    'type',
                    'status',
                    'primary_branch_id',
                    'last_order_branch_id',
                    'created_by_user_id',
                    'assigned_manager_id',
                    'acquisition_source_id',
                    'last_order_source_id',
                    'primary_phone',
                    'primary_email',
                    'display_name',
                    'canceled_orders_count',
                    'returned_orders_count',
                    'average_order_value',
                    'lifetime_value',
                    'first_ordered_at',
                    'average_order_frequency_days',
                    'customer_score',
                    'loyalty_tier',
                    'custom_discount_percent',
                    'marketing_sms_consent',
                    'marketing_email_consent',
                    'marketing_calls_consent',
                    'consent_recorded_at',
                ]);
            });
        }

        Schema::dropIfExists('customer_sources');
    }
};
