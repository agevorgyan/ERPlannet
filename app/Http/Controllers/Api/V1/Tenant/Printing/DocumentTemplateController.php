<?php

namespace App\Http\Controllers\Api\V1\Tenant\Printing;

use App\Domain\Printing\Models\DocumentTemplate;
use App\Domain\Printing\Services\DocumentTemplateRenderer;
use App\Domain\Sales\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DocumentTemplate::with(['branch', 'latestVersion']);

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->query('document_type'));
        }

        if ($request->filled('paper_size')) {
            $query->where('paper_size', $request->query('paper_size'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
            'document_type' => ['required', 'string'],
            'paper_size' => ['required', 'string', 'in:58mm,80mm,a4,a5'],
            'is_default' => ['nullable', 'boolean'],
            'layout_config' => ['required', 'array'],
            'sample_data' => ['nullable', 'array'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']).'-'.Str::random(5);

        $template = DocumentTemplate::create($validated);

        // Automatically create initial version 1
        $template->publishVersion($request->user()?->id, 'Initial draft creation');

        return response()->json([
            'success' => true,
            'message' => 'Document template created successfully.',
            'data' => $template->load(['branch', 'latestVersion']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $template = DocumentTemplate::with(['branch', 'versions.publisher'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $template = DocumentTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'paper_size' => ['sometimes', 'string'],
            'paper_width' => ['sometimes', 'string'],
            'content' => ['sometimes', 'nullable', 'string'],
            'config' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'layout_config' => ['sometimes', 'array'],
            'sample_data' => ['nullable', 'array'],
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template draft updated successfully.',
            'data' => $template->fresh(['branch', 'latestVersion']),
        ]);
    }

    public function publish(Request $request, string $id): JsonResponse
    {
        $template = DocumentTemplate::findOrFail($id);

        $validated = $request->validate([
            'changelog' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $notes = $validated['notes'] ?? $validated['changelog'] ?? 'Published version update';

        $version = $template->publishVersion($request->user()?->id, $notes);

        return response()->json([
            'success' => true,
            'message' => "Template version {$version->version_number} published successfully.",
            'data' => $template->fresh(['branch', 'latestVersion', 'versions']),
        ]);
    }

    public function duplicate(string $id): JsonResponse
    {
        $template = DocumentTemplate::findOrFail($id);

        $copy = $template->replicate(['slug']);
        $copy->name = $template->name.' (Copy)';
        $copy->slug = Str::slug($copy->name).'-'.Str::random(5);
        $copy->is_default = false;
        $copy->save();

        $copy->publishVersion(auth()->id(), 'Duplicated from '.$template->name);

        return response()->json([
            'success' => true,
            'message' => 'Template duplicated successfully.',
            'data' => $copy->load(['branch', 'latestVersion']),
        ], 201);
    }

    public function preview(Request $request, string $id, DocumentTemplateRenderer $renderer): JsonResponse
    {
        $template = DocumentTemplate::findOrFail($id);

        $order = null;
        if ($request->filled('order_id')) {
            $order = Order::with(['items', 'customer', 'branch', 'warehouse', 'paymentTransactions'])->find($request->query('order_id'));
        }

        // If client provided live draft layout overrides, merge them
        $layout = $request->input('layout_config') ?? $template->layout_config;

        $html = $renderer->render(
            template: array_merge($template->toArray(), ['layout_config' => $layout]),
            order: $order,
            customContext: $request->input('custom_context', [])
        );

        return response()->json([
            'success' => true,
            'data' => [
                'html' => $html,
                'paper_size' => $template->paper_size,
                'document_type' => $template->document_type,
            ],
        ]);
    }

    public function placeholders(DocumentTemplateRenderer $renderer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $renderer->getPlaceholderRegistry(),
        ]);
    }
}
