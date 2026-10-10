<?php

namespace App\Http\Controllers\Api\V1\Tenant\Sales;

use App\Domain\Branch\Models\Branch;
use App\Domain\Sales\Models\XmlImport;
use App\Domain\Sales\Services\Xml\XmlOrderImporter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XmlImportController extends Controller
{
    public function preview(Request $request, XmlOrderImporter $importer): JsonResponse
    {
        $file = $request->file('file') ?? $request->file('xml_file');
        if (! $file) {
            return response()->json([
                'message' => 'The file field is required.',
                'errors' => ['file' => ['The file field is required.']],
            ], 422);
        }

        if (! $request->filled('branch_id')) {
            $defaultBranch = Branch::where('is_active', true)->first() ?? Branch::first();
            if ($defaultBranch) {
                $request->merge(['branch_id' => $defaultBranch->id]);
            }
        }

        $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
        ]);

        $content = file_get_contents($file->getRealPath());

        try {
            $xmlImport = $importer->preview(
                xmlContent: $content,
                fileName: $file->getClientOriginalName(),
                branchId: $request->input('branch_id'),
                warehouseId: $request->input('warehouse_id'),
                userId: $request->user()?->id
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'XML file parsed and validated.',
            'data' => $xmlImport,
        ]);
    }

    public function confirm(Request $request, XmlOrderImporter $importer): JsonResponse
    {
        $file = $request->file('file') ?? $request->file('xml_file');

        if ($file) {
            if (! $request->filled('branch_id')) {
                $defaultBranch = Branch::where('is_active', true)->first() ?? Branch::first();
                if ($defaultBranch) {
                    $request->merge(['branch_id' => $defaultBranch->id]);
                }
            }

            $request->validate([
                'branch_id' => ['required', 'uuid', 'exists:branches,id'],
                'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
            ]);

            $content = file_get_contents($file->getRealPath());
            $checksum = hash('sha256', $content);

            $existing = XmlImport::where('checksum', $checksum)
                ->where('status', 'completed')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate import: This XML file has already been imported.',
                ], 422);
            }

            try {
                $preview = $importer->preview(
                    xmlContent: $content,
                    fileName: $file->getClientOriginalName(),
                    branchId: $request->input('branch_id'),
                    warehouseId: $request->input('warehouse_id'),
                    userId: $request->user()?->id
                );

                $order = $importer->confirm($preview->id, $request->user()?->id);
                $preview->refresh();

                return response()->json([
                    'success' => true,
                    'message' => "Order {$order->order_number} successfully imported from XML.",
                    'data' => $preview,
                ], 201);
            } catch (\InvalidArgumentException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        $validated = $request->validate([
            'import_id' => ['required', 'uuid', 'exists:xml_imports,id'],
        ]);

        $order = $importer->confirm($validated['import_id'], $request->user()?->id);
        $importRecord = XmlImport::find($validated['import_id']);

        return response()->json([
            'success' => true,
            'message' => "Order {$order->order_number} successfully imported from XML.",
            'data' => $importRecord ?? $order,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $imports = XmlImport::with(['branch', 'warehouse', 'user'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $imports->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $imports->currentPage(),
                    'per_page' => $imports->perPage(),
                    'total' => $imports->total(),
                    'last_page' => $imports->lastPage(),
                ],
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $import = XmlImport::with(['branch', 'warehouse', 'user', 'importErrors'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $import,
        ]);
    }
}
