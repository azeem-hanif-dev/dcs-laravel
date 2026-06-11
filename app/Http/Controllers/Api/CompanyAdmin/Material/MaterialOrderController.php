<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Material\MaterialOrder;
use App\Models\Material\MaterialOrderItem;
use Illuminate\Http\Request;

class MaterialOrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = MaterialOrder::where('company_id', $request->company_id)
            ->with('items.material', 'items.supplier', 'orderedBy')
            ->latest();
        return $this->paginatedResponse($query, $request, 'Orders retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orderDate' => 'required|date',
            'status' => 'nullable|string|in:pending,approved,delivered,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.materialId' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.supplierId' => 'nullable|exists:suppliers,id',
            'items.*.projectId' => 'nullable|exists:projects,id',
        ]);

        $orderData = [
            'company_id' => $request->company_id,
            'ordered_by' => $request->auth_user->id,
            'order_date' => $data['orderDate'],
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null,
        ];

        $items = $data['items'];
        unset($data['items'], $data['orderDate']);

        $order = MaterialOrder::create($orderData);

        foreach ($items as $item) {
            MaterialOrderItem::create([
                'material_order_id' => $order->id,
                'material_id' => $item['materialId'],
                'quantity' => $item['quantity'],
                'supplier_id' => $item['supplierId'] ?? null,
                'project_id' => $item['projectId'] ?? null,
            ]);
        }

        return $this->successResponse($order->load('items.material', 'items.supplier'), 'Material order created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $order = MaterialOrder::where('company_id', $request->company_id)
            ->with('items.material', 'items.supplier', 'orderedBy')
            ->findOrFail($id);
        return $this->successResponse($order);
    }

    public function update(Request $request, $id)
    {
        $order = MaterialOrder::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'status' => 'required|string|in:pending,approved,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);
        $order->update($data);
        return $this->successResponse($order, 'Material order updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $order = MaterialOrder::where('company_id', $request->company_id)->findOrFail($id);
        $order->items()->delete();
        $order->delete();
        return $this->successResponse(null, 'Material order deleted successfully');
    }

    public function statusUpdate(Request $request, $id)
    {
        $order = MaterialOrder::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['status' => 'required|string|in:pending,approved,delivered,cancelled']);
        $oldStatus = $order->status;
        $order->update($data);

        // Auto-update stock when purchase order is delivered
        if ($data['status'] === 'delivered' && $oldStatus !== 'delivered') {
            foreach ($order->items as $item) {
                $stock = \App\Models\Stock::firstOrCreate(
                    ['product_id' => $item->material_id, 'warehouse_id' => $order->warehouse_id ?? null, 'company_id' => $request->company_id],
                    ['total_quantity' => 0, 'reserved_quantity' => 0]
                );
                $stock->increment('total_quantity', $item->quantity);

                // Also update the material's total_quantity
                $material = \App\Models\Material\Material::find($item->material_id);
                if ($material) {
                    $material->increment('total_quantity', $item->quantity);
                }
            }
        }

        return $this->successResponse($order, 'Order status updated successfully');
    }
}
