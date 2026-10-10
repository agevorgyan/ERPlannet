<?php

namespace App\Http\Controllers\Api\V1\Tenant\Sales;

use App\Domain\Printing\Services\DocumentTemplateRenderer;
use App\Domain\Sales\Models\B2bDeliveryNote;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Services\B2bDeliveryNoteService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class B2bDeliveryNoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = B2bDeliveryNote::with(['branch', 'order.customer', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->query('order_id'));
        }

        if ($request->filled('search')) {
            $term = trim($request->query('search'));
            $query->where(function ($q) use ($term) {
                $q->where('document_number', 'ilike', "%{$term}%")
                    ->orWhere('customer_name', 'ilike', "%{$term}%")
                    ->orWhere('customer_tax_id', 'ilike', "%{$term}%");
            });
        }

        $notes = $query->latest('document_date')->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $notes->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $notes->currentPage(),
                    'per_page' => $notes->perPage(),
                    'total' => $notes->total(),
                    'last_page' => $notes->lastPage(),
                ],
            ],
        ]);
    }

    public function generateFromOrder(Request $request, B2bDeliveryNoteService $service, ?string $id = null): JsonResponse
    {
        $orderId = $id ?? $request->route('id') ?? $request->input('order_id');
        if ($orderId) {
            $request->merge(['order_id' => $orderId]);
        }

        $validated = $request->validate([
            'order_id' => ['required', 'uuid', 'exists:orders,id'],
            'document_date' => ['nullable', 'date'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_tax_id' => ['nullable', 'string', 'max:50'],
            'recipient_legal_name' => ['nullable', 'string', 'max:255'],
            'recipient_tax_id' => ['nullable', 'string', 'max:50'],
            'delivery_address' => ['nullable', 'string'],
            'delivered_by' => ['nullable', 'string', 'max:150'],
            'delivered_by_name' => ['nullable', 'string', 'max:150'],
            'received_by' => ['nullable', 'string', 'max:150'],
            'received_by_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = Order::with(['items', 'customer', 'branch', 'responsibleEmployee'])->findOrFail($validated['order_id']);

        $note = $service->generateFromOrder($order, $validated, $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => "B2B Delivery Note {$note->document_number} generated successfully.",
            'data' => $note->load(['branch', 'order']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $note = B2bDeliveryNote::with(['branch', 'order.customer', 'creator'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $note,
        ]);
    }

    public function reprint(Request $request, string $id, B2bDeliveryNoteService $service): JsonResponse
    {
        $note = B2bDeliveryNote::findOrFail($id);
        $reprinted = $service->reprint($note, $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => "Delivery note marked reprinted (Reprint #{$reprinted->reprint_count}).",
            'data' => $reprinted,
        ]);
    }

    public function cancel(Request $request, string $id, B2bDeliveryNoteService $service): JsonResponse
    {
        $note = B2bDeliveryNote::findOrFail($id);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $cancelled = $service->cancel($note, $validated['reason'], $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => "Delivery note {$cancelled->document_number} has been cancelled.",
            'data' => $cancelled,
        ]);
    }

    public function print(string $id, DocumentTemplateRenderer $renderer, B2bDeliveryNoteService $service): JsonResponse
    {
        $note = B2bDeliveryNote::with(['branch', 'order.items', 'order.customer'])->findOrFail($id);

        // Record reprint if already issued
        if ($note->reprint_count > 0 || request()->boolean('record_reprint', true)) {
            $service->reprint($note, auth()->id());
        }

        $html = $renderer->render(
            template: [
                'document_type' => 'b2b_delivery_note',
                'paper_size' => 'a4',
                'layout_config' => [
                    'header' => ['title' => 'ԱՊՐԱՆՔԱԳԻՐ — ԲԵՌՆԱԳԻՐ / B2B DELIVERY NOTE', 'subtitle' => '{{organization.name}}', 'show_logo' => false],
                    'sections' => ['branch_info' => true, 'customer_info' => true, 'items_table' => true, 'totals' => true, 'payments' => true, 'signatures' => true],
                    'footer' => ['text' => 'Ապրանքագիրը հանդիսանում է հիմք ապրանքանյութական արժեքների հանձնման և ընդունման համար:'],
                ],
            ],
            order: $note->order,
            deliveryNote: $note
        );

        return response()->json([
            'success' => true,
            'data' => [
                'document_number' => $note->document_number,
                'reprint_count' => $note->reprint_count,
                'html' => $html,
            ],
        ]);
    }
}
