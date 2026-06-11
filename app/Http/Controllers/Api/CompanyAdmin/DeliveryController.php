<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Delivery;
use App\Models\Stock;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Delivery::where('company_id', $request->company_id)
            ->with(['salesOrder.shop', 'shop'])->latest();
        return $this->paginatedResponse($query, $request, 'Deliveries retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'shop_id' => 'required|exists:shops,id',
            'delivery_date' => 'nullable|date',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'vehicle_number' => 'nullable|string|max:50',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;
        $data['status'] = 'Pending';

        $delivery = Delivery::create($data);
        return $this->successResponse($delivery->load('salesOrder'), 'Delivery created', 201);
    }

    public function show($id)
    {
        return $this->successResponse(
            Delivery::with('salesOrder.items.product', 'shop')->findOrFail($id)
        );
    }

    public function updateStatus(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);
        $request->validate([
            'status' => 'required|in:Pending,Dispatched,In Transit,Delivered,Failed'
        ]);

        $newStatus = $request->status;

        // Stock logic: when delivered, decrement stock
        if ($newStatus === 'Delivered' && $delivery->salesOrder) {
            $order = $delivery->salesOrder;
            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->where('company_id', $order->company_id)->first();
                if ($stock) {
                    $stock->decrement('total_quantity', $item->quantity);
                    $stock->decrement('reserved_quantity', $item->quantity);
                }
            }
        }

        $delivery->update(['status' => $newStatus]);
        if ($request->delivery_date) $delivery->update(['delivery_date' => $request->delivery_date]);

        return $this->successResponse($delivery, "Delivery status updated to {$newStatus}");
    }

    public function destroy($id)
    {
        Delivery::findOrFail($id)->delete();
        return $this->successResponse(null, 'Delivery deleted');
    }
}
