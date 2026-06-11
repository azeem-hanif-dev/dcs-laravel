<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Payment::where('company_id', $request->company_id)
            ->with(['invoice', 'shop'])->latest();
        return $this->paginatedResponse($query, $request, 'Payments retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'shop_id' => 'required|exists:shops,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|in:Cash,Bank Transfer,Cheque,Mobile Money,Credit Card',
            'reference_number' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;

        $payment = Payment::create($data);

        // Update invoice paid_amount
        $invoice = Invoice::find($data['invoice_id']);
        $invoice->increment('paid_amount', $data['amount']);
        $invoice->refresh(); // Refresh to get updated generated columns

        // Auto-update invoice status
        if ($invoice->balance_due <= 0) {
            $invoice->update(['status' => 'Paid']);
        } elseif ($invoice->paid_amount > 0) {
            $invoice->update(['status' => 'Partially Paid']);
        }

        return $this->successResponse($payment->load('invoice'), 'Payment recorded', 201);
    }

    public function show($id)
    {
        return $this->successResponse(Payment::with('invoice.shop')->findOrFail($id));
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $invoice = Invoice::find($payment->invoice_id);
        if ($invoice) {
            $invoice->decrement('paid_amount', $payment->amount);
            $invoice->refresh(); // Refresh generated columns
            if ($invoice->paid_amount <= 0) {
                $invoice->update(['status' => 'Unpaid']);
            } elseif ($invoice->balance_due > 0) {
                $invoice->update(['status' => 'Partially Paid']);
            }
        }
        $payment->delete();
        return $this->successResponse(null, 'Payment deleted');
    }
}
