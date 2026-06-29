<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = Invoice::where('company_id', $request->company_id)
            ->with(['shop', 'salesOrder', 'purchaseOrder', 'supplier']);

        // Distributor sees: own invoices + invoices linked to their entities
        $query = $this->applyInvoiceVisibility($query, $request);
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('shop', fn($sq) => $sq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }
        if ($request->status) $query->where('status', $request->status);
        $query->latest();
        return $this->paginatedResponse($query, $request, 'Invoices retrieved');
    }

    /**
     * Custom visibility for invoices — they can be sales OR procurement.
     */
    private function applyInvoiceVisibility($query, Request $request)
    {
        $user = $request->auth_user;

        if (!$user || in_array($user->role, ['superadmin', 'admin'])) {
            return $query;
        }

        if ($user->role !== 'distributor') {
            return $query->where('user_id', $user->id);
        }

        $distributorId = $user->distributor_id;
        if (!$distributorId) {
            return $query->where('user_id', $user->id);
        }

        return $query->where(function ($q) use ($user, $distributorId) {
            // 1. Created by the distributor
            $q->where('user_id', $user->id);

            // 2. Sales invoice linked to their salesman's shop
            $q->orWhereHas('salesOrder.shop.salesman', function ($sub) use ($distributorId) {
                $sub->where('distributor_id', $distributorId);
            });

            // 3. Procurement invoice for a PO the distributor created
            $q->orWhereHas('purchaseOrder', function ($sub) use ($user) {
                $sub->where('ordered_by', $user->id);
            });
        });
    }

    public function show(Request $request, $id)
    {
        $query = Invoice::where('company_id', $request->company_id)
            ->with('shop', 'salesOrder.items.product', 'purchaseOrder.items.material', 'supplier', 'payments');
        $query = $this->applyInvoiceVisibility($query, $request);
        return $this->successResponse($query->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'shop_id' => 'nullable|exists:shops,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'invoice_type' => 'nullable|in:sales,procurement',
        ]);

        $data['invoice_number'] = Invoice::generateInvoiceNumber();
        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;
        $data['status'] = 'Unpaid';
        $data['paid_amount'] = 0;
        $data['invoice_type'] = $data['invoice_type'] ?? 'sales';

        $invoice = Invoice::create($data);
        return $this->successResponse($invoice->load('shop'), 'Invoice created', 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $query = Invoice::where('company_id', $request->company_id);
        $query = $this->applyInvoiceVisibility($query, $request);
        $invoice = $query->findOrFail($id);

        $this->authorizeStatusUpdate($invoice, $request);

        $request->validate([
            'status' => 'required|in:Unpaid,Partially Paid,Paid,Overdue,Cancelled'
        ]);
        $invoice->update(['status' => $request->status]);
        return $this->successResponse($invoice, 'Invoice status updated');
    }

    public function destroy(Request $request, $id)
    {
        $query = Invoice::where('company_id', $request->company_id);
        $query = $this->applyInvoiceVisibility($query, $request);
        $invoice = $query->findOrFail($id);
        $invoice->delete();
        return $this->successResponse(null, 'Invoice deleted');
    }
}
