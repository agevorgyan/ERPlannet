<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Branch\Actions\CreateBranchAction;
use App\Domain\Branch\Models\Branch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = Branch::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    public function store(Request $request, CreateBranchAction $action): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch = $action->execute($validated);

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully.',
            'data' => $branch,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $branch = Branch::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $branch,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully.',
            'data' => $branch,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Branch deleted successfully.',
        ]);
    }
}
