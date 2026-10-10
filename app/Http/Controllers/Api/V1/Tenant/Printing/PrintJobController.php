<?php

namespace App\Http\Controllers\Api\V1\Tenant\Printing;

use App\Domain\Printing\Models\PrintJob;
use App\Domain\Printing\Services\PrintJobManager;
use App\Domain\Sales\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintJobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PrintJob::with(['printer', 'workstation', 'order', 'template', 'requester']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->query('order_id'));
        }

        $jobs = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $jobs->currentPage(),
                    'per_page' => $jobs->perPage(),
                    'total' => $jobs->total(),
                    'last_page' => $jobs->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request, PrintJobManager $manager): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['nullable', 'uuid', 'exists:orders,id'],
            'delivery_note_id' => ['nullable', 'uuid', 'exists:b2b_delivery_notes,id'],
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'printer_id' => ['nullable', 'uuid', 'exists:printers,id'],
            'workstation_id' => ['nullable', 'uuid', 'exists:pos_workstations,id'],
            'document_type' => ['nullable', 'string'],
            'template_id' => ['nullable', 'uuid', 'exists:document_templates,id'],
            'copies' => ['nullable', 'integer', 'min:1', 'max:10'],
            'idempotency_key' => ['nullable', 'string', 'max:255'],
        ]);

        $job = $manager->createPrintJob($validated, $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => 'Print job queued successfully.',
            'data' => $job,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $job = PrintJob::with(['printer', 'workstation', 'order', 'template', 'events', 'requester'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    public function reprint(Request $request, string $id, PrintJobManager $manager): JsonResponse
    {
        $job = PrintJob::findOrFail($id);

        $reprinted = $manager->reprint($job, $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => "Reprint #{$reprinted->reprint_count} dispatched successfully.",
            'data' => $reprinted,
        ]);
    }

    public function printOrder(Request $request, string $orderId, string $documentType, PrintJobManager $manager): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        $job = $manager->createPrintJob([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'document_type' => $documentType,
            'copies' => $request->integer('copies', 1),
            'idempotency_key' => $request->header('X-Idempotency-Key'),
        ], $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => "Order document '{$documentType}' generated successfully.",
            'data' => [
                'job_id' => $job->id,
                'status' => $job->status,
                'html' => $job->payload_rendered,
                'reprint_count' => $job->reprint_count,
            ],
        ]);
    }
}
