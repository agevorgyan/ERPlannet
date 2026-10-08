<?php

namespace App\Http\Controllers\Api\V1\Tenant\Delivery;

use App\Domain\Delivery\Actions\SettleCourierCodAction;
use App\Domain\Delivery\Models\CodSettlement;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodSettlementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CodSettlement::with(['shipment', 'order', 'driver', 'cashier']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $settlements = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $settlements->items(),
            'meta' => [
                'current_page' => $settlements->currentPage(),
                'last_page' => $settlements->lastPage(),
                'total' => $settlements->total(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $settlement = CodSettlement::with(['shipment', 'order', 'driver', 'cashier'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $settlement,
        ]);
    }

    public function submit(Request $request, string $id, SettleCourierCodAction $action): JsonResponse
    {
        $validated = $request->validate([
            'submitted_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $settlement = $action->submitCash($id, (float) $validated['submitted_amount']);

        return response()->json([
            'success' => true,
            'message' => 'COD cash submitted by courier.',
            'data' => $settlement,
        ]);
    }

    public function verify(Request $request, string $id, SettleCourierCodAction $action): JsonResponse
    {
        $validated = $request->validate([
            'verified_amount' => ['required', 'numeric', 'min:0'],
            'discrepancy_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $settlement = $action->verifyCash(
            codSettlementId: $id,
            verifiedAmount: (float) $validated['verified_amount'],
            discrepancyReason: $validated['discrepancy_reason'] ?? null,
            cashierId: $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'COD cash verified with discrepancy tracking.',
            'data' => $settlement,
        ]);
    }

    public function settle(Request $request, string $id, SettleCourierCodAction $action): JsonResponse
    {
        $validated = $request->validate([
            'pos_session_id' => ['nullable', 'uuid', 'exists:pos_sessions,id'],
        ]);

        $settlement = $action->settleCash(
            codSettlementId: $id,
            posSessionId: $validated['pos_session_id'] ?? null,
            cashierId: $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'COD settlement finalized and deposited.',
            'data' => $settlement,
        ]);
    }
}
