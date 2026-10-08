<?php

namespace Database\Seeders;

use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use Illuminate\Database\Seeder;

class PlansAndFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Features
        $features = [
            ['code' => 'feature.crm', 'name' => 'CRM Module', 'type' => 'boolean', 'module' => 'crm'],
            ['code' => 'feature.warehouse', 'name' => 'Warehouse & Inventory', 'type' => 'boolean', 'module' => 'warehouse'],
            ['code' => 'feature.inventory_multi_warehouse', 'name' => 'Multi-Warehouse Support', 'type' => 'boolean', 'module' => 'warehouse'],
            ['code' => 'feature.batch_tracking', 'name' => 'Batch, Lot & Expiry Tracking', 'type' => 'boolean', 'module' => 'warehouse'],
            ['code' => 'feature.production', 'name' => 'Production & Recipes', 'type' => 'boolean', 'module' => 'production'],
            ['code' => 'feature.delivery', 'name' => 'Delivery Management', 'type' => 'boolean', 'module' => 'delivery'],
            ['code' => 'feature.iso22000', 'name' => 'ISO 22000 & Quality Assurance', 'type' => 'boolean', 'module' => 'quality'],
            ['code' => 'limit.users', 'name' => 'Active Users Limit', 'type' => 'limit', 'module' => 'iam'],
            ['code' => 'limit.branches', 'name' => 'Branches Limit', 'type' => 'limit', 'module' => 'branches'],
            ['code' => 'limit.warehouses', 'name' => 'Warehouses Limit', 'type' => 'limit', 'module' => 'warehouse'],
            ['code' => 'limit.suppliers', 'name' => 'Suppliers Limit', 'type' => 'limit', 'module' => 'procurement'],
            ['code' => 'limit.products', 'name' => 'Products Limit', 'type' => 'limit', 'module' => 'catalog'],
            ['code' => 'limit.recipes', 'name' => 'Recipes Limit', 'type' => 'limit', 'module' => 'production'],
            ['code' => 'limit.orders_monthly', 'name' => 'Monthly Orders Limit', 'type' => 'limit', 'module' => 'sales'],
            ['code' => 'limit.production_orders_monthly', 'name' => 'Monthly Production Orders Limit', 'type' => 'limit', 'module' => 'production'],
        ];

        $featureModels = [];
        foreach ($features as $f) {
            $featureModels[$f['code']] = Feature::firstOrCreate(['code' => $f['code']], $f);
        }

        // 2. Starter Plan
        $starter = Plan::firstOrCreate(
            ['code' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Ideal for small retail and growing businesses.',
                'price_monthly' => 15000.00,
                'price_yearly' => 150000.00,
                'currency' => 'AMD',
                'trial_days' => 14,
                'sort_order' => 1,
            ]
        );

        $starter->features()->syncWithoutDetaching([
            $featureModels['feature.crm']->id => ['value' => 'true'],
            $featureModels['feature.warehouse']->id => ['value' => 'false'],
            $featureModels['feature.inventory_multi_warehouse']->id => ['value' => 'false'],
            $featureModels['feature.batch_tracking']->id => ['value' => 'false'],
            $featureModels['feature.production']->id => ['value' => 'false'],
            $featureModels['feature.delivery']->id => ['value' => 'false'],
            $featureModels['feature.iso22000']->id => ['value' => 'false'],
            $featureModels['limit.users']->id => ['value' => '3'],
            $featureModels['limit.branches']->id => ['value' => '1'],
            $featureModels['limit.warehouses']->id => ['value' => '1'],
            $featureModels['limit.suppliers']->id => ['value' => '5'],
            $featureModels['limit.products']->id => ['value' => '200'],
            $featureModels['limit.recipes']->id => ['value' => '0'],
            $featureModels['limit.orders_monthly']->id => ['value' => '500'],
            $featureModels['limit.production_orders_monthly']->id => ['value' => '0'],
        ]);

        // 3. Growth Plan
        $growth = Plan::firstOrCreate(
            ['code' => 'growth'],
            [
                'name' => 'Growth & Production',
                'description' => 'For food production, manufacturers, and multi-branch companies.',
                'price_monthly' => 45000.00,
                'price_yearly' => 450000.00,
                'currency' => 'AMD',
                'trial_days' => 14,
                'sort_order' => 2,
            ]
        );

        $growth->features()->syncWithoutDetaching([
            $featureModels['feature.crm']->id => ['value' => 'true'],
            $featureModels['feature.warehouse']->id => ['value' => 'true'],
            $featureModels['feature.inventory_multi_warehouse']->id => ['value' => 'true'],
            $featureModels['feature.batch_tracking']->id => ['value' => 'true'],
            $featureModels['feature.production']->id => ['value' => 'true'],
            $featureModels['feature.delivery']->id => ['value' => 'true'],
            $featureModels['feature.iso22000']->id => ['value' => 'false'],
            $featureModels['limit.users']->id => ['value' => '15'],
            $featureModels['limit.branches']->id => ['value' => '5'],
            $featureModels['limit.warehouses']->id => ['value' => '5'],
            $featureModels['limit.suppliers']->id => ['value' => '50'],
            $featureModels['limit.products']->id => ['value' => '2000'],
            $featureModels['limit.recipes']->id => ['value' => '50'],
            $featureModels['limit.orders_monthly']->id => ['value' => '5000'],
            $featureModels['limit.production_orders_monthly']->id => ['value' => '500'],
        ]);

        // 4. Enterprise Plan
        $enterprise = Plan::firstOrCreate(
            ['code' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Unlimited capacity, ISO 22000 quality modules, and dedicated support.',
                'price_monthly' => 120000.00,
                'price_yearly' => 1200000.00,
                'currency' => 'AMD',
                'trial_days' => 14,
                'sort_order' => 3,
            ]
        );

        $enterprise->features()->syncWithoutDetaching([
            $featureModels['feature.crm']->id => ['value' => 'true'],
            $featureModels['feature.warehouse']->id => ['value' => 'true'],
            $featureModels['feature.inventory_multi_warehouse']->id => ['value' => 'true'],
            $featureModels['feature.batch_tracking']->id => ['value' => 'true'],
            $featureModels['feature.production']->id => ['value' => 'true'],
            $featureModels['feature.delivery']->id => ['value' => 'true'],
            $featureModels['feature.iso22000']->id => ['value' => 'true'],
            $featureModels['limit.users']->id => ['value' => 'unlimited'],
            $featureModels['limit.branches']->id => ['value' => 'unlimited'],
            $featureModels['limit.warehouses']->id => ['value' => 'unlimited'],
            $featureModels['limit.suppliers']->id => ['value' => 'unlimited'],
            $featureModels['limit.products']->id => ['value' => 'unlimited'],
            $featureModels['limit.recipes']->id => ['value' => 'unlimited'],
            $featureModels['limit.orders_monthly']->id => ['value' => 'unlimited'],
            $featureModels['limit.production_orders_monthly']->id => ['value' => 'unlimited'],
        ]);
    }
}
