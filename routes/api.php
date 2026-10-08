<?php

use App\Http\Controllers\Api\V1\Platform\PlanManagementController;
use App\Http\Controllers\Api\V1\Platform\PlatformAuthController;
use App\Http\Controllers\Api\V1\Platform\TenantManagementController;
use App\Http\Controllers\Api\V1\Tenant\BranchController;
use App\Http\Controllers\Api\V1\Tenant\Catalog\CategoryController;
use App\Http\Controllers\Api\V1\Tenant\Catalog\ProductController;
use App\Http\Controllers\Api\V1\Tenant\Catalog\UnitController;
use App\Http\Controllers\Api\V1\Tenant\CRM\CustomerController;
use App\Http\Controllers\Api\V1\Tenant\Manufacturing\ProductionOrderController;
use App\Http\Controllers\Api\V1\Tenant\Manufacturing\RecipeController;
use App\Http\Controllers\Api\V1\Tenant\PaymentController;
use App\Http\Controllers\Api\V1\Tenant\Procurement\PurchaseOrderController;
use App\Http\Controllers\Api\V1\Tenant\Procurement\SupplierController;
use App\Http\Controllers\Api\V1\Tenant\Quality\QualityInspectionController;
use App\Http\Controllers\Api\V1\Tenant\RoleController;
use App\Http\Controllers\Api\V1\Tenant\Sales\OrderController;
use App\Http\Controllers\Api\V1\Tenant\SubscriptionController;
use App\Http\Controllers\Api\V1\Tenant\TenantAuthController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\StockBatchController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\StockLevelController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\StockMovementController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\StockTransferController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'php_version' => PHP_VERSION,
        ]);
    });

    // Public translations endpoint
    Route::get('/translations/{locale}', function (string $locale) {
        $allowed = ['hy', 'en', 'ru'];
        $lang = in_array($locale, $allowed) ? $locale : 'hy';
        $path = base_path("lang/{$lang}.json");

        if (file_exists($path)) {
            $content = json_decode(file_get_contents($path), true);
            return response()->json([
                'success' => true,
                'locale' => $lang,
                'translations' => $content,
            ]);
        }

        return response()->json(['success' => false, 'error' => 'Locale not found'], 404);
    });

    /*
    |--------------------------------------------------------------------------
    | PLATFORM LEVEL ROUTES (Superadmins & Platform Management)
    |--------------------------------------------------------------------------
    */
    Route::prefix('platform')->group(function () {
        Route::post('/auth/login', [PlatformAuthController::class, 'login']);

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/auth/me', [PlatformAuthController::class, 'me']);
            Route::post('/auth/logout', [PlatformAuthController::class, 'logout']);

            // Tenant administration
            Route::get('/tenants', [TenantManagementController::class, 'index']);
            Route::post('/tenants', [TenantManagementController::class, 'store']);
            Route::get('/tenants/{id}', [TenantManagementController::class, 'show']);
            Route::post('/tenants/{id}/suspend', [TenantManagementController::class, 'suspend']);
            Route::post('/tenants/{id}/activate', [TenantManagementController::class, 'activate']);

            // SaaS Plans & Features
            Route::get('/plans', [PlanManagementController::class, 'index']);
            Route::post('/plans', [PlanManagementController::class, 'store']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | TENANT LEVEL ROUTES (SaaS Customers & Company Staff)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['tenant.identify', 'tenant.active'])->group(function () {
        // Tenant Auth
        Route::post('/auth/login', [TenantAuthController::class, 'login']);

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/auth/me', [TenantAuthController::class, 'me']);
            Route::post('/auth/logout', [TenantAuthController::class, 'logout']);

            // Roles & RBAC
            Route::apiResource('roles', RoleController::class);

            // Branches
            Route::apiResource('branches', BranchController::class);

            // Warehouses
            Route::apiResource('warehouses', WarehouseController::class);

            // Inventory & Stock
            Route::get('/inventory/levels', [StockLevelController::class, 'index']);
            Route::get('/inventory/batches', [StockBatchController::class, 'index']);
            Route::get('/inventory/movements', [StockMovementController::class, 'index']);
            Route::post('/inventory/adjust', [StockMovementController::class, 'adjust']);
            Route::apiResource('inventory/transfers', StockTransferController::class)->only(['index', 'store', 'show']);
            Route::post('/inventory/transfers/{id}/ship', [StockTransferController::class, 'ship']);
            Route::post('/inventory/transfers/{id}/receive', [StockTransferController::class, 'receive']);

            // Procurement & Suppliers
            Route::apiResource('suppliers', SupplierController::class);
            Route::apiResource('purchase-orders', PurchaseOrderController::class)->only(['index', 'store', 'show']);
            Route::post('/purchase-orders/{id}/submit', [PurchaseOrderController::class, 'submit']);
            Route::post('/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
            Route::post('/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);

            // Catalog
            Route::apiResource('categories', CategoryController::class);
            Route::get('/units', [UnitController::class, 'index']);
            Route::post('/units', [UnitController::class, 'store']);
            Route::apiResource('products', ProductController::class);

            // CRM & Customers
            Route::apiResource('customers', CustomerController::class);
            Route::post('/customers/{id}/addresses', [CustomerController::class, 'addAddress']);

            // Manufacturing & Recipes (BOM)
            Route::apiResource('recipes', RecipeController::class);

            // Production Orders
            Route::apiResource('production-orders', ProductionOrderController::class)->only(['index', 'store', 'show']);
            Route::post('/production-orders/{id}/start', [ProductionOrderController::class, 'start']);
            Route::post('/production-orders/{id}/complete', [ProductionOrderController::class, 'complete']);
            Route::post('/production-orders/{id}/cancel', [ProductionOrderController::class, 'cancel']);

            // Quality Assurance & ISO 22000
            Route::apiResource('quality-inspections', QualityInspectionController::class)->only(['index', 'store', 'show']);

            // Sales & Orders
            Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
            Route::post('/orders/{id}/status', [OrderController::class, 'transitionStatus']);
            Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

            // Subscription & Entitlements
            Route::get('/subscription', [SubscriptionController::class, 'show']);

            // Payments
            Route::get('/payments/gateways', [PaymentController::class, 'availableGateways']);
            Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
        });
    });
});
