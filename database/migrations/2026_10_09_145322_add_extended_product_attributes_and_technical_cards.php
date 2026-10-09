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
        Schema::table('products', function (Blueprint $table) {
            // Taxation & Pricing flexibility
            if (! Schema::hasColumn('products', 'has_vat')) {
                $table->boolean('has_vat')->default(true)->after('vat_rate');
            }
            if (! Schema::hasColumn('products', 'allow_discount')) {
                $table->boolean('allow_discount')->default(true)->after('discount_percent');
            }
            if (! Schema::hasColumn('products', 'allow_price_edit')) {
                $table->boolean('allow_price_edit')->default(false)->after('allow_discount');
            }
            if (! Schema::hasColumn('products', 'special_price')) {
                $table->decimal('special_price', 15, 2)->nullable()->after('sale_price');
            }

            // Food & Retail Modifiers & Order flags
            if (! Schema::hasColumn('products', 'allow_modifiers')) {
                $table->boolean('allow_modifiers')->default(false)->after('is_produced');
            }
            if (! Schema::hasColumn('products', 'is_ungrouped_in_order')) {
                $table->boolean('is_ungrouped_in_order')->default(false)->after('allow_modifiers');
            }

            // Regulatory, Excise, Marking, Stop-List
            if (! Schema::hasColumn('products', 'is_excise')) {
                $table->boolean('is_excise')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('products', 'is_marked')) {
                $table->boolean('is_marked')->default(false)->after('is_excise');
            }
            if (! Schema::hasColumn('products', 'is_stop_list')) {
                $table->boolean('is_stop_list')->default(false)->after('is_marked');
            }

            // Portion, Weight, Calories & EU Allergens / Dietary
            if (! Schema::hasColumn('products', 'net_quantity')) {
                $table->string('net_quantity', 100)->nullable()->after('packaging');
            }
            if (! Schema::hasColumn('products', 'calories')) {
                $table->decimal('calories', 8, 2)->nullable()->after('net_quantity');
            }
            if (! Schema::hasColumn('products', 'nutritional_info')) {
                $table->jsonb('nutritional_info')->default('{}')->after('calories');
            }
            if (! Schema::hasColumn('products', 'allergens')) {
                $table->jsonb('allergens')->default('[]')->after('nutritional_info');
            }
            if (! Schema::hasColumn('products', 'dietary_tags')) {
                $table->jsonb('dietary_tags')->default('[]')->after('allergens');
            }

            // Availability (Branches, Time Windows, Happy Hour Discounts)
            if (! Schema::hasColumn('products', 'available_branch_ids')) {
                $table->jsonb('available_branch_ids')->default('[]')->after('dietary_tags');
            }
            if (! Schema::hasColumn('products', 'time_availability')) {
                $table->jsonb('time_availability')->default('{}')->after('available_branch_ids');
            }
            if (! Schema::hasColumn('products', 'discount_hours')) {
                $table->jsonb('discount_hours')->default('{}')->after('time_availability');
            }
            if (! Schema::hasColumn('products', 'shelf_life_info')) {
                $table->string('shelf_life_info', 150)->nullable()->after('shelf_life_days');
            }
        });

        // Add gross quantity & unit cost snapshot to recipe_items
        Schema::table('recipe_items', function (Blueprint $table) {
            if (! Schema::hasColumn('recipe_items', 'gross_quantity')) {
                $table->decimal('gross_quantity', 15, 4)->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('recipe_items', 'cost_per_unit')) {
                $table->decimal('cost_per_unit', 15, 4)->nullable()->after('waste_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_items', function (Blueprint $table) {
            $table->dropColumn(['gross_quantity', 'cost_per_unit']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'has_vat',
                'allow_discount',
                'allow_price_edit',
                'special_price',
                'allow_modifiers',
                'is_ungrouped_in_order',
                'is_excise',
                'is_marked',
                'is_stop_list',
                'net_quantity',
                'calories',
                'nutritional_info',
                'allergens',
                'dietary_tags',
                'available_branch_ids',
                'time_availability',
                'discount_hours',
                'shelf_life_info',
            ]);
        });
    }
};
