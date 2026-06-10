<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Material\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $suppliers = Supplier::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($suppliers);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contactPerson' => 'nullable|string',
            'contactNumber' => 'nullable|string',
            'address' => 'nullable|string',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        if (isset($data['contactNumber'])) { $data['contact_number'] = $data['contactNumber']; unset($data['contactNumber']); }
        $supplier = Supplier::create($data);
        return $this->successResponse($supplier, 'Supplier created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $supplier = Supplier::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'contactPerson' => 'nullable|string',
            'contactNumber' => 'nullable|string',
            'address' => 'nullable|string',
            'isActive' => 'sometimes|boolean',
        ]);
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        if (isset($data['contactNumber'])) { $data['contact_number'] = $data['contactNumber']; unset($data['contactNumber']); }
        if (isset($data['isActive'])) { $data['is_active'] = $data['isActive']; unset($data['isActive']); }
        $supplier->update($data);
        return $this->successResponse($supplier, 'Supplier updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $supplier = Supplier::where('company_id', $request->company_id)->findOrFail($id);
        $supplier->delete();
        return $this->successResponse(null, 'Supplier deleted successfully');
    }
}
