<?php

namespace App\Http\Controllers\Api\V1\Tenant\Warehouse;

use App\Domain\Warehouse\Models\StockLevel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockLevelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockLevel::with(['warehouse', 'product.unit', 'productVariant']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        if ($request->boolean('low_stock')) {
            $query->whereRaw('quantity_on_hand <= reorder_point AND reorder_point > 0');
        }

        $levels = $query->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $levels->items(),
            'meta' => [
                'current_page' => $levels->currentPage(),
                'last_page' => $levels->lastPage(),
                'total' => $levels->total(),
            ],
        ]);
    }
}
