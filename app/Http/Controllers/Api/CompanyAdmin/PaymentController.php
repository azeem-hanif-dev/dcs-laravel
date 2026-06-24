<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = Payment::where('company_id', $request->company_id)
            ->with(['invoice', 'shop']);

        // Payments visible if the linked invoice is visible to the distributor
        $query = $this->applyPaymentVisibility($query, $request);
        $query->latest();
        return $this->paginatedResponse($query, $request, 'Payments retrieved');
    }

    private function applyPaymentVisibility($query, Request $request)
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
            // Created by distributor
            $q->where('user_id', $user->id);

            // Payment for a sales invoice linked to their salesman
            $q->orWhereHas('invoice.salesOrder.shop.salesman', function ($sub) use ($distributorId) {
                $sub->where('distributor_id', $distributorId);
            });

            // Payment for a procurement invoice of a PO they created
            $q->orWhereHas('invoice.purchaseOrder', function ($sub) use ($user) {
                $sub->where('ordered_by', $user->id);
            });
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'shop_id' => 'nullable|exists:shops,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:' . implode(',', Payment::allowedMethods()),
            'reference_number' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;

        $payment = Payment::create($data);

        $invoice = Invoice::find($data['invoice_id']);
        $invoice->increment('paid_amount', $data['amount']);
        $invoice->refresh();

        if ($invoice->balance_due <= 0) {
            $invoice->update(['status' => 'Paid']);
        } elseif ($invoice->paid_amount > 0) {
            $invoice->update(['status' => 'Partially Paid']);
        }

        return $this->successResponse($payment->load('invoice'), 'Payment recorded', 201);
    }

    public function show(Request $request, $id)
    {
        $query = Payment::where('company_id', $request->company_id)
            ->with('invoice.shop', 'invoice.purchaseOrder');
        $query = $this->applyPaymentVisibility($query, $request);
        return $this->successResponse($query->findOrFail($id));
    }

    public function destroy(Request $request, $id)
    {
        $query = Payment::where('company_id', $request->company_id);
        $query = $this->applyPaymentVisibility($query, $request);
        $payment = $query->findOrFail($id);

        $invoice = Invoice::find($payment->invoice_id);
        if ($invoice) {
            $invoice->decrement('paid_amount', $payment->amount);
            $invoice->refresh();
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
