<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Material\MaterialOrder;
use App\Models\Material\MaterialOrderItem;
use App\Models\Invoice;
use App\Models\Stock;
use Illuminate\Http\Request;

class MaterialOrderController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = MaterialOrder::where('company_id', $request->company_id)
            ->with('items.material', 'items.supplier', 'orderedBy');
        $query = $this->applyVisibility($query, $request, 'ordered_by');
        $query->latest();
        return $this->paginatedResponse($query, $request, 'Orders retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orderDate' => 'required|date',
            'supplierId' => 'nullable|exists:suppliers,id',
            'warehouseId' => 'nullable|exists:warehouses,id',
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
            'supplier_id' => $data['supplierId'] ?? null,
            'warehouse_id' => $data['warehouseId'] ?? null,
            'order_date' => $data['orderDate'],
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null,
        ];

        $items = $data['items'];
        unset($data['items'], $data['orderDate'], $data['supplierId'], $data['warehouseId']);

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

        return $this->successResponse(
            $order->load('items.material', 'items.supplier'),
            'Material order created successfully', 201
        );
    }

    public function show(Request $request, $id)
    {
        $query = MaterialOrder::where('company_id', $request->company_id)
            ->with('items.material', 'items.supplier', 'orderedBy');
        $query = $this->applyVisibility($query, $request, 'ordered_by');
        $order = $query->findOrFail($id);
        return $this->successResponse($order);
    }

    public function update(Request $request, $id)
    {
        $query = MaterialOrder::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request, 'ordered_by');
        $order = $query->findOrFail($id);

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

        return $this->successResponse(
            $order->load('items.material', 'items.supplier', 'orderedBy'),
            'Order updated'
        );
    }

    public function destroy(Request $request, $id)
    {
        $query = MaterialOrder::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request, 'ordered_by');
        $order = $query->findOrFail($id);
        $order->items()->delete();
        $order->delete();
        return $this->successResponse(null, 'Material order deleted successfully');
    }

    /**
     * Update purchase order status.
     *
     * When a distributor marks a PO as "delivered" (received goods from supplier),
     * the system auto-creates a procurement invoice so payment can be recorded.
     *
     * Only superadmin/admin can update any status. Distributors can only update
     * their own orders.
     */
    public function updateStatus(Request $request, $id)
    {
        $query = MaterialOrder::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request, 'ordered_by');
        $order = $query->findOrFail($id);

        // Permission check: distributor can only update their own records
        $this->authorizeStatusUpdate($order, $request);

        $data = $request->validate([
            'status' => 'required|string|in:pending,approved,delivered,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->update($data);

        $newStatus = $data['status'];
        $procurementInvoice = null;

        // ─── Auto-actions when delivered ──────────────────────
        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {

            // 1. Update material quantities and stock
            $warehouseId = $order->warehouse_id ?? null;
            $totalAmount = 0;

            foreach ($order->items as $item) {
                $material = \App\Models\Material\Material::find($item->material_id);
                if ($material) {
                    $material->increment('total_quantity', $item->quantity);
                    $totalAmount += $item->quantity * ($material->price ?? 0);
                }

                if ($warehouseId) {
                    $stock = Stock::firstOrCreate(
                        [
                            'product_id' => $item->material_id,
                            'warehouse_id' => $warehouseId,
                            'company_id' => $request->company_id,
                        ],
                        ['total_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $stock->increment('total_quantity', $item->quantity);
                }
            }

            // 2. Auto-create procurement invoice
            $supplierId = $order->supplier_id ?? $order->items->first()?->supplier_id ?? null;
            $procurementInvoice = Invoice::create([
                'invoice_number' => Invoice::generateProcurementInvoiceNumber(),
                'purchase_order_id' => $order->id,
                'supplier_id' => $supplierId,
                'shop_id' => null,
                'sales_order_id' => null,
                'invoice_type' => 'procurement',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => 'Unpaid',
                'company_id' => $request->company_id,
                'user_id' => $request->auth_user->id,
            ]);
        }

        // ─── Release stock on cancel (if was delivered) ────────
        if ($newStatus === 'cancelled' && $oldStatus === 'delivered') {
            $warehouseId = $order->warehouse_id ?? null;
            foreach ($order->items as $item) {
                $material = \App\Models\Material\Material::find($item->material_id);
                if ($material) {
                    $material->decrement('total_quantity', $item->quantity);
                }
                if ($warehouseId) {
                    $stock = Stock::where('product_id', $item->material_id)
                        ->where('warehouse_id', $warehouseId)
                        ->where('company_id', $request->company_id)->first();
                    if ($stock) {
                        $stock->decrement('total_quantity', $item->quantity);
                        if ($stock->total_quantity < 0) {
                            $stock->update(['total_quantity' => 0]);
                        }
                    }
                }
            }
        }

        $responseData = [
            'order' => $order->load('items.material'),
        ];

        if ($procurementInvoice) {
            $responseData['invoice'] = $procurementInvoice->load('purchaseOrder');
        }

        return $this->successResponse(
            $responseData,
            $procurementInvoice
                ? 'Order delivered. Procurement invoice auto-generated. Now record payment.'
                : "Order status updated to {$newStatus}"
        );
    }
}
