<?php

namespace App\Http\Controllers\Api\V1\Tenant\POS;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\POS\Models\PosTerminal;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosTerminalController extends Controller
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PosTerminal::with(['branch', 'warehouse', 'activeSession.cashier']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $terminals = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $terminals->items(),
            'meta' => [
                'current_page' => $terminals->currentPage(),
                'last_page' => $terminals->lastPage(),
                'total' => $terminals->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->entitlements->assertCan('feature.pos');
        $this->entitlements->assertCan('limit.pos_terminals', 1);

        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'device_uid' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $tenant = $this->tenantContext->getTenant();

        $terminal = PosTerminal::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'device_uid' => $validated['device_uid'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'POS Terminal created successfully.',
            'data' => $terminal->load(['branch', 'warehouse']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $terminal = PosTerminal::with(['branch', 'warehouse', 'activeSession.cashier'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $terminal,
        ]);
    }
}
