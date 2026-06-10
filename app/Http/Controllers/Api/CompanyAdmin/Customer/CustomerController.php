<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Customer::where('company_id', $request->company_id)->latest();
        return $this->paginatedResponse($query, $request, 'Customers retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'contactPerson1' => 'nullable|string',
            'contactPerson2' => 'nullable|string',
            'phone' => 'required|string',
            'countryCode' => 'required|string',
            'country' => 'required|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'password' => 'required|string|min:6',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $data['contact_person1'] = $data['contactPerson1'] ?? null;
        $data['contact_person2'] = $data['contactPerson2'] ?? null;
        $data['country_code'] = $data['countryCode'];
        unset($data['contactPerson1'], $data['contactPerson2'], $data['countryCode']);
        $data['password'] = Hash::make($data['password']);
        $customer = Customer::create($data);
        return $this->successResponse($customer, 'Customer created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $customer = Customer::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'contactPerson1' => 'nullable|string',
            'contactPerson2' => 'nullable|string',
            'phone' => 'sometimes|string',
            'countryCode' => 'sometimes|string',
            'country' => 'sometimes|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
        ]);
        if (isset($data['contactPerson1'])) { $data['contact_person1'] = $data['contactPerson1']; unset($data['contactPerson1']); }
        if (isset($data['contactPerson2'])) { $data['contact_person2'] = $data['contactPerson2']; unset($data['contactPerson2']); }
        if (isset($data['countryCode'])) { $data['country_code'] = $data['countryCode']; unset($data['countryCode']); }
        $customer->update($data);
        return $this->successResponse($customer, 'Customer updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $customer = Customer::where('company_id', $request->company_id)->findOrFail($id);
        $customer->update(['is_delete' => true]);
        return $this->successResponse(null, 'Customer deleted successfully');
    }
}
