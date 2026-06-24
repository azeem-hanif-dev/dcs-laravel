<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Stock;
use App\Models\Invoice;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = SalesOrder::where('company_id', $request->company_id)
            ->with(['shop', 'salesman', 'items.product']);

        // Sales orders: visible if created by user OR linked to their salesman/shop chain
        $query = $this->applyDistributorThroughScope($query, $request, 'salesman');

        $query->latest();
        return $this->paginatedResponse($query, $request, 'Sales orders retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'salesman_id' => 'nullable|exists:salesmen,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',
        ]);

        $data['so_number'] = SalesOrder::generateSoNumber();
        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;
        $data['status'] = $request->status ?? 'Processing';

        $items = $data['items'];
        unset($data['items']);

        $order = SalesOrder::create($data);

        foreach ($items as $item) {
            $item['total'] = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $order->items()->create($item);
        }

        // Only reserve stock if status is Confirmed or beyond (not Draft/Processing)
        if (in_array($order->status, ['Confirmed', 'Ready', 'Delivered'])) {
            if ($order->warehouse_id) {
                foreach ($order->items as $item) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item->product_id, 'warehouse_id' => $order->warehouse_id, 'company_id' => $order->company_id],
                        ['total_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $stock->increment('reserved_quantity', $item->quantity);
                    $stock->refresh();
                    $stock->logMovement(
                        type: 'reserved',
                        change: $item->quantity,
                        userId: $request->auth_user->id,
                        refType: 'SalesOrder',
                        refId: $order->id,
                        refNumber: $order->so_number,
                        notes: "SO #{$order->so_number} created as Confirmed — reserved {$item->quantity} units"
                    );
                }
            }

            if (!Invoice::where('sales_order_id', $order->id)->exists()) {
                Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'sales_order_id' => $order->id,
                    'shop_id' => $order->shop_id,
                    'invoice_type' => 'sales',
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'total_amount' => $order->grand_total,
                    'paid_amount' => 0,
                    'status' => 'Unpaid',
                    'company_id' => $order->company_id,
                    'user_id' => $request->auth_user->id,
                ]);
            }
        }

        return $this->successResponse($order->load('items.product', 'shop'), 'Sales order created', 201);
    }

    public function show(Request $request, $id)
    {
        $query = SalesOrder::where('company_id', $request->company_id)
            ->with('items.product', 'shop', 'salesman', 'invoice', 'delivery');
        $query = $this->applyDistributorThroughScope($query, $request, 'salesman');
        return $this->successResponse($query->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $query = SalesOrder::where('company_id', $request->company_id);
        $query = $this->applyDistributorThroughScope($query, $request, 'salesman');
        $order = $query->findOrFail($id);

        $data = $request->validate([
            'shop_id' => 'sometimes|exists:shops,id',
            'salesman_id' => 'nullable|exists:salesmen,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'sometimes|date',
            'notes' => 'nullable|string',
            'subtotal' => 'nullable|numeric',
            'tax_percent' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'grand_total' => 'nullable|numeric',
        ]);
        $order->update($data);
        return $this->successResponse($order, 'Sales order updated');
    }

    public function updateStatus(Request $request, $id)
    {
        $query = SalesOrder::where('company_id', $request->company_id);
        $query = $this->applyDistributorThroughScope($query, $request, 'salesman');
        $order = $query->findOrFail($id);

        $this->authorizeStatusUpdate($order, $request);

        $request->validate([
            'status' => 'required|in:Draft,Confirmed,Processing,Ready,Delivered,Cancelled'
        ]);

        $newStatus = $request->status;
        $oldStatus = $order->status;

        $order->update(['status' => $newStatus]);

        if ($newStatus === 'Confirmed' && $oldStatus !== 'Confirmed') {
            foreach ($order->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $order->warehouse_id, 'company_id' => $order->company_id],
                    ['total_quantity' => 0, 'reserved_quantity' => 0]
                );
                $stock->increment('reserved_quantity', $item->quantity);
                $stock->refresh();

                // Log reserve movement
                $stock->logMovement(
                    type:         'reserved',
                    change:       $item->quantity,
                    userId:       $request->auth_user->id,
                    refType:      'SalesOrder',
                    refId:        $order->id,
                    refNumber:    $order->so_number,
                    notes:        "SO #{$order->so_number} confirmed — reserved {$item->quantity} units"
                );
            }

            if (!Invoice::where('sales_order_id', $order->id)->exists()) {
                Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'sales_order_id' => $order->id,
                    'shop_id' => $order->shop_id,
                    'invoice_type' => 'sales',
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'total_amount' => $order->grand_total,
                    'paid_amount' => 0,
                    'status' => 'Unpaid',
                    'company_id' => $order->company_id,
                    'user_id' => $request->auth_user->id,
                ]);
            }
        }

        if ($newStatus === 'Cancelled' && $oldStatus === 'Confirmed') {
            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->where('company_id', $order->company_id)->first();
                if ($stock) {
                    $stock->decrement('reserved_quantity', $item->quantity);
                    $stock->refresh();

                    $stock->logMovement(
                        type:         'released',
                        change:       -$item->quantity,
                        userId:       $request->auth_user->id,
                        refType:      'SalesOrder',
                        refId:        $order->id,
                        refNumber:    $order->so_number,
                        notes:        "SO #{$order->so_number} cancelled — released {$item->quantity} reserved units"
                    );
                }
            }
        }

        return $this->successResponse($order, "Sales order status updated to {$newStatus}");
    }

    public function destroy(Request $request, $id)
    {
        $query = SalesOrder::where('company_id', $request->company_id);
        $query = $this->applyDistributorThroughScope($query, $request, 'salesman');
        $order = $query->findOrFail($id);
        $order->delete();
        return $this->successResponse(null, 'Sales order deleted');
    }
}
