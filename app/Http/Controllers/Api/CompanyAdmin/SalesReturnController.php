<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Stock;
use Illuminate\Http\Request;

class SalesReturnController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = SalesReturn::where('company_id', $request->company_id)
            ->with(['shop', 'salesOrder', 'items.product'])->latest();
        return $this->paginatedResponse($query, $request, 'Sales returns retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'shop_id' => 'required|exists:shops,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        $data['return_number'] = SalesReturn::generateNumber();
        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;
        $data['status'] = 'Pending';

        $items = $data['items']; unset($data['items']);
        $return = SalesReturn::create($data);

        $total = 0;
        foreach ($items as $item) {
            $item['total'] = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $total += $item['total'];
            $return->items()->create($item);
        }
        $return->update(['total_amount' => $total]);

        // Return stock to inventory
        foreach ($items as $item) {
            $stock = Stock::where('product_id', $item['product_id'])->where('company_id', $request->company_id)->first();
            if ($stock) $stock->increment('total_quantity', $item['quantity']);
            $material = \App\Models\Material\Material::find($item['product_id']);
            if ($material) $material->increment('total_quantity', $item['quantity']);
        }

        return $this->successResponse($return->load('items.product', 'shop'), 'Sales return created', 201);
    }

    public function show($id) { return $this->successResponse(SalesReturn::with('items.product','shop','salesOrder')->findOrFail($id)); }

    public function updateStatus(Request $request, $id)
    {
        $return = SalesReturn::findOrFail($id);
        $request->validate(['status' => 'required|in:Pending,Approved,Completed,Rejected']);
        $return->update(['status' => $request->status]);
        return $this->successResponse($return, 'Status updated');
    }

    public function destroy($id) { SalesReturn::findOrFail($id)->delete(); return $this->successResponse(null, 'Deleted'); }
}
