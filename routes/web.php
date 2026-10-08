<?php

use App\Domain\Billing\Models\Plan;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\CRM\Models\Customer;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
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
        'products_count' => Product::withoutGlobalScopes()->count(),
        'orders_count' => Order::withoutGlobalScopes()->count(),
        'customers_count' => Customer::withoutGlobalScopes()->count(),
        'total_revenue' => Order::withoutGlobalScopes()->sum('total'),
    ];

    $demoTenant = Tenant::where('slug', 'gourmet')->first();
    $branches = $demoTenant ? Branch::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->get() : collect();
    $products = $demoTenant ? Product::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['category', 'unit'])->get() : collect();
    $customers = $demoTenant ? Customer::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->get() : collect();
    $orders = $demoTenant ? Order::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['items', 'customer'])->latest()->get() : collect();

    return view('welcome', compact(
        'currentTenant',
        'tenants',
        'plans',
        'stats',
        'demoTenant',
        'branches',
        'products',
        'customers',
        'orders'
    ));
});
