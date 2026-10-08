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
use App\Domain\Procurement\Models\PurchaseOrder;
use App\Domain\Procurement\Models\PurchaseOrderItem;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantDomain;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\StockMovement;
use App\Domain\Warehouse\Models\StockTransfer;
use App\Domain\Warehouse\Models\StockTransferItem;
use App\Domain\Warehouse\Models\Warehouse;
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

        // 10. Warehouses (Phase 2)
        $whMain = Warehouse::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'WH-MAIN'],
            [
                'branch_id' => $branchMain->id,
                'name' => 'Կենտրոն Գլխավոր Պահեստ',
                'type' => 'standard',
                'address' => 'Abovyan 12, Yerevan',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        $whCold = Warehouse::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'WH-COLD'],
            [
                'branch_id' => $branchMain->id,
                'name' => 'Սառնարանային Պահեստ N1 (Կաթնամթերք և Միս)',
                'type' => 'cold_storage',
                'address' => 'Abovyan 12 (Sub-level 1), Yerevan',
                'is_active' => true,
                'is_default' => false,
            ]
        );

        // 11. Suppliers (Phase 2)
        $supplierMeat = Supplier::firstOrCreate(
            ['tenant_id' => $tenant->id, 'tax_id' => '01548234'],
            [
                'company_name' => 'Արարատ Միս Ֆարմ ՍՊԸ',
                'legal_name' => '«Արարատ Միս Ֆարմ» ՍՊԸ',
                'contact_person' => 'Կարեն Գրիգորյան',
                'email' => 'karen@araratmeat.am',
                'phone' => '+37493112233',
                'address' => 'Արարատի մարզ, գ. Ոսկետափ',
                'currency' => 'AMD',
                'payment_terms_days' => 14,
                'is_active' => true,
            ]
        );

        $supplierDairy = Supplier::firstOrCreate(
            ['tenant_id' => $tenant->id, 'tax_id' => '06938219'],
            [
                'company_name' => 'Լոռվա Կաթնամթերք ՓԲԸ',
                'legal_name' => '«Լոռվա Կաթնամթերք» ՓԲԸ',
                'contact_person' => 'Անահիտ Սարգսյան',
                'email' => 'info@loridairy.am',
                'phone' => '+37494445566',
                'address' => 'Լոռու մարզ, ք. Ստեփանավան',
                'currency' => 'AMD',
                'payment_terms_days' => 7,
                'is_active' => true,
            ]
        );

        // 12. Stock Batches & Levels (Phase 2)
        $batchCheese = StockBatch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'warehouse_id' => $whCold->id, 'product_id' => $p2->id, 'batch_number' => 'LOT-202610-001'],
            [
                'quantity_on_hand' => 85.0000,
                'quantity_reserved' => 0.6870,
                'cost_price' => 2200.00,
                'mfg_date' => now()->subDays(5)->toDateString(),
                'expiry_date' => now()->addDays(55)->toDateString(),
                'status' => 'active',
                'notes' => 'Բարձր յուղայնության տնական լոռի',
            ]
        );

        $batchBasturma = StockBatch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'warehouse_id' => $whCold->id, 'product_id' => $p3->id, 'batch_number' => 'LOT-202610-002'],
            [
                'quantity_on_hand' => 45.0000,
                'quantity_reserved' => 0.0000,
                'cost_price' => 4500.00,
                'mfg_date' => now()->subDays(12)->toDateString(),
                'expiry_date' => now()->addDays(108)->toDateString(),
                'status' => 'active',
                'notes' => 'Պրեմիում տավարի ֆիլեից',
            ]
        );

        StockLevel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'warehouse_id' => $whCold->id, 'product_id' => $p2->id, 'product_variant_id' => null],
            [
                'quantity_on_hand' => 85.0000,
                'quantity_reserved' => 0.6870,
                'reorder_point' => 20.0000,
                'ideal_stock' => 100.0000,
                'updated_at' => now(),
            ]
        );

        StockLevel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'warehouse_id' => $whCold->id, 'product_id' => $p3->id, 'product_variant_id' => null],
            [
                'quantity_on_hand' => 45.0000,
                'quantity_reserved' => 0.0000,
                'reorder_point' => 15.0000,
                'ideal_stock' => 50.0000,
                'updated_at' => now(),
            ]
        );

        StockLevel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'warehouse_id' => $whMain->id, 'product_id' => $p1->id, 'product_variant_id' => null],
            [
                'quantity_on_hand' => 150.0000,
                'quantity_reserved' => 5.0000,
                'reorder_point' => 50.0000,
                'ideal_stock' => 200.0000,
                'updated_at' => now(),
            ]
        );

        // 13. Purchase Orders (Phase 2)
        $po1 = PurchaseOrder::firstOrCreate(
            ['tenant_id' => $tenant->id, 'po_number' => "PO-{$year}-000001"],
            [
                'supplier_id' => $supplierDairy->id,
                'warehouse_id' => $whCold->id,
                'user_id' => $owner->id,
                'status' => 'received',
                'order_date' => now()->subDays(6)->toDateString(),
                'received_at' => now()->subDays(5),
                'subtotal' => 187000.00,
                'tax_amount' => 0.00,
                'total' => 187000.00,
                'currency' => 'AMD',
                'payment_status' => 'paid',
                'notes' => 'Առաջին փորձնական խմբաքանակի գնում',
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'purchase_order_id' => $po1->id, 'product_id' => $p2->id],
            [
                'quantity_ordered' => 85.0000,
                'quantity_received' => 85.0000,
                'unit_cost' => 2200.00,
                'total' => 187000.00,
            ]
        );

        // Movement for PO1 receipt
        StockMovement::firstOrCreate(
            ['tenant_id' => $tenant->id, 'reference_type' => PurchaseOrder::class, 'reference_id' => $po1->id],
            [
                'warehouse_id' => $whCold->id,
                'product_id' => $p2->id,
                'product_variant_id' => null,
                'stock_batch_id' => $batchCheese->id,
                'user_id' => $owner->id,
                'type' => 'purchase_receipt',
                'quantity' => 85.0000,
                'unit_cost' => 2200.00,
                'balance_before' => 0.0000,
                'balance_after' => 85.0000,
                'notes' => 'Goods receipt from Lori Dairies',
                'created_at' => now()->subDays(5),
            ]
        );

        $po2 = PurchaseOrder::firstOrCreate(
            ['tenant_id' => $tenant->id, 'po_number' => "PO-{$year}-000002"],
            [
                'supplier_id' => $supplierMeat->id,
                'warehouse_id' => $whCold->id,
                'user_id' => $owner->id,
                'status' => 'ordered',
                'order_date' => now()->subDays(1)->toDateString(),
                'expected_delivery_date' => now()->addDays(2)->toDateString(),
                'subtotal' => 225000.00,
                'tax_amount' => 0.00,
                'total' => 225000.00,
                'currency' => 'AMD',
                'payment_status' => 'unpaid',
                'notes' => 'Տավարի մսի հումքի հերթական պատվեր',
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'purchase_order_id' => $po2->id, 'product_id' => $p3->id],
            [
                'quantity_ordered' => 50.0000,
                'quantity_received' => 0.0000,
                'unit_cost' => 4500.00,
                'total' => 225000.00,
            ]
        );
    }
}
