<?php

namespace App\Http\Controllers\Api\V1\Tenant\Warehouse;

use App\Domain\Warehouse\Models\StockBatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockBatchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockBatch::with(['warehouse', 'product', 'productVariant']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->boolean('expiring_soon')) {
            $days = $request->integer('days', 30);
            $query->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays($days)->toDateString())
                ->where('status', 'active');
        }

        $batches = $query->orderBy('expiry_date', 'asc')->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $batches->items(),
            'meta' => [
                'current_page' => $batches->currentPage(),
                'last_page' => $batches->lastPage(),
                'total' => $batches->total(),
            ],
        ]);
    }
}
