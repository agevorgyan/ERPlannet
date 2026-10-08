<?php

namespace App\Http\Controllers\Api\V1\Tenant\Catalog;

use App\Domain\Catalog\Models\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(): JsonResponse
    {
        $units = Unit::all();

        return response()->json([
            'success' => true,
            'data' => $units,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required'], // can be string or array
            'precision' => ['nullable', 'integer', 'min:0', 'max:4'],
        ]);

        $name = is_array($validated['name']) ? $validated['name'] : ['hy' => $validated['name']];

        $unit = Unit::create([
            'code' => $validated['code'],
            'name' => $name,
            'precision' => $validated['precision'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully.',
            'data' => $unit,
        ], 201);
    }
}
