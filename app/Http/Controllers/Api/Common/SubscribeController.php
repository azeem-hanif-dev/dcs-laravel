<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\PotentialCustomer;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|unique:potential_customers,email']);
        $customer = PotentialCustomer::create([
            'email' => $data['email'],
            'source' => 'subscribe',
            'visit_count' => 1,
        ]);
        return $this->successResponse($customer, 'Subscribed successfully', 201);
    }

    public function potentialCustomers(Request $request)
    {
        $customers = PotentialCustomer::latest()->get();
        return $this->successResponse($customers);
    }
}
