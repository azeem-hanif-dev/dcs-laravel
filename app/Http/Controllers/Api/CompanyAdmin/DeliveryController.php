<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Delivery;
use App\Models\Stock;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = Delivery::where('company_id', $request->company_id)
            ->with(['salesOrder.shop', 'shop']);
        // Delivery → Shop → Salesman → distributor_id
        $query = $this->applyDistributorThroughScope($query, $request, 'shop.salesman');
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('tracking_number', 'like', "%{$s}%")
                  ->orWhere('driver_name', 'like', "%{$s}%")
                  ->orWhereHas('shop', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }
        if ($request->status) $query->where('status', $request->status);
        $query->latest();
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

    public function show(Request $request, $id)
    {
        $query = Delivery::where('company_id', $request->company_id)
            ->with('salesOrder.items.product', 'shop');
        $query = $this->applyDistributorThroughScope($query, $request, 'shop.salesman');
        return $this->successResponse($query->findOrFail($id));
    }

    public function updateStatus(Request $request, $id)
    {
        $query = Delivery::where('company_id', $request->company_id);
        $query = $this->applyDistributorThroughScope($query, $request, 'shop.salesman');
        $delivery = $query->findOrFail($id);

        $this->authorizeStatusUpdate($delivery, $request);

        $request->validate([
            'status' => 'required|in:Pending,Dispatched,In Transit,Delivered,Failed'
        ]);

        $newStatus = $request->status;

        if ($newStatus === 'Delivered' && $delivery->salesOrder) {
            $order = $delivery->salesOrder;
            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->where('company_id', $order->company_id)->first();
                if ($stock) {
                    $stock->decrement('total_quantity', $item->quantity);
                    $stock->decrement('reserved_quantity', $item->quantity);
                    $stock->refresh();

                    $stock->logMovement(
                        type:         'sales_shipped',
                        change:       -$item->quantity,
                        userId:       $request->auth_user->id,
                        refType:      'SalesOrder',
                        refId:        $order->id,
                        refNumber:    $order->so_number,
                        notes:        "Delivery #{$delivery->id} shipped — {$item->quantity} units out"
                    );
                }
            }
        }

        $delivery->update(['status' => $newStatus]);
        if ($request->delivery_date) $delivery->update(['delivery_date' => $request->delivery_date]);

        return $this->successResponse($delivery, "Delivery status updated to {$newStatus}");
    }

    public function destroy(Request $request, $id)
    {
        $query = Delivery::where('company_id', $request->company_id);
        $query = $this->applyDistributorThroughScope($query, $request, 'shop.salesman');
        $delivery = $query->findOrFail($id);
        $delivery->delete();
        return $this->successResponse(null, 'Delivery deleted');
    }
}
