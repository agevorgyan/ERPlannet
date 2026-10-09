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
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\Delivery\Models\DeliveryProof;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\IAM\Models\Permission;
use App\Domain\IAM\Models\Role;
use App\Domain\IAM\Models\User;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Manufacturing\Models\ProductionOrderItem;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Manufacturing\Models\RecipeItem;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\POS\Models\PosCashMovement;
use App\Domain\POS\Models\PosSession;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\Procurement\Models\PurchaseOrder;
use App\Domain\Procurement\Models\PurchaseOrderItem;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Quality\Models\QualityInspection;
use App\Domain\Quality\Models\QualityInspectionItem;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantDomain;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\StockMovement;
use App\Domain\Warehouse\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::where('code', 'enterprise')->first() ?? Plan::first();

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
            ['name' => ['hy' => 'Հացաբուլկեղեն', 'en' => 'Bakery', 'ru' => 'Выпечка'], 'type' => 'product', 'sort_order' => 1]
        );

        $catDairy = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'dairy'],
            ['name' => ['hy' => 'Կաթնամթերք', 'en' => 'Dairy', 'ru' => 'Молочные продукты'], 'type' => 'product', 'sort_order' => 2]
        );

        $catMeat = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'meat'],
            ['name' => ['hy' => 'Մսամթերք', 'en' => 'Meat Products', 'ru' => 'Мясные изделия'], 'type' => 'product', 'sort_order' => 3]
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
                'shelf_life_days' => 180,
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

        $catRaw = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'raw-materials'],
            ['name' => ['hy' => 'Հումք և նյութեր', 'en' => 'Raw Materials', 'ru' => 'Сырье и материалы'], 'type' => 'ingredient', 'sort_order' => 4]
        );

        $pFlour = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'ING-FLOUR'],
            [
                'category_id' => $catRaw->id,
                'unit_id' => $unitKg->id,
                'name' => ['hy' => 'Ցորենի ալյուր Բ/Տ', 'en' => 'Wheat Flour Premium', 'ru' => 'Мука пшеничная в/с'],
                'cost_price' => 320.00,
                'sale_price' => 420.00,
                'currency' => 'AMD',
                'track_stock' => true,
                'is_produced' => false,
                'shelf_life_days' => 180,
            ]
        );

        $pYeast = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'ING-YEAST'],
            [
                'category_id' => $catRaw->id,
                'unit_id' => $unitKg->id,
                'name' => ['hy' => 'Հացի խմորիչ չոր', 'en' => 'Baking Yeast Dry', 'ru' => 'Дрожжи сухие'],
                'cost_price' => 1800.00,
                'sale_price' => 2400.00,
                'currency' => 'AMD',
                'track_stock' => true,
                'is_produced' => false,
                'shelf_life_days' => 90,
            ]
        );

        $pMilk = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'ING-MILK'],
            [
                'category_id' => $catRaw->id,
                'unit_id' => $unitKg->id,
                'name' => ['hy' => 'Կովի անարատ կաթ', 'en' => 'Raw Cow Milk Grade A', 'ru' => 'Коровье молоко цельное'],
                'cost_price' => 220.00,
                'sale_price' => 280.00,
                'currency' => 'AMD',
                'track_stock' => true,
                'is_produced' => false,
                'shelf_life_days' => 3,
            ]
        );

        $pSpices = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'ING-SPICES'],
            [
                'category_id' => $catRaw->id,
                'unit_id' => $unitKg->id,
                'name' => ['hy' => 'Չաման և բաստուրմայի համեմունք', 'en' => 'Chaman & Basturma Spices', 'ru' => 'Чаман и специи'],
                'cost_price' => 3500.00,
                'sale_price' => 4500.00,
                'currency' => 'AMD',
                'track_stock' => true,
                'is_produced' => false,
                'shelf_life_days' => 365,
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

        // =========================================================================
        // 14. Phase 3: Raw Material Stock Levels & Initial Inventory (WH-MAIN)
        // =========================================================================
        $rawInventory = [
            ['product' => $pFlour, 'qty' => 1200.0, 'cost' => 320.0],
            ['product' => $pYeast, 'qty' => 60.0, 'cost' => 1800.0],
            ['product' => $pMilk, 'qty' => 850.0, 'cost' => 220.0],
            ['product' => $pSpices, 'qty' => 45.0, 'cost' => 3500.0],
        ];

        foreach ($rawInventory as $item) {
            StockLevel::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'warehouse_id' => $whMain->id,
                    'product_id' => $item['product']->id,
                    'product_variant_id' => null,
                ],
                [
                    'quantity_on_hand' => $item['qty'],
                    'quantity_reserved' => 0.0,
                    'reorder_point' => 50.0,
                    'ideal_stock' => 200.0,
                ]
            );

            StockMovement::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'warehouse_id' => $whMain->id,
                    'product_id' => $item['product']->id,
                    'type' => 'adjustment_plus',
                    'notes' => 'Initial raw materials stock intake',
                ],
                [
                    'quantity' => $item['qty'],
                    'unit_cost' => $item['cost'],
                    'balance_before' => 0.0,
                    'balance_after' => $item['qty'],
                    'user_id' => $owner->id,
                    'created_at' => now()->subDays(4),
                ]
            );
        }

        // =========================================================================
        // 15. Phase 3: Recipes & Bill of Materials (BOM)
        // =========================================================================
        // Recipe 1: Tonir Matnakash Traditional (100 pcs)
        $recipeBread = Recipe::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'RCP-MATNAKASH-100'],
            [
                'product_id' => $p1->id,
                'name' => 'Ավանդական Թոնրի Մատնաքաշ (100 հատ)',
                'version' => '1.0',
                'yield_quantity' => 100.0,
                'yield_unit_id' => $unitPcs->id,
                'scrap_percentage' => 1.5,
                'labor_cost' => 3000.00,
                'overhead_cost' => 1200.00,
                'instructions' => '1. Խառնել ալյուրը, ջուրը և խմորիչը: 2. Հասունացնել 45 րոպե: 3. Թխել թոնրում 240°C 12-14 րոպե:',
                'is_active' => true,
            ]
        );

        RecipeItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'recipe_id' => $recipeBread->id, 'product_id' => $pFlour->id],
            [
                'quantity' => 50.0,
                'unit_id' => $unitKg->id,
                'waste_percentage' => 2.0,
                'sort_order' => 1,
                'notes' => 'Մաղած ալյուր',
            ]
        );

        RecipeItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'recipe_id' => $recipeBread->id, 'product_id' => $pYeast->id],
            [
                'quantity' => 1.2,
                'unit_id' => $unitKg->id,
                'waste_percentage' => 0.0,
                'sort_order' => 2,
                'notes' => 'Տաք ջրում լուծված',
            ]
        );

        // Recipe 2: Lori Cheese Homemade (10 kg)
        $recipeCheese = Recipe::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'RCP-LORI-CHEESE-10'],
            [
                'product_id' => $p2->id,
                'name' => 'Լոռի Պանիր Տնական Բաղադրատոմս (10 կգ)',
                'version' => '1.0',
                'yield_quantity' => 10.0,
                'yield_unit_id' => $unitKg->id,
                'scrap_percentage' => 0.5,
                'labor_cost' => 4500.00,
                'overhead_cost' => 1800.00,
                'instructions' => 'Պաստերիզացիա 72°C, մակարդում, շիճուկի հեռացում, աղաջրում հասունացում 45 օր:',
                'is_active' => true,
            ]
        );

        RecipeItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'recipe_id' => $recipeCheese->id, 'product_id' => $pMilk->id],
            [
                'quantity' => 100.0,
                'unit_id' => $unitKg->id,
                'waste_percentage' => 0.0,
                'sort_order' => 1,
                'notes' => 'Անարատ կովի կաթ',
            ]
        );

        // Recipe 3: Basturma Premium (20 kg)
        $recipeBasturma = Recipe::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'RCP-BASTURMA-20'],
            [
                'product_id' => $p3->id,
                'name' => 'Տավարի Բաստուրմա Պրեմիում (20 կգ)',
                'version' => '1.0',
                'yield_quantity' => 20.0,
                'yield_unit_id' => $unitKg->id,
                'scrap_percentage' => 3.0,
                'labor_cost' => 15000.00,
                'overhead_cost' => 6000.00,
                'instructions' => 'Աղ դնել 14 օր, չորացնել ճնշման տակ, պատել չամանով և հասունացնել 25 օր:',
                'is_active' => true,
            ]
        );

        RecipeItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'recipe_id' => $recipeBasturma->id, 'product_id' => $pSpices->id],
            [
                'quantity' => 2.5,
                'unit_id' => $unitKg->id,
                'waste_percentage' => 1.0,
                'sort_order' => 1,
                'notes' => 'Չամանի խառնուրդ',
            ]
        );

        // =========================================================================
        // 16. Phase 3: Production Orders
        // =========================================================================
        // Order 1: Completed Tonir Matnakash batch
        $batchMatnakash = StockBatch::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'warehouse_id' => $whMain->id,
                'product_id' => $p1->id,
                'batch_number' => "LOT-{$year}1008-000001",
            ],
            [
                'quantity_on_hand' => 98.0,
                'cost_price' => 155.0,
                'mfg_date' => now()->toDateString(),
                'expiry_date' => now()->addDays(3)->toDateString(),
                'status' => 'active',
                'notes' => 'Առավոտյան թխվածք #1 (Թոնիր)',
            ]
        );

        $prodOrder1 = ProductionOrder::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_number' => "PRD-{$year}-000001"],
            [
                'recipe_id' => $recipeBread->id,
                'product_id' => $p1->id,
                'product_variant_id' => null,
                'source_warehouse_id' => $whMain->id,
                'target_warehouse_id' => $whMain->id,
                'user_id' => $owner->id,
                'status' => 'completed',
                'planned_quantity' => 100.0,
                'actual_quantity' => 98.0,
                'waste_quantity' => 2.0,
                'unit_cost' => 155.0,
                'total_cost' => 15190.0,
                'stock_batch_id' => $batchMatnakash->id,
                'planned_start_date' => now()->subHours(4)->toDateString(),
                'started_at' => now()->subHours(4),
                'completed_at' => now()->subHours(1),
                'notes' => 'Առավոտյան թոնրի հերթափոխ #1',
            ]
        );

        ProductionOrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'production_order_id' => $prodOrder1->id, 'product_id' => $pFlour->id],
            [
                'planned_quantity' => 51.0,
                'consumed_quantity' => 51.0,
                'unit_cost' => 320.0,
                'total_cost' => 16320.0,
            ]
        );

        ProductionOrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'production_order_id' => $prodOrder1->id, 'product_id' => $pYeast->id],
            [
                'planned_quantity' => 1.2,
                'consumed_quantity' => 1.2,
                'unit_cost' => 1800.0,
                'total_cost' => 2160.0,
            ]
        );

        // Movement for yield
        StockMovement::firstOrCreate(
            ['tenant_id' => $tenant->id, 'reference_type' => ProductionOrder::class, 'reference_id' => $prodOrder1->id, 'type' => 'production_yield'],
            [
                'warehouse_id' => $whMain->id,
                'product_id' => $p1->id,
                'stock_batch_id' => $batchMatnakash->id,
                'quantity' => 98.0,
                'unit_cost' => 155.0,
                'balance_before' => 150.0,
                'balance_after' => 248.0,
                'user_id' => $owner->id,
                'notes' => 'Finished goods yield from PRD-2026-000001',
                'created_at' => now()->subHours(1),
            ]
        );

        // Order 2: In-Progress Lori Cheese
        $prodOrder2 = ProductionOrder::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_number' => "PRD-{$year}-000002"],
            [
                'recipe_id' => $recipeCheese->id,
                'product_id' => $p2->id,
                'product_variant_id' => null,
                'source_warehouse_id' => $whMain->id,
                'target_warehouse_id' => $whCold->id,
                'user_id' => $owner->id,
                'status' => 'in_progress',
                'planned_quantity' => 30.0,
                'actual_quantity' => 0.0,
                'waste_quantity' => 0.0,
                'unit_cost' => 0.0,
                'total_cost' => 66000.0,
                'planned_start_date' => now()->toDateString(),
                'started_at' => now()->subHours(2),
                'notes' => 'Պանրի խմբաքանակի մշակում պաստերիզացիայի փուլում',
            ]
        );

        ProductionOrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'production_order_id' => $prodOrder2->id, 'product_id' => $pMilk->id],
            [
                'planned_quantity' => 300.0,
                'consumed_quantity' => 300.0,
                'unit_cost' => 220.0,
                'total_cost' => 66000.0,
            ]
        );

        // =========================================================================
        // 17. Phase 3: ISO 22000 / HACCP Quality Assurance Inspection
        // =========================================================================
        $qaInspection = QualityInspection::firstOrCreate(
            ['tenant_id' => $tenant->id, 'inspection_number' => "QA-{$year}-000001"],
            [
                'production_order_id' => $prodOrder1->id,
                'inspector_id' => $owner->id,
                'status' => 'passed',
                'standard_applied' => 'ISO 22000:2018 / HACCP',
                'overall_score' => 100.0,
                'notes' => 'Թխման պարամետրերը և մանրէաբանական սահմանները համապատասխանում են ISO 22000 ստանդարտին:',
                'inspected_at' => now()->subHours(1)->subMinutes(15),
            ]
        );

        QualityInspectionItem::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'quality_inspection_id' => $qaInspection->id,
                'critical_control_point' => 'CCP-1',
            ],
            [
                'parameter_name' => 'Թոնրի թխման ջերմաստիճան (Baking Temperature)',
                'target_value' => '240.0',
                'min_value' => 230.0,
                'max_value' => 255.0,
                'actual_value' => '242.0',
                'unit' => '°C',
                'is_passed' => true,
            ]
        );

        QualityInspectionItem::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'quality_inspection_id' => $qaInspection->id,
                'critical_control_point' => 'CCP-2',
            ],
            [
                'parameter_name' => 'Խոնավության պարունակություն (Moisture Content)',
                'target_value' => '38.0',
                'min_value' => 35.0,
                'max_value' => 42.0,
                'actual_value' => '38.4',
                'unit' => '%',
                'is_passed' => true,
            ]
        );

        QualityInspectionItem::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'quality_inspection_id' => $qaInspection->id,
                'critical_control_point' => 'CCP-3',
            ],
            [
                'parameter_name' => 'Մետաղորսիչ և օտար մարմիններ (Metal Detection)',
                'target_value' => 'Բացակայում է',
                'actual_value' => 'Մաքուր / Չի հայտնաբերվել',
                'unit' => 'ստուգում',
                'is_passed' => true,
            ]
        );

        // 17. Phase 4: POS Terminals
        $posKentron = PosTerminal::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'POS-KENTRON-01'],
            [
                'branch_id' => $branchMain->id,
                'warehouse_id' => $whMain->id,
                'name' => 'Kentron Main Front Desk #1',
                'device_uid' => 'POS-DEV-KTN-01',
                'is_active' => true,
            ]
        );

        $posKomitas = PosTerminal::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'POS-KOMITAS-01'],
            [
                'branch_id' => $branchKomitas->id,
                'warehouse_id' => $whMain->id,
                'name' => 'Komitas Store Cash Desk #1',
                'device_uid' => 'POS-DEV-KMT-01',
                'is_active' => true,
            ]
        );

        // 18. Phase 4: POS Shift Session & Cash Movement
        $posSession = PosSession::firstOrCreate(
            ['tenant_id' => $tenant->id, 'session_number' => "SES-{$year}-000001"],
            [
                'pos_terminal_id' => $posKentron->id,
                'cashier_id' => $owner->id,
                'status' => 'open',
                'opening_cash' => 25000.00,
                'opened_at' => now()->startOfDay()->addHours(8),
                'notes' => 'Առավոտյան հերթափոխի բացում (Morning shift opening float)',
            ]
        );

        PosCashMovement::firstOrCreate(
            ['tenant_id' => $tenant->id, 'pos_session_id' => $posSession->id, 'reason' => 'Մանրադրամի համալրում (Cash float top-up)'],
            [
                'user_id' => $owner->id,
                'type' => 'cash_in',
                'amount' => 10000.00,
            ]
        );

        // 19. Phase 4: Completed POS Order with Receipt
        $posOrder = Order::firstOrCreate(
            ['tenant_id' => $tenant->id, 'receipt_number' => "REC-{$year}-000001"],
            [
                'order_number' => "ORD-{$year}-000003",
                'branch_id' => $branchMain->id,
                'pos_terminal_id' => $posKentron->id,
                'pos_session_id' => $posSession->id,
                'customer_id' => $customer->id,
                'status' => 'delivered',
                'source' => 'pos',
                'delivery_type' => 'pickup',
                'subtotal' => 4500.00,
                'discount' => 0.00,
                'delivery_fee' => 0.00,
                'tax' => 0.00,
                'total' => 4500.00,
                'currency' => 'AMD',
                'payment_status' => 'paid',
                'placed_at' => now()->subHours(2),
                'delivered_at' => now()->subHours(2),
            ]
        );

        OrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_id' => $posOrder->id, 'product_id' => $p1->id],
            [
                'product_name' => 'Թոնրի Մատնաքաշ Ավանդական',
                'product_sku' => 'BREAD-MATNAKASH',
                'quantity' => 5.0,
                'unit_price' => 300.00,
                'discount' => 0.00,
                'total' => 1500.00,
                'created_at' => now()->subHours(2),
            ]
        );

        OrderItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_id' => $posOrder->id, 'product_id' => $p2->id],
            [
                'product_name' => 'Լոռի Պանիր (Տնական)',
                'product_sku' => 'CHEESE-LORI',
                'quantity' => 1.0,
                'unit_price' => 3000.00,
                'discount' => 0.00,
                'total' => 3000.00,
                'created_at' => now()->subHours(2),
            ]
        );

        PaymentTransaction::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_id' => $posOrder->id, 'transaction_id' => "TX-TELCELL-{$year}-001"],
            [
                'pos_session_id' => $posSession->id,
                'gateway' => 'telcell',
                'payment_method' => 'qr',
                'amount' => 4500.00,
                'currency' => 'AMD',
                'status' => 'successful',
                'gateway_response' => ['method' => 'telcell_wallet', 'rrn' => '9876543210'],
                'paid_at' => now()->subHours(2),
            ]
        );

        // 20. Phase 4: Delivery Fleet Drivers
        $driver1 = DeliveryDriver::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '+37498112233'],
            [
                'user_id' => $owner->id,
                'first_name' => 'Դավիթ',
                'last_name' => 'Սահակյան',
                'vehicle_type' => 'car',
                'license_plate' => '36AA123',
                'status' => 'available',
                'is_active' => true,
            ]
        );

        $driver2 = DeliveryDriver::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '+37494445566'],
            [
                'first_name' => 'Նարեկ',
                'last_name' => 'Դանիելյան',
                'vehicle_type' => 'motorcycle',
                'license_plate' => '77BB456',
                'status' => 'on_delivery',
                'is_active' => true,
            ]
        );

        // 21. Phase 4: Delivery Shipment & Proof of Delivery (POD)
        $shipment = DeliveryShipment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'shipment_number' => "DLV-{$year}-000001"],
            [
                'order_id' => $order1->id,
                'delivery_driver_id' => $driver2->id,
                'status' => 'delivered',
                'delivery_address' => 'Երևան, Սայաթ-Նովա պող. 10, բն. 18',
                'recipient_name' => 'Մարիամ Պողոսյան',
                'recipient_phone' => '+37493223344',
                'scheduled_slot_start' => now()->subHours(3),
                'scheduled_slot_end' => now()->subHours(1),
                'cod_amount' => 12400.00,
                'cod_collected' => 12400.00,
                'dispatched_at' => now()->subHours(3),
                'delivered_at' => now()->subHours(2),
                'notes' => 'Առաքումն ավարտված է հաճախորդի ստորագրությամբ (POD)',
            ]
        );

        DeliveryProof::firstOrCreate(
            ['tenant_id' => $tenant->id, 'delivery_shipment_id' => $shipment->id],
            [
                'received_by_name' => 'Մարիամ Պողոսյան',
                'signature_url' => 'https://storage.erplannet.am/proofs/sig-dlv-001.png',
                'photo_url' => 'https://storage.erplannet.am/proofs/photo-dlv-001.jpg',
                'latitude' => 40.1811,
                'longitude' => 44.5136,
                'delivered_at' => now()->subHours(2),
                'notes' => 'Առձեռն հանձնում / Կանխիկ վճարում տեղում',
            ]
        );
    }
}
