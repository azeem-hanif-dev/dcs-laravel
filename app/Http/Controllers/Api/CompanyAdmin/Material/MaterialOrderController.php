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
                'purchase_order_id' => $order->id,
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
            'orderDate' => 'sometimes|date',
            'status' => 'sometimes|string|in:pending,approved,delivered,cancelled',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.materialId' => 'nullable|exists:materials,id',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.supplierId' => 'nullable|exists:suppliers,id',
        ]);

        $orderData = [];
        if (isset($data['orderDate'])) $orderData['order_date'] = $data['orderDate'];
        if (isset($data['status'])) $orderData['status'] = $data['status'];
        if (array_key_exists('notes', $data)) $orderData['notes'] = $data['notes'];
        if (!empty($orderData)) $order->update($orderData);

        // Update items if provided
        if (isset($data['items'])) {
            $order->items()->delete();
            foreach ($data['items'] as $item) {
                if (!empty($item['materialId'])) {
                    $order->items()->create([
                        'material_id' => $item['materialId'],
                        'quantity' => $item['quantity'] ?? 1,
                        'supplier_id' => $item['supplierId'] ?? null,
                    ]);
                }
            }
        }

        return $this->successResponse($order->load('items.material', 'items.supplier', 'orderedBy'), 'Order updated');
    }

    public function destroy(Request $request, $id)
    {
        $order = MaterialOrder::where('company_id', $request->company_id)->findOrFail($id);
        $order->items()->delete();
        $order->delete();
        return $this->successResponse(null, 'Material order deleted successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $order = MaterialOrder::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['status' => 'required|string|in:pending,approved,delivered,cancelled']);
        $oldStatus = $order->status;
        $order->update($data);

        // Auto-update stock when purchase order is delivered
        if ($data['status'] === 'delivered' && $oldStatus !== 'delivered') {
            $warehouseId = $order->warehouse_id ?? null;
            foreach ($order->items as $item) {
                // Update material's total_quantity
                $material = \App\Models\Material\Material::find($item->material_id);
                if ($material) {
                    $material->increment('total_quantity', $item->quantity);
                }

                // Update stock record if warehouse is set
                if ($warehouseId) {
                    $stock = \App\Models\Stock::firstOrCreate(
                        ['product_id' => $item->material_id, 'warehouse_id' => $warehouseId, 'company_id' => $request->company_id],
                        ['total_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $stock->increment('total_quantity', $item->quantity);
                }
            }
        }

        return $this->successResponse($order, 'Order status updated successfully');
    }
}
