<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Stock;
use App\Models\Material\Material;
use Illuminate\Http\Request;

class StockController extends Controller
{
    use ApiResponse;

    public function overview(Request $request)
    {
        $query = Stock::where('company_id', $request->company_id)
            ->with(['product' => function ($q) {
                $q->with('category', 'supplier');
            }, 'warehouse'])
            ->latest();

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->low_stock) {
            $query->whereRaw('(total_quantity - reserved_quantity) <= reorder_level');
        }

        return $this->paginatedResponse($query, $request, 'Stock overview retrieved');
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:materials,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|not_in:0',
            'type' => 'required|in:add,remove',
            'reason' => 'nullable|string',
        ]);

        $stock = Stock::firstOrCreate(
            [
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'company_id' => $request->company_id
            ],
            ['total_quantity' => 0, 'reserved_quantity' => 0]
        );

        if ($data['type'] === 'add') {
            $stock->increment('total_quantity', abs($data['quantity']));
        } else {
            $stock->decrement('total_quantity', abs($data['quantity']));
            if ($stock->total_quantity < 0) {
                $stock->update(['total_quantity' => 0]);
            }
        }

        return $this->successResponse($stock->fresh()->load('product', 'warehouse'), 'Stock adjusted');
    }
}
