<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Customer\Quotation;
use App\Models\Customer\QuoteDetail;
use App\Models\StaffManagement\Staff;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $quotations = Quotation::where('company_id', $request->company_id)
            ->with('quoteDetails.worker')
            ->latest()->get();
        return $this->successResponse($quotations);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'companyname' => 'required|string',
            'date' => 'required|date',
            'address' => 'required|string',
            'validity' => 'required|date',
            'contactPerson' => 'required|string',
            'phone' => 'required|string',
            'type' => 'required|in:hourly,daily,monthly,extra_hours',
            'grandtotal' => 'required|numeric|min:0',
            'quotedetails' => 'nullable|array',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $data['contact_person'] = $data['contactPerson'];
        unset($data['contactPerson']);

        $quoteDetails = $data['quotedetails'] ?? [];
        unset($data['quotedetails']);

        $quotation = Quotation::create($data);

        foreach ($quoteDetails as $detail) {
            QuoteDetail::create([
                'quotation_id' => $quotation->id,
                'worker_id' => $detail['worker'] ?? $detail['worker_id'],
                'total_workers' => $detail['totalWorkers'] ?? 1,
                'rate' => $detail['rate'] ?? 0,
                'hours' => $detail['hours'] ?? 0,
                'days' => $detail['days'] ?? 0,
                'extra_hours' => $detail['extraHours'] ?? 0,
                'discount' => $detail['discount'] ?? 0,
                'net_rate' => $detail['netRate'] ?? 0,
                'price' => $detail['price'] ?? 0,
            ]);
        }

        return $this->successResponse($quotation->load('quoteDetails.worker'), 'Quotation created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $quotation = Quotation::where('company_id', $request->company_id)
            ->with('quoteDetails.worker')->findOrFail($id);
        return $this->successResponse($quotation);
    }

    public function update(Request $request, $id)
    {
        $quotation = Quotation::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'companyname' => 'sometimes|string',
            'date' => 'sometimes|date',
            'address' => 'sometimes|string',
            'validity' => 'sometimes|date',
            'contactPerson' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'type' => 'sometimes|in:hourly,daily,monthly,extra_hours',
            'grandtotal' => 'sometimes|numeric|min:0',
        ]);
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        $quotation->update($data);
        return $this->successResponse($quotation, 'Quotation updated successfully');
    }

    public function workers(Request $request)
    {
        $workers = Staff::where('company_id', $request->company_id)
            ->where('designation', 'worker')->get(['id', 'name', 'username']);
        return $this->successResponse($workers);
    }
}
