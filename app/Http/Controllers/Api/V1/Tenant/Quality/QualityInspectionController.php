<?php

namespace App\Http\Controllers\Api\V1\Tenant\Quality;

use App\Domain\Quality\Actions\RecordQualityInspectionAction;
use App\Domain\Quality\Models\QualityInspection;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityInspectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = QualityInspection::with(['productionOrder.product', 'inspector']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('production_order_id')) {
            $query->where('production_order_id', $request->query('production_order_id'));
        }

        $inspections = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $inspections->items(),
            'meta' => [
                'current_page' => $inspections->currentPage(),
                'last_page' => $inspections->lastPage(),
                'total' => $inspections->total(),
            ],
        ]);
    }

    public function store(Request $request, RecordQualityInspectionAction $action): JsonResponse
    {
        $validated = $request->validate([
            'production_order_id' => ['required', 'uuid', 'exists:production_orders,id'],
            'standard_applied' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.parameter_name' => ['required', 'string', 'max:150'],
            'items.*.critical_control_point' => ['nullable', 'string', 'max:50'],
            'items.*.target_value' => ['nullable', 'string', 'max:100'],
            'items.*.min_value' => ['nullable', 'numeric'],
            'items.*.max_value' => ['nullable', 'numeric'],
            'items.*.actual_value' => ['required', 'string', 'max:100'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
            'items.*.is_passed' => ['nullable', 'boolean'],
            'items.*.deviation_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $inspection = $action->execute(
            productionOrderId: $validated['production_order_id'],
            inspectorId: $request->user()->id,
            items: $validated['items'],
            standardApplied: $validated['standard_applied'] ?? 'ISO 22000:2018',
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Quality inspection recorded successfully.',
            'data' => $inspection,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $inspection = QualityInspection::with([
            'productionOrder.product',
            'inspector',
            'items',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $inspection,
        ]);
    }
}
