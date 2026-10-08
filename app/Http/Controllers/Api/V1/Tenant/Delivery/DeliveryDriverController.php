<?php

namespace App\Http\Controllers\Api\V1\Tenant\Delivery;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryDriverController extends Controller
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = DeliveryDriver::with(['user']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->query('vehicle_type'));
        }

        $drivers = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $drivers->items(),
            'meta' => [
                'current_page' => $drivers->currentPage(),
                'last_page' => $drivers->lastPage(),
                'total' => $drivers->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->entitlements->assertCan('feature.delivery');
        $this->entitlements->assertCan('limit.delivery_drivers', 1);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'vehicle_type' => ['nullable', 'string', 'in:car,motorcycle,van,bicycle'],
            'license_plate' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $tenant = $this->tenantContext->getTenant();

        $driver = DeliveryDriver::create([
            'tenant_id' => $tenant->id,
            'user_id' => $validated['user_id'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'vehicle_type' => $validated['vehicle_type'] ?? 'car',
            'license_plate' => $validated['license_plate'] ?? null,
            'status' => 'available',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery driver registered successfully.',
            'data' => $driver,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $driver = DeliveryDriver::with(['user', 'shipments.order'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $driver,
        ]);
    }
}
