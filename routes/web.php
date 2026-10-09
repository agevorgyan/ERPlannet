<?php

use App\Domain\Billing\Models\Plan;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\IAM\Models\Role;
use App\Domain\IAM\Models\User;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\POS\Models\PosSession;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\Procurement\Models\PurchaseOrder;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Quality\Models\QualityInspection;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\Warehouse;
use App\Http\Controllers\AuthController;
use App\Infrastructure\MultiTenancy\Resolvers\TenantResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================================================
// Authentication & Partner Onboarding Routes
// ============================================================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/password-reset', [AuthController::class, 'showPasswordReset'])->name('password.reset');
Route::get('/forgot-password', fn () => redirect()->route('password.reset'));
Route::post('/password-reset/request', [AuthController::class, 'requestPasswordReset'])->name('password.request');
Route::post('/password-reset/confirm', [AuthController::class, 'confirmPasswordReset'])->name('password.confirm');
Route::post('/password-reset/lookup-account', [AuthController::class, 'lookupAccount'])->name('password.lookup');

// ============================================================================
// Main Application Views
// ============================================================================
Route::get('/{view?}', function (Request $request, TenantResolver $resolver, ?string $view = null) {
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
        'pos_terminals_count' => PosTerminal::withoutGlobalScopes()->count(),
        'pos_sessions_count' => PosSession::withoutGlobalScopes()->count(),
        'delivery_drivers_count' => DeliveryDriver::withoutGlobalScopes()->count(),
        'delivery_shipments_count' => DeliveryShipment::withoutGlobalScopes()->count(),
        'payment_transactions_count' => PaymentTransaction::withoutGlobalScopes()->count(),
        'total_revenue' => Order::withoutGlobalScopes()->sum('total'),
    ];

    $demoTenant = Tenant::where('slug', 'gourmet')->first() ?? $tenants->first();
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
    $posTerminals = $demoTenant ? PosTerminal::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['branch', 'warehouse', 'activeSession'])->get() : collect();
    $posSessions = $demoTenant ? PosSession::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['terminal', 'cashier', 'cashMovements'])->latest()->get() : collect();
    $deliveryDrivers = $demoTenant ? DeliveryDriver::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with('user')->get() : collect();
    $deliveryShipments = $demoTenant ? DeliveryShipment::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with(['driver', 'order', 'proof'])->latest()->get() : collect();
    $paymentTransactions = $demoTenant ? PaymentTransaction::withoutGlobalScopes()->where('tenant_id', $demoTenant->id)->with('order')->latest()->get() : collect();

    // IAM Users and Roles
    $roles = Role::with('permissions')->get();
    $users = $demoTenant ? User::where('tenant_id', $demoTenant->id)->with('roles')->get() : collect();

    // Catalog Categories & Units for dynamic forms
    $categories = $demoTenant ? Category::where('tenant_id', $demoTenant->id)->get() : collect();
    $units = $demoTenant ? Unit::where('tenant_id', $demoTenant->id)->get() : collect();

    // Generate valid session token for browser API execution
    $currentUser = auth()->user() ?: ($demoTenant ? User::where('tenant_id', $demoTenant->id)->first() : null);
    $apiToken = $currentUser ? $currentUser->createToken('browser_live_token', ['tenant:*'])->plainTextToken : null;

    $activeView = $view ?: 'dashboard';

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
        'categories',
        'units',
        'customers',
        'orders',
        'batches',
        'purchaseOrders',
        'recipes',
        'productionOrders',
        'qualityInspections',
        'posTerminals',
        'posSessions',
        'deliveryDrivers',
        'deliveryShipments',
        'paymentTransactions',
        'roles',
        'users',
        'currentUser',
        'apiToken',
        'activeView'
    ));
})->where('view', '^(?!api/).*$');
