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
        // 1. Enhance suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'website')) {
                $table->string('website', 255)->nullable()->after('email');
            }
            if (! Schema::hasColumn('suppliers', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('address');
            }
            if (! Schema::hasColumn('suppliers', 'legal_address')) {
                $table->text('legal_address')->nullable()->after('shipping_address');
            }
        });

        // 2. Create supplier_couriers table
        if (! Schema::hasTable('supplier_couriers')) {
            Schema::create('supplier_couriers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->string('name', 255);
                $table->string('phone', 50)->nullable();
                $table->string('vehicle_model', 100)->nullable();
                $table->string('license_plate', 50)->nullable();
                $table->timestampsTz();

                $table->index(['tenant_id', 'supplier_id']);
            });
        }

        // 3. Create supplier_products pivot table (many-to-many suppliers and products/ingredients)
        if (! Schema::hasTable('supplier_products')) {
            Schema::create('supplier_products', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('supply_price', 15, 2)->nullable();
                $table->integer('lead_time_days')->nullable();
                $table->timestampsTz();

                $table->unique(['tenant_id', 'supplier_id', 'product_id'], 'uq_supplier_products_unique');
                $table->index(['tenant_id', 'product_id']);
            });
        }

        // 4. Enhance products table for detailed ingredient & invoice attributes
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'hs_code')) {
                $table->string('hs_code', 50)->nullable()->after('barcode'); // ԱՏԳ ԱԱ ծածկագիր
            }
            if (! Schema::hasColumn('products', 'subcategory_id')) {
                $table->foreignUuid('subcategory_id')->nullable()->after('category_id')->constrained('categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('products', 'packaging')) {
                $table->string('packaging', 100)->nullable()->after('unit_id'); // Տարա (պարկ, տուփ, etc.)
            }
            if (! Schema::hasColumn('products', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(20.00)->after('sale_price'); // ԱԱՀ դրույքաչափ (%)
            }
            if (! Schema::hasColumn('products', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0.00)->after('vat_rate'); // Զեղչ (%)
            }
            if (! Schema::hasColumn('products', 'transaction_type')) {
                $table->string('transaction_type', 50)->default('local_purchase')->after('discount_percent'); // Գործարքի տեսակ
            }
            if (! Schema::hasColumn('products', 'min_stock_level')) {
                $table->decimal('min_stock_level', 15, 3)->default(0.000)->after('track_stock'); // Նվազագույն քանակ հիշեցման համար
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn([
                'hs_code',
                'subcategory_id',
                'packaging',
                'vat_rate',
                'discount_percent',
                'transaction_type',
                'min_stock_level',
            ]);
        });

        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('supplier_couriers');

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'website',
                'shipping_address',
                'legal_address',
            ]);
        });
    }
};
