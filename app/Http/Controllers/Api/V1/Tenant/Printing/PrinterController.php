<?php

namespace App\Http\Controllers\Api\V1\Tenant\Printing;

use App\Domain\Printing\Models\Printer;
use App\Domain\Printing\Services\PrintJobManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Printer::with(['branch', 'workstation']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('printer_type')) {
            $query->where('printer_type', $request->query('printer_type'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->filled('interface_type') && ! $request->filled('connection_type')) {
            $connectionType = match ($request->input('interface_type')) {
                'network' => 'escpos_network',
                'usb' => 'escpos_usb_bridge',
                default => $request->input('interface_type'),
            };
            $request->merge(['connection_type' => $connectionType]);
        }

        if ($request->filled('protocol') && ! $request->filled('printer_type')) {
            $printerType = $request->input('protocol') === 'esc_pos' ? 'thermal' : 'office';
            $request->merge(['printer_type' => $printerType]);
        }

        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'workstation_id' => ['nullable', 'uuid', 'exists:pos_workstations,id'],
            'name' => ['required', 'string', 'max:100'],
            'printer_type' => ['required', 'string', 'in:thermal,office,virtual'],
            'connection_type' => ['required', 'string', 'in:browser,escpos_network,escpos_usb_bridge,system_pdf'],
            'interface_type' => ['nullable', 'string'],
            'protocol' => ['nullable', 'string'],
            'ip_address' => ['nullable', 'string', 'max:50'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'paper_width' => ['required', 'string', 'in:58mm,80mm,a4,a5'],
            'character_set' => ['nullable', 'string', 'max:30'],
            'is_default' => ['nullable', 'boolean'],
            'supports_cash_drawer' => ['nullable', 'boolean'],
            'supports_cutter' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        if (! empty($validated['is_default'])) {
            Printer::where('branch_id', $validated['branch_id'])->update(['is_default' => false]);
        }

        $printer = Printer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Printer created successfully.',
            'data' => $printer->load(['branch', 'workstation']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $printer = Printer::with(['branch', 'workstation', 'printJobs'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $printer,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $printer = Printer::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => ['sometimes', 'uuid', 'exists:branches,id'],
            'workstation_id' => ['nullable', 'uuid', 'exists:pos_workstations,id'],
            'name' => ['sometimes', 'string', 'max:100'],
            'printer_type' => ['sometimes', 'string', 'in:thermal,office,virtual'],
            'connection_type' => ['sometimes', 'string', 'in:browser,escpos_network,escpos_usb_bridge,system_pdf'],
            'ip_address' => ['nullable', 'string', 'max:50'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'paper_width' => ['sometimes', 'string', 'in:58mm,80mm,a4,a5'],
            'character_set' => ['nullable', 'string', 'max:30'],
            'is_default' => ['nullable', 'boolean'],
            'supports_cash_drawer' => ['nullable', 'boolean'],
            'supports_cutter' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:online,offline,error'],
            'settings' => ['nullable', 'array'],
        ]);

        if (! empty($validated['is_default']) && ! empty($validated['branch_id'])) {
            Printer::where('branch_id', $validated['branch_id'])->where('id', '!=', $printer->id)->update(['is_default' => false]);
        }

        $printer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Printer updated successfully.',
            'data' => $printer->fresh(['branch', 'workstation']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $printer = Printer::findOrFail($id);
        $printer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Printer deleted successfully.',
        ]);
    }

    public function testPrint(string $id, PrintJobManager $manager): JsonResponse
    {
        $printer = Printer::findOrFail($id);

        $job = $manager->createPrintJob([
            'branch_id' => $printer->branch_id,
            'printer_id' => $printer->id,
            'document_type' => 'test_page',
            'copies' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Test print dispatched to {$printer->name}.",
            'data' => [
                'job_id' => $job->id,
                'status' => $job->status,
                'rendered_html' => $job->payload_rendered,
            ],
        ]);
    }
}
