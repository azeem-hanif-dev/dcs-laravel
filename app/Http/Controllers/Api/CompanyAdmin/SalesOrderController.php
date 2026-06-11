<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Stock;
use App\Models\Invoice;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = SalesOrder::where('company_id', $request->company_id)
            ->with(['shop', 'salesman', 'items.product'])
            ->latest();
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
        $data['status'] = $request->status ?? 'Draft';

        $items = $data['items'];
        unset($data['items']);

        $order = SalesOrder::create($data);

        foreach ($items as $item) {
            $item['total'] = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $order->items()->create($item);
        }

        return $this->successResponse($order->load('items.product', 'shop'), 'Sales order created', 201);
    }

    public function show($id)
    {
        return $this->successResponse(
            SalesOrder::with('items.product', 'shop', 'salesman', 'invoice', 'delivery')->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $order = SalesOrder::findOrFail($id);
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
        $order = SalesOrder::findOrFail($id);
        $request->validate([
            'status' => 'required|in:Draft,Confirmed,Processing,Ready,Delivered,Cancelled'
        ]);

        $newStatus = $request->status;
        $oldStatus = $order->status;

        $order->update(['status' => $newStatus]);

        // Stock logic: reserve stock when confirmed
        if ($newStatus === 'Confirmed' && $oldStatus !== 'Confirmed') {
            foreach ($order->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $order->warehouse_id, 'company_id' => $order->company_id],
                    ['total_quantity' => 0, 'reserved_quantity' => 0]
                );
                $stock->increment('reserved_quantity', $item->quantity);
            }

            // Auto-generate invoice when confirmed
            if (!Invoice::where('sales_order_id', $order->id)->exists()) {
                Invoice::create([
                    'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
                    'sales_order_id' => $order->id,
                    'shop_id' => $order->shop_id,
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

        // Stock logic: release stock when cancelled (if was confirmed)
        if ($newStatus === 'Cancelled' && $oldStatus === 'Confirmed') {
            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->where('company_id', $order->company_id)->first();
                if ($stock) {
                    $stock->decrement('reserved_quantity', $item->quantity);
                }
            }
        }

        return $this->successResponse($order, "Sales order status updated to {$newStatus}");
    }

    public function destroy($id)
    {
        SalesOrder::findOrFail($id)->delete();
        return $this->successResponse(null, 'Sales order deleted');
    }
}
