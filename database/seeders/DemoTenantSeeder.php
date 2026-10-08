<?php

namespace Database\Seeders;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerAddress;
use App\Domain\IAM\Models\Permission;
use App\Domain\IAM\Models\Role;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantDomain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::where('code', 'growth')->first() ?? Plan::first();

        // 1. Tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'gourmet'],
            [
                'name' => 'Armenia Gourmet Food',
                'legal_name' => 'Gourmet Food LLC',
                'tax_number' => '02589412',
                'subdomain' => 'gourmet',
                'status' => 'active',
                'country' => 'AM',
                'currency' => 'AMD',
                'timezone' => 'Asia/Yerevan',
                'default_locale' => 'hy',
                'trial_ends_at' => now()->addDays(30),
            ]
        );

        TenantDomain::firstOrCreate(
            ['domain' => 'gourmet.localhost'],
            [
                'tenant_id' => $tenant->id,
                'is_primary' => true,
                'is_verified' => true,
                'ssl_status' => 'active',
            ]
        );

        if ($plan) {
            Subscription::firstOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'billing_cycle' => 'monthly',
                    'starts_at' => now()->subDays(5),
                    'ends_at' => now()->addDays(25),
                    'auto_renew' => true,
                ]
            );
        }

        // 2. Roles & Permissions
        $ownerRole = Role::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'owner'],
            [
                'name' => 'Owner',
                'description' => 'Account Owner with absolute permissions.',
                'is_system' => true,
            ]
        );

        $allPerms = Permission::all();
        if ($allPerms->isNotEmpty()) {
            $ownerRole->permissions()->syncWithoutDetaching($allPerms->pluck('id'));
        }

        // 3. Owner User
        $owner = User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'aram@gourmet.am'],
            [
                'name' => 'Aram Muradyan',
                'phone' => '+37491000111',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'is_owner' => true,
                'locale' => 'hy',
            ]
        );
        $owner->assignRole($ownerRole);

        // 4. Branches
        $branchMain = Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'KENTRON'],
            [
                'name' => 'Kentron Main Branch',
                'address' => 'Abovyan 12, Yerevan',
                'phone' => '+37410521122',
                'is_main' => true,
                'is_active' => true,
            ]
        );

        $branchKomitas = Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'KOMITAS'],
            [
                'name' => 'Komitas Branch',
                'address' => 'Komitas Ave 28, Yerevan',
                'phone' => '+37410234455',
                'is_main' => false,
                'is_active' => true,
            ]
        );

        // 5. Units
        $unitKg = Unit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'kg'],
            ['name' => ['hy' => 'կգ', 'en' => 'kg', 'ru' => 'кг'], 'precision' => 3]
        );

        $unitPcs = Unit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'pcs'],
            ['name' => ['hy' => 'հատ', 'en' => 'pcs', 'ru' => 'шт'], 'precision' => 0]
        );

        // 6. Categories
        $catBakery = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'bakery'],
            ['name' => ['hy' => 'Հացաբուլկեղեն', 'en' => 'Bakery', 'ru' => 'Выпечка'], 'sort_order' => 1]
        );

        $catDairy = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'dairy'],
            ['name' => ['hy' => 'Կաթնամթերք', 'en' => 'Dairy', 'ru' => 'Молочные продукты'], 'sort_order' => 2]
        );

        $catMeat = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'meat'],
            ['name' => ['hy' => 'Մսամթերք', 'en' => 'Meat Products', 'ru' => 'Мясные изделия'], 'sort_order' => 3]
        );

        // 7. Products
        $p1 = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'BREAD-MATNAKASH'],
            [
                'category_id' => $catBakery->id,
                'unit_id' => $unitPcs->id,
                'barcode' => '485000100001',
                'name' => ['hy' => 'Մատնաքաշ Թոնրի', 'en' => 'Tonir Matnakash', 'ru' => 'Матнакаш Тондырный'],
                'cost_price' => 150.00,
                'sale_price' => 250.00,
                'currency' => 'AMD',
                'track_stock' => true,
                'is_produced' => true,
            ]
        );

        $p2 = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'CHEESE-LORI'],
            [
                'category_id' => $catDairy->id,
                'unit_id' => $unitKg->id,
                'barcode' => '485000100002',
                'name' => ['hy' => 'Լոռի Պանիր (Տնական)', 'en' => 'Lori Cheese', 'ru' => 'Сыр Лори'],
                'cost_price' => 2200.00,
                'sale_price' => 3200.00,
                'currency' => 'AMD',
                'track_stock' => true,
                'is_produced' => false,
            ]
        );

        $p3 = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'MEAT-BASTURMA'],
            [
                'category_id' => $catMeat->id,
                'unit_id' => $unitKg->id,
                'barcode' => '485000100003',
                'name' => ['hy' => 'Տավարի Բաստուրմա Պրեմիում', 'en' => 'Beef Basturma Premium', 'ru' => 'Бастурма Говяжья'],
                'cost_price' => 4500.00,
                'sale_price' => 6800.00,
                'currency' => 'AMD',
                'track_stock' => true,
                'is_produced' => true,
            ]
        );

        ProductVariant::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'BASTURMA-SLICED-200G'],
            [
                'product_id' => $p3->id,
                'name' => ['hy' => 'Կտրատված 200գ', 'en' => 'Sliced 200g'],
                'sale_price' => 1600.00,
                'attributes' => ['packaging' => 'sliced_200g'],
            ]
        );

        // 8. Customer
        $customer = Customer::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '+37491223344'],
            [
                'first_name' => 'Գևորգ',
                'last_name' => 'Հակոբյան',
                'email' => 'gevorg@example.am',
                'company_name' => 'Հակոբյան Գրուպ',
                'tax_id' => '01234567',
                'notes' => 'VIP Customer',
                'total_spent' => 12400.00,
                'orders_count' => 1,
                'last_ordered_at' => now()->subDays(2),
            ]
        );

        $address = CustomerAddress::firstOrCreate(
            ['tenant_id' => $tenant->id, 'customer_id' => $customer->id, 'address_line_1' => 'Սայաթ-Նովա պող. 10'],
            [
                'title' => 'Գրասենյակ',
                'city' => 'Երևան',
                'floor' => '4',
                'apartment' => '18',
                'entry_code' => '45K',
                'is_default' => true,
            ]
        );

        // 9. Sample Orders
        $year = date('Y');
        $order1 = Order::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_number' => "ORD-{$year}-000001"],
            [
                'branch_id' => $branchMain->id,
                'customer_id' => $customer->id,
                'customer_address_id' => $address->id,
                'status' => 'delivered',
                'delivery_type' => 'delivery',
                'subtotal' => 12400.00,
                'discount' => 0.00,
                'delivery_fee' => 0.00,
                'total' => 12400.00,
                'currency' => 'AMD',
                'payment_status' => 'paid',
                'placed_at' => now()->subDays(2),
                'delivered_at' => now()->subDays(2)->addHours(1),
            ]
        );

        OrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_id' => $order1->id, 'product_id' => $p3->id],
            [
                'product_name' => 'Տավարի Բաստուրմա Պրեմիում',
                'product_sku' => 'MEAT-BASTURMA',
                'quantity' => 1.500,
                'unit_price' => 6800.00,
                'total' => 10200.00,
            ]
        );

        OrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_id' => $order1->id, 'product_id' => $p2->id],
            [
                'product_name' => 'Լոռի Պանիր (Տնական)',
                'product_sku' => 'CHEESE-LORI',
                'quantity' => 0.687,
                'unit_price' => 3200.00,
                'total' => 2200.00,
            ]
        );

        OrderStatusHistory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_id' => $order1->id, 'to_status' => 'delivered'],
            [
                'user_id' => $owner->id,
                'from_status' => 'delivery',
                'comment' => 'Delivered to customer office',
            ]
        );

        $order2 = Order::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_number' => "ORD-{$year}-000002"],
            [
                'branch_id' => $branchKomitas->id,
                'customer_id' => $customer->id,
                'status' => 'processing',
                'delivery_type' => 'pickup',
                'subtotal' => 3450.00,
                'total' => 3450.00,
                'currency' => 'AMD',
                'payment_status' => 'unpaid',
                'placed_at' => now()->subMinutes(30),
            ]
        );

        OrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_id' => $order2->id, 'product_id' => $p1->id],
            [
                'product_name' => 'Մատնաքաշ Թոնրի',
                'product_sku' => 'BREAD-MATNAKASH',
                'quantity' => 5.000,
                'unit_price' => 250.00,
                'total' => 1250.00,
            ]
        );

        OrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_id' => $order2->id, 'product_id' => $p2->id],
            [
                'product_name' => 'Լոռի Պանիր (Տնական)',
                'product_sku' => 'CHEESE-LORI',
                'quantity' => 0.687,
                'unit_price' => 3200.00,
                'total' => 2200.00,
            ]
        );
    }
}
