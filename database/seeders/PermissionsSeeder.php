<?php

namespace Database\Seeders;

use App\Domain\IAM\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // CRM
            ['code' => 'crm.customers.view', 'module' => 'crm', 'name' => 'View Customers'],
            ['code' => 'crm.customers.create', 'module' => 'crm', 'name' => 'Create Customers'],
            ['code' => 'crm.customers.edit', 'module' => 'crm', 'name' => 'Edit Customers'],
            ['code' => 'crm.customers.delete', 'module' => 'crm', 'name' => 'Delete Customers'],

            // Sales & Orders
            ['code' => 'sales.orders.view', 'module' => 'sales', 'name' => 'View Orders'],
            ['code' => 'sales.orders.create', 'module' => 'sales', 'name' => 'Create Orders'],
            ['code' => 'sales.orders.edit', 'module' => 'sales', 'name' => 'Edit Orders'],
            ['code' => 'sales.orders.cancel', 'module' => 'sales', 'name' => 'Cancel Orders'],

            // Catalog & Products
            ['code' => 'catalog.products.view', 'module' => 'catalog', 'name' => 'View Products'],
            ['code' => 'catalog.products.create', 'module' => 'catalog', 'name' => 'Create Products'],
            ['code' => 'catalog.products.edit', 'module' => 'catalog', 'name' => 'Edit Products'],
            ['code' => 'catalog.products.delete', 'module' => 'catalog', 'name' => 'Delete Products'],

            // Warehouse & Stock
            ['code' => 'warehouse.warehouses.view', 'module' => 'warehouse', 'name' => 'View Warehouses'],
            ['code' => 'warehouse.warehouses.manage', 'module' => 'warehouse', 'name' => 'Manage Warehouses'],
            ['code' => 'warehouse.stock.view', 'module' => 'warehouse', 'name' => 'View Stock'],
            ['code' => 'warehouse.stock.adjust', 'module' => 'warehouse', 'name' => 'Adjust Stock Quantities'],
            ['code' => 'warehouse.movements.view', 'module' => 'warehouse', 'name' => 'View Stock Movements'],
            ['code' => 'warehouse.movements.create', 'module' => 'warehouse', 'name' => 'Create Stock Movements'],
            ['code' => 'warehouse.transfers.manage', 'module' => 'warehouse', 'name' => 'Manage Stock Transfers'],

            // Procurement & Suppliers
            ['code' => 'procurement.suppliers.view', 'module' => 'procurement', 'name' => 'View Suppliers'],
            ['code' => 'procurement.suppliers.manage', 'module' => 'procurement', 'name' => 'Manage Suppliers'],
            ['code' => 'procurement.orders.view', 'module' => 'procurement', 'name' => 'View Purchase Orders'],
            ['code' => 'procurement.orders.manage', 'module' => 'procurement', 'name' => 'Manage Purchase Orders'],
            ['code' => 'procurement.orders.receive', 'module' => 'procurement', 'name' => 'Receive Purchase Order Goods'],

            // Production (Food / Manufacturing)
            ['code' => 'production.recipes.view', 'module' => 'production', 'name' => 'View Recipes'],
            ['code' => 'production.recipes.manage', 'module' => 'production', 'name' => 'Manage Recipes'],
            ['code' => 'production.orders.manage', 'module' => 'production', 'name' => 'Manage Production Orders'],

            // Quality Assurance & ISO 22000
            ['code' => 'quality.inspections.view', 'module' => 'quality', 'name' => 'View Quality Inspections'],
            ['code' => 'quality.inspections.manage', 'module' => 'quality', 'name' => 'Perform & Manage Quality Inspections'],

            // Delivery
            ['code' => 'delivery.orders.view', 'module' => 'delivery', 'name' => 'View Deliveries'],
            ['code' => 'delivery.orders.dispatch', 'module' => 'delivery', 'name' => 'Dispatch Deliveries'],

            // Settings & RBAC
            ['code' => 'settings.view', 'module' => 'settings', 'name' => 'View Settings'],
            ['code' => 'settings.manage', 'module' => 'settings', 'name' => 'Manage Settings'],
            ['code' => 'roles.manage', 'module' => 'settings', 'name' => 'Manage Roles and Permissions'],

            // Billing
            ['code' => 'billing.manage', 'module' => 'billing', 'name' => 'Manage Subscriptions and Invoices'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['code' => $perm['code']], $perm);
        }
    }
}
