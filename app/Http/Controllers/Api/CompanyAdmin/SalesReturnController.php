<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Stock;
use Illuminate\Http\Request;

class SalesReturnController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = SalesReturn::where('company_id', $request->company_id)
            ->with(['shop', 'salesOrder', 'items.product']);
        // SalesReturn → Shop → Salesman → distributor_id
        $query = $this->applyDistributorThroughScope($query, $request, 'shop.salesman');
        $query->latest();
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

        foreach ($items as $item) {
            $stock = Stock::where('product_id', $item['product_id'])->where('company_id', $request->company_id)->first();
            if ($stock) {
                $stock->increment('total_quantity', $item['quantity']);
                $stock->refresh();
                $stock->logMovement(
                    type:         'sales_returned',
                    change:       $item['quantity'],
                    userId:       $request->auth_user->id,
                    refType:      'SalesReturn',
                    refId:        $return->id,
                    refNumber:    $return->return_number,
                    notes:        "Return #{$return->return_number} — {$item['quantity']} units back"
                );
            }
            $material = \App\Models\Material\Material::find($item['product_id']);
            if ($material) $material->increment('total_quantity', $item['quantity']);
        }

        return $this->successResponse($return->load('items.product', 'shop'), 'Sales return created', 201);
    }

    public function show(Request $request, $id)
    {
        $query = SalesReturn::where('company_id', $request->company_id)
            ->with('items.product', 'shop', 'salesOrder');
        $query = $this->applyDistributorThroughScope($query, $request, 'shop.salesman');
        return $this->successResponse($query->findOrFail($id));
    }

    public function updateStatus(Request $request, $id)
    {
        $query = SalesReturn::where('company_id', $request->company_id);
        $query = $this->applyDistributorThroughScope($query, $request, 'shop.salesman');
        $return = $query->findOrFail($id);

        $this->authorizeStatusUpdate($return, $request);

        $request->validate(['status' => 'required|in:Pending,Approved,Completed,Rejected']);
        $return->update(['status' => $request->status]);
        return $this->successResponse($return, 'Status updated');
    }

    public function destroy(Request $request, $id)
    {
        $query = SalesReturn::where('company_id', $request->company_id);
        $query = $this->applyDistributorThroughScope($query, $request, 'shop.salesman');
        $return = $query->findOrFail($id);
        $return->delete();
        return $this->successResponse(null, 'Deleted');
    }
}
