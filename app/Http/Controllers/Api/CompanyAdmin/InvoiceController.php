<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Invoice::where('company_id', $request->company_id)
            ->with(['shop', 'salesOrder'])->latest();
        return $this->paginatedResponse($query, $request, 'Invoices retrieved');
    }

    public function show($id)
    {
        return $this->successResponse(
            Invoice::with('shop', 'salesOrder.items.product', 'payments')->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'shop_id' => 'required|exists:shops,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $data['invoice_number'] = Invoice::generateInvoiceNumber();
        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;
        $data['status'] = 'Unpaid';
        $data['paid_amount'] = 0;

        $invoice = Invoice::create($data);
        return $this->successResponse($invoice->load('shop'), 'Invoice created', 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $request->validate([
            'status' => 'required|in:Unpaid,Partially Paid,Paid,Overdue,Cancelled'
        ]);
        $invoice->update(['status' => $request->status]);
        return $this->successResponse($invoice, 'Invoice status updated');
    }

    public function destroy($id)
    {
        Invoice::findOrFail($id)->delete();
        return $this->successResponse(null, 'Invoice deleted');
    }
}
