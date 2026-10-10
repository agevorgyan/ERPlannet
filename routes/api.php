<?php

use App\Http\Controllers\Api\V1\Platform\PlanManagementController;
use App\Http\Controllers\Api\V1\Platform\PlatformAuthController;
use App\Http\Controllers\Api\V1\Platform\TenantManagementController;
use App\Http\Controllers\Api\V1\Tenant\AccountingExportController;
use App\Http\Controllers\Api\V1\Tenant\BranchController;
use App\Http\Controllers\Api\V1\Tenant\Catalog\CategoryController;
use App\Http\Controllers\Api\V1\Tenant\Catalog\IngredientController;
use App\Http\Controllers\Api\V1\Tenant\Catalog\ProductController;
use App\Http\Controllers\Api\V1\Tenant\Catalog\UnitController;
use App\Http\Controllers\Api\V1\Tenant\CRM\CustomerController;
use App\Http\Controllers\Api\V1\Tenant\Delivery\CodSettlementController;
use App\Http\Controllers\Api\V1\Tenant\Delivery\DeliveryDriverController;
use App\Http\Controllers\Api\V1\Tenant\Delivery\DeliveryDriverShiftController;
use App\Http\Controllers\Api\V1\Tenant\Delivery\DeliveryShipmentController;
use App\Http\Controllers\Api\V1\Tenant\InboundWebhookController;
use App\Http\Controllers\Api\V1\Tenant\IntegrationController;
use App\Http\Controllers\Api\V1\Tenant\Manufacturing\ProductionOrderController;
use App\Http\Controllers\Api\V1\Tenant\Manufacturing\RecipeController;
use App\Http\Controllers\Api\V1\Tenant\MediaController;
use App\Http\Controllers\Api\V1\Tenant\NotificationTemplateController;
use App\Http\Controllers\Api\V1\Tenant\PaymentController;
use App\Http\Controllers\Api\V1\Tenant\Payments\OrderPaymentController;
use App\Http\Controllers\Api\V1\Tenant\POS\PosCheckoutController;
use App\Http\Controllers\Api\V1\Tenant\POS\PosRefundController;
use App\Http\Controllers\Api\V1\Tenant\POS\PosSessionController;
use App\Http\Controllers\Api\V1\Tenant\POS\PosTerminalController;
use App\Http\Controllers\Api\V1\Tenant\Printing\DocumentTemplateController;
use App\Http\Controllers\Api\V1\Tenant\Printing\PrinterController;
use App\Http\Controllers\Api\V1\Tenant\Printing\PrintJobController;
use App\Http\Controllers\Api\V1\Tenant\Procurement\PurchaseOrderController;
use App\Http\Controllers\Api\V1\Tenant\Procurement\SupplierController;
use App\Http\Controllers\Api\V1\Tenant\Quality\QualityInspectionController;
use App\Http\Controllers\Api\V1\Tenant\RoleController;
use App\Http\Controllers\Api\V1\Tenant\Sales\B2bDeliveryNoteController;
use App\Http\Controllers\Api\V1\Tenant\Sales\OrderController;
use App\Http\Controllers\Api\V1\Tenant\Sales\XmlImportController;
use App\Http\Controllers\Api\V1\Tenant\SubscriptionController;
use App\Http\Controllers\Api\V1\Tenant\TenantAuthController;
use App\Http\Controllers\Api\V1\Tenant\UserController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\StockBatchController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\StockLevelController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\StockMovementController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\StockTransferController;
use App\Http\Controllers\Api\V1\Tenant\Warehouse\WarehouseController;
use App\Http\Controllers\Api\V1\Tenant\WebhookSubscriptionController;
use App\Http\Controllers\AuthController;
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

    // Inbound Webhooks Ingress (WooCommerce, etc. - Verified by HMAC)
    Route::post('/webhooks/ingress/woocommerce/{integrationId}', [InboundWebhookController::class, 'handleWooCommerce']);

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
    | PUBLIC AUTHENTICATION & ONBOARDING ENDPOINTS
    |--------------------------------------------------------------------------
    */
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthController::class, 'requestPasswordReset']);
    Route::post('/auth/reset-password', [AuthController::class, 'confirmPasswordReset']);
    Route::post('/auth/lookup-account', [AuthController::class, 'lookupAccount']);

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
            Route::apiResource('users', UserController::class);

            // Branches
            Route::apiResource('branches', BranchController::class);

            // Warehouses
            Route::apiResource('warehouses', WarehouseController::class);

            // Inventory & Stock
            Route::get('/inventory/levels', [StockLevelController::class, 'index']);
            Route::get('/inventory/batches', [StockBatchController::class, 'index']);
            Route::get('/inventory/movements', [StockMovementController::class, 'index']);
            Route::post('/inventory/adjust', [StockMovementController::class, 'adjust']);
            Route::post('/inventory/scrap', [StockMovementController::class, 'scrap']);
            Route::apiResource('inventory/transfers', StockTransferController::class)->only(['index', 'store', 'show']);
            Route::post('/inventory/transfers/{id}/ship', [StockTransferController::class, 'ship']);
            Route::post('/inventory/transfers/{id}/receive', [StockTransferController::class, 'receive']);

            // Procurement & Suppliers
            Route::post('/suppliers/{id}/toggle-suspend', [SupplierController::class, 'toggleSuspend']);
            Route::post('/suppliers/{id}/restore', [SupplierController::class, 'restore']);
            Route::delete('/suppliers/{id}/force', [SupplierController::class, 'forceDelete']);
            Route::apiResource('suppliers', SupplierController::class);
            Route::apiResource('purchase-orders', PurchaseOrderController::class)->only(['index', 'store', 'show']);
            Route::post('/purchase-orders/{id}/submit', [PurchaseOrderController::class, 'submit']);
            Route::post('/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
            Route::post('/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);

            // Catalog & Directory
            Route::post('/media/upload', [MediaController::class, 'upload']);
            Route::post('/ingredients/import-invoices', [IngredientController::class, 'importInvoices']);
            Route::apiResource('ingredients', IngredientController::class);
            Route::apiResource('categories', CategoryController::class);
            Route::get('/units', [UnitController::class, 'index']);
            Route::get('/products/{id}/technical-card', [ProductController::class, 'getTechnicalCard']);
            Route::post('/products/{id}/technical-card', [ProductController::class, 'saveTechnicalCard']);
            Route::post('/products/{id}/produce', [ProductController::class, 'produce']);
            Route::apiResource('products', ProductController::class);

            // CRM & Customers
            Route::get('/customers/sources', [CustomerController::class, 'sources']);
            Route::match(['get', 'post'], '/customers/check-duplicate', [CustomerController::class, 'checkDuplicate']);
            Route::post('/customers/merge', [CustomerController::class, 'merge']);
            Route::post('/customers/{id}/restore', [CustomerController::class, 'restore']);
            Route::get('/customers/{id}/timeline', [CustomerController::class, 'timeline']);
            Route::post('/customers/{id}/notes', [CustomerController::class, 'addNote']);
            Route::post('/customers/{id}/loyalty/adjust', [CustomerController::class, 'adjustLoyalty']);
            Route::post('/customers/{id}/addresses', [CustomerController::class, 'addAddress']);
            Route::apiResource('customers', CustomerController::class);

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
            Route::post('/orders/calculate-pricing', [OrderController::class, 'calculatePricing']);
            Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
            Route::match(['post', 'patch'], '/orders/{id}/status', [OrderController::class, 'transitionStatus']);
            Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
            Route::match(['post', 'patch'], '/orders/{id}/reschedule', [OrderController::class, 'reschedule']);
            Route::post('/orders/{id}/print/{documentType}', [PrintJobController::class, 'printOrder']);
            Route::post('/orders/{id}/delivery-notes', [B2bDeliveryNoteController::class, 'generateFromOrder']);

            // XML Import Center
            Route::post('/orders/xml-import/preview', [XmlImportController::class, 'preview']);
            Route::post('/orders/xml-import/confirm', [XmlImportController::class, 'confirm']);
            Route::get('/orders/xml-import/history', [XmlImportController::class, 'index']);
            Route::get('/orders/xml-import/{id}', [XmlImportController::class, 'show']);
            Route::post('/orders/xml-imports/preview', [XmlImportController::class, 'preview']);
            Route::post('/orders/xml-imports/confirm', [XmlImportController::class, 'confirm']);
            Route::get('/orders/xml-imports', [XmlImportController::class, 'index']);
            Route::get('/orders/xml-imports/{id}', [XmlImportController::class, 'show']);
            Route::post('/sales/xml-imports/preview', [XmlImportController::class, 'preview']);
            Route::post('/sales/xml-imports/confirm', [XmlImportController::class, 'confirm']);
            Route::get('/sales/xml-imports', [XmlImportController::class, 'index']);
            Route::get('/sales/xml-imports/{id}', [XmlImportController::class, 'show']);

            // B2B Delivery Notes / Накладная
            Route::get('/delivery-notes', [B2bDeliveryNoteController::class, 'index']);
            Route::post('/delivery-notes/generate', [B2bDeliveryNoteController::class, 'generateFromOrder']);
            Route::get('/delivery-notes/{id}', [B2bDeliveryNoteController::class, 'show']);
            Route::post('/delivery-notes/{id}/reprint', [B2bDeliveryNoteController::class, 'reprint']);
            Route::post('/delivery-notes/{id}/cancel', [B2bDeliveryNoteController::class, 'cancel']);
            Route::get('/delivery-notes/{id}/print', [B2bDeliveryNoteController::class, 'print']);

            Route::post('/sales/orders/{id}/delivery-notes', [B2bDeliveryNoteController::class, 'generateFromOrder']);
            Route::get('/sales/delivery-notes', [B2bDeliveryNoteController::class, 'index']);
            Route::post('/sales/delivery-notes/generate', [B2bDeliveryNoteController::class, 'generateFromOrder']);
            Route::get('/sales/delivery-notes/{id}', [B2bDeliveryNoteController::class, 'show']);
            Route::post('/sales/delivery-notes/{id}/reprint', [B2bDeliveryNoteController::class, 'reprint']);
            Route::post('/sales/delivery-notes/{id}/cancel', [B2bDeliveryNoteController::class, 'cancel']);
            Route::get('/sales/delivery-notes/{id}/print', [B2bDeliveryNoteController::class, 'print']);

            // Print Management & Document Designer
            Route::post('/printers/{id}/test-print', [PrinterController::class, 'testPrint']);
            Route::apiResource('printers', PrinterController::class);
            Route::post('/printing/printers/{id}/test-print', [PrinterController::class, 'testPrint']);
            Route::apiResource('printing/printers', PrinterController::class);

            Route::get('/document-templates/placeholders', [DocumentTemplateController::class, 'placeholders']);
            Route::post('/document-templates/{id}/publish', [DocumentTemplateController::class, 'publish']);
            Route::post('/document-templates/{id}/duplicate', [DocumentTemplateController::class, 'duplicate']);
            Route::post('/document-templates/{id}/preview', [DocumentTemplateController::class, 'preview']);
            Route::apiResource('document-templates', DocumentTemplateController::class);
            Route::post('/printing/templates/{id}/publish', [DocumentTemplateController::class, 'publish']);
            Route::post('/printing/templates/{id}/duplicate', [DocumentTemplateController::class, 'duplicate']);
            Route::post('/printing/templates/{id}/preview', [DocumentTemplateController::class, 'preview']);
            Route::apiResource('printing/templates', DocumentTemplateController::class);

            Route::get('/print-jobs', [PrintJobController::class, 'index']);
            Route::post('/print-jobs', [PrintJobController::class, 'store']);
            Route::get('/print-jobs/{id}', [PrintJobController::class, 'show']);
            Route::post('/print-jobs/{id}/reprint', [PrintJobController::class, 'reprint']);

            // Subscription & Entitlements
            Route::get('/subscription', [SubscriptionController::class, 'show']);

            // Billing Payments
            Route::get('/payments/gateways', [PaymentController::class, 'availableGateways']);
            Route::post('/payments/initiate', [PaymentController::class, 'initiate']);

            // POS (Point of Sale) & Cashier Shifts
            Route::apiResource('pos/terminals', PosTerminalController::class)->only(['index', 'store', 'show']);
            Route::post('/pos/sessions/open', [PosSessionController::class, 'open']);
            Route::get('/pos/sessions/current', [PosSessionController::class, 'current']);
            Route::post('/pos/sessions/cash-movement', [PosSessionController::class, 'cashMovement']);
            Route::post('/pos/sessions/close', [PosSessionController::class, 'close']);
            Route::get('/pos/sessions/{id}/z-report', [PosSessionController::class, 'zReport']);
            Route::post('/pos/checkout', [PosCheckoutController::class, 'checkout']);
            Route::post('/pos/orders/{id}/refund', [PosRefundController::class, 'refund']);
            Route::post('/pos/orders/{id}/void', [PosRefundController::class, 'void']);

            // Delivery Fleet & Dispatch Management
            Route::apiResource('delivery/drivers', DeliveryDriverController::class)->only(['index', 'store', 'show']);
            Route::post('/delivery/drivers/{id}/shifts/start', [DeliveryDriverShiftController::class, 'start']);
            Route::post('/delivery/driver-shifts/{id}/end', [DeliveryDriverShiftController::class, 'end']);
            Route::apiResource('delivery/shipments', DeliveryShipmentController::class)->only(['index', 'store', 'show']);
            Route::post('/delivery/shipments/{id}/assign', [DeliveryShipmentController::class, 'assign']);
            Route::post('/delivery/shipments/{id}/dispatch', [DeliveryShipmentController::class, 'dispatch']);
            Route::post('/delivery/shipments/{id}/complete', [DeliveryShipmentController::class, 'complete']);
            Route::post('/delivery/shipments/{id}/fail', [DeliveryShipmentController::class, 'fail']);
            Route::post('/delivery/shipments/{id}/return', [DeliveryShipmentController::class, 'return']);
            Route::post('/delivery/shipments/{id}/gps', [DeliveryShipmentController::class, 'gps']);

            // COD Settlements
            Route::get('/delivery/cod-settlements', [CodSettlementController::class, 'index']);
            Route::get('/delivery/cod-settlements/{id}', [CodSettlementController::class, 'show']);
            Route::post('/delivery/cod-settlements/{id}/submit', [CodSettlementController::class, 'submit']);
            Route::post('/delivery/cod-settlements/{id}/verify', [CodSettlementController::class, 'verify']);
            Route::post('/delivery/cod-settlements/{id}/settle', [CodSettlementController::class, 'settle']);

            // Order Payments & Transactions
            Route::post('/orders/{id}/payments', [OrderPaymentController::class, 'recordOrInitiate']);
            Route::post('/sales/orders/{id}/payments', [OrderPaymentController::class, 'recordOrInitiate']);
            Route::get('/orders/{id}/payments', [OrderPaymentController::class, 'transactions']);
            Route::get('/sales/orders/{id}/payments', [OrderPaymentController::class, 'transactions']);
            Route::post('/payments/{id}/refund', [OrderPaymentController::class, 'refund']);
            Route::post('/sales/orders/{orderId}/payments/{id}/refund', [OrderPaymentController::class, 'refund']);
            Route::post('/orders/{orderId}/payments/{id}/refund', [OrderPaymentController::class, 'refund']);
            Route::post('/payments/{id}/reconcile', [OrderPaymentController::class, 'reconcile']);

            // Phase 5: Integrations & External Ecosystem
            Route::apiResource('integrations', IntegrationController::class);
            Route::post('/integrations/{integration}/test-connection', [IntegrationController::class, 'testConnection']);
            Route::post('/integrations/{integration}/sync/products', [IntegrationController::class, 'syncProducts']);
            Route::post('/integrations/{integration}/sync/stock', [IntegrationController::class, 'syncStock']);
            Route::post('/integrations/{integration}/sync/orders', [IntegrationController::class, 'syncOrders']);
            Route::get('/integrations/{integration}/logs', [IntegrationController::class, 'logs']);

            // Webhook Subscriptions (Outbound)
            Route::get('/webhooks/subscriptions', [WebhookSubscriptionController::class, 'index']);
            Route::post('/webhooks/subscriptions', [WebhookSubscriptionController::class, 'store']);
            Route::delete('/webhooks/subscriptions/{subscription}', [WebhookSubscriptionController::class, 'destroy']);
            Route::get('/webhooks/deliveries', [WebhookSubscriptionController::class, 'deliveries']);
            Route::post('/webhooks/deliveries/{delivery}/retry', [WebhookSubscriptionController::class, 'retry']);

            // Notifications & Templates
            Route::get('/notifications/templates', [NotificationTemplateController::class, 'index']);
            Route::post('/notifications/templates', [NotificationTemplateController::class, 'store']);
            Route::put('/notifications/templates/{template}', [NotificationTemplateController::class, 'update']);
            Route::post('/notifications/test-send', [NotificationTemplateController::class, 'testSend']);

            // Accounting & Fiscal Data Export (1C & Armenian Software)
            Route::get('/accounting-export/1c/invoices', [AccountingExportController::class, 'exportInvoices']);
            Route::get('/accounting-export/as/data', [AccountingExportController::class, 'exportInventory']);
        });

        // Payment Webhooks
        Route::post('/payments/webhooks/{gateway}', [OrderPaymentController::class, 'webhook']);
    });
});
