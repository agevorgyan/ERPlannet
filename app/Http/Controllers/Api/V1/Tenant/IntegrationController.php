<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Integration\Models\TenantIntegration;
use App\Domain\Integration\Services\IntegrationManager;
use App\Domain\Integration\Services\WooCommerceSyncService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function __construct(
        protected IntegrationManager $integrationManager,
        protected WooCommerceSyncService $wooCommerceSyncService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $integrations = TenantIntegration::where('tenant_id', $tenantId)
            ->latest()
            ->get()
            ->map(fn (TenantIntegration $i) => $this->formatIntegration($i));

        return response()->json([
            'success' => true,
            'data' => $integrations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $validated = $request->validate([
            'provider' => 'required|string|in:woocommerce,telegram,nikita_sms,mobipace_sms,armenian_software',
            'name' => 'required|string|max:150',
            'credentials' => 'required|array',
            'settings' => 'nullable|array',
        ]);

        $integration = new TenantIntegration([
            'tenant_id' => $tenantId,
            'provider' => $validated['provider'],
            'name' => $validated['name'],
            'status' => 'active',
            'settings' => $validated['settings'] ?? [],
        ]);
        $integration->setCredentials($validated['credentials']);
        $integration->save();

        return response()->json([
            'success' => true,
            'message' => 'Integration connected successfully.',
            'data' => $this->formatIntegration($integration),
        ], 201);
    }

    public function show(TenantIntegration $integration): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatIntegration($integration),
        ]);
    }

    public function update(Request $request, TenantIntegration $integration): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:150',
            'status' => 'nullable|string|in:active,inactive',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        if (isset($validated['name'])) {
            $integration->name = $validated['name'];
        }
        if (isset($validated['status'])) {
            $integration->status = $validated['status'];
        }
        if (isset($validated['settings'])) {
            $integration->settings = $validated['settings'];
        }
        if (! empty($validated['credentials'])) {
            $integration->setCredentials($validated['credentials']);
        }

        $integration->save();

        return response()->json([
            'success' => true,
            'message' => 'Integration updated successfully.',
            'data' => $this->formatIntegration($integration),
        ]);
    }

    public function destroy(TenantIntegration $integration): JsonResponse
    {
        $integration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Integration disconnected.',
        ]);
    }

    public function testConnection(TenantIntegration $integration): JsonResponse
    {
        $driver = $this->integrationManager->driverFor($integration);
        $result = $driver->testConnection($integration);

        if ($result['success']) {
            $integration->update(['status' => 'active', 'last_error' => null]);
        } else {
            $integration->update(['status' => 'error', 'last_error' => $result['message']]);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'details' => $result['details'] ?? null,
        ]);
    }

    public function syncProducts(TenantIntegration $integration): JsonResponse
    {
        $result = $this->wooCommerceSyncService->syncProducts($integration);

        return response()->json([
            'success' => true,
            'message' => "Synced {$result['processed']} products to WooCommerce.",
            'data' => $result,
        ]);
    }

    public function syncStock(TenantIntegration $integration): JsonResponse
    {
        $result = $this->wooCommerceSyncService->syncStock($integration);

        return response()->json([
            'success' => true,
            'message' => "Pushed stock balances for {$result['processed']} products to WooCommerce.",
            'data' => $result,
        ]);
    }

    public function syncOrders(TenantIntegration $integration): JsonResponse
    {
        $result = $this->wooCommerceSyncService->syncOrders($integration);

        return response()->json([
            'success' => true,
            'message' => "Imported {$result['imported']} orders from WooCommerce ({$result['skipped']} already synced).",
            'data' => $result,
        ]);
    }

    public function logs(TenantIntegration $integration): JsonResponse
    {
        $logs = $integration->syncLogs()->latest('created_at')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Format integration and mask sensitive credentials for client presentation.
     */
    protected function formatIntegration(TenantIntegration $i): array
    {
        $creds = $i->getCredentials();
        $maskedCreds = [];
        foreach ($creds as $key => $val) {
            $maskedCreds[$key] = is_string($val) && strlen($val) > 4
                ? substr($val, 0, 3).'••••••••'.substr($val, -3)
                : '••••';
        }

        return [
            'id' => $i->id,
            'tenant_id' => $i->tenant_id,
            'provider' => $i->provider,
            'name' => $i->name,
            'status' => $i->status,
            'credentials_masked' => $maskedCreds,
            'settings' => $i->settings,
            'last_sync_at' => $i->last_sync_at?->toISOString(),
            'last_error' => $i->last_error,
            'created_at' => $i->created_at?->toISOString(),
        ];
    }
}
