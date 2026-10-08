<?php

use App\Domain\Billing\Models\Plan;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\CRM\Models\Customer;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Procurement\Models\PurchaseOrder;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Quality\Models\QualityInspection;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\Resolvers\TenantResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request, TenantResolver $resolver) {
    $currentTenant = $resolver->resolve($request);

    $tenants = Tenant::with(['subscriptions.plan', 'domains'])->get();
    $plans = Plan::with('features')->get();

    $stats = [
        'tenants_count' => Tenant::count(),
        'plans_count' => Plan::count(),
        'branches_count' => Branch::withoutGlobalScopes()->count(),
        'warehouses_count' => Warehouse::withoutGlobalScopes()->count(),
        'suppliers_count' => Supplier::withoutGlobalScopes()->count(),
        'products_count' => Product::withoutGlobalScopes()->count(),
        'orders_count' => Order::withoutGlobalScopes()->count(),
        'purchase_orders_count' => PurchaseOrder::withoutGlobalScopes()->count(),
        'recipes_count' => Recipe::withoutGlobalScopes()->count(),
        'production_orders_count' => ProductionOrder::withoutGlobalScopes()->count(),
        'quality_inspections_count' => QualityInspection::withoutGlobalScopes()->count(),
        'total_revenue' => Order::withoutGlobalScopes()->sum('total'),
    ];

    $demoTenant = Tenant::where('slug', 'gourmet')->first();
    $branches = $demoTenant ? Branch::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->get() : collect();
    $warehouses = $demoTenant ? Warehouse::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with('branch')->get() : collect();
    $suppliers = $demoTenant ? Supplier::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->get() : collect();
    $products = $demoTenant ? Product::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['category', 'unit'])->get() : collect();
    $customers = $demoTenant ? Customer::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->get() : collect();
    $orders = $demoTenant ? Order::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['items', 'customer'])->latest()->get() : collect();
    $batches = $demoTenant ? StockBatch::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['product', 'warehouse'])->get() : collect();
    $purchaseOrders = $demoTenant ? PurchaseOrder::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['supplier', 'warehouse', 'items.product'])->latest()->get() : collect();
    $recipes = $demoTenant ? Recipe::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['product', 'items.product', 'yieldUnit'])->get() : collect();
    $productionOrders = $demoTenant ? ProductionOrder::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['product', 'recipe', 'items.product', 'inspections'])->latest()->get() : collect();
    $qualityInspections = $demoTenant ? QualityInspection::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['productionOrder.product', 'inspector', 'items'])->latest()->get() : collect();

    return view('welcome', compact(
        'currentTenant',
        'tenants',
        'plans',
        'stats',
        'demoTenant',
        'branches',
        'warehouses',
        'suppliers',
        'products',
        'customers',
        'orders',
        'batches',
        'purchaseOrders',
        'recipes',
        'productionOrders',
        'qualityInspections'
    ));
});
