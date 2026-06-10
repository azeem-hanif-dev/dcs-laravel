<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Material\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DistributorController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $distributors = Distributor::where('company_id', $request->company_id)
            ->with('supplier')
            ->latest()->get();
        return $this->successResponse($distributors);
    }

    public function bySupplier(Request $request, $supplierId)
    {
        $distributors = Distributor::where('company_id', $request->company_id)
            ->where('supplier_id', $supplierId)
            ->latest()->get();
        return $this->successResponse($distributors);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contactPerson' => 'nullable|string',
            'contactNumber' => 'nullable|string',
            'address' => 'nullable|string',
            'supplierId' => 'required|exists:suppliers,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $data['supplier_id'] = $data['supplierId'];
        unset($data['supplierId']);
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        if (isset($data['contactNumber'])) { $data['contact_number'] = $data['contactNumber']; unset($data['contactNumber']); }
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('distributors/logos', 'public');
        }
        $distributor = Distributor::create($data);
        return $this->successResponse($distributor, 'Distributor created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $distributor = Distributor::where('company_id', $request->company_id)
            ->with('supplier')
            ->findOrFail($id);
        return $this->successResponse($distributor);
    }

    public function update(Request $request, $id)
    {
        $distributor = Distributor::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'contactPerson' => 'nullable|string',
            'contactNumber' => 'nullable|string',
            'address' => 'nullable|string',
            'supplierId' => 'sometimes|exists:suppliers,id',
            'isActive' => 'sometimes|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        if (isset($data['contactNumber'])) { $data['contact_number'] = $data['contactNumber']; unset($data['contactNumber']); }
        if (isset($data['supplierId'])) { $data['supplier_id'] = $data['supplierId']; unset($data['supplierId']); }
        if (isset($data['isActive'])) { $data['is_active'] = $data['isActive']; unset($data['isActive']); }
        if ($request->hasFile('logo')) {
            if ($distributor->logo) { Storage::disk('public')->delete($distributor->logo); }
            $data['logo'] = $request->file('logo')->store('distributors/logos', 'public');
        }
        $distributor->update($data);
        return $this->successResponse($distributor, 'Distributor updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $distributor = Distributor::where('company_id', $request->company_id)->findOrFail($id);
        if ($distributor->logo) { Storage::disk('public')->delete($distributor->logo); }
        $distributor->delete();
        return $this->successResponse(null, 'Distributor deleted successfully');
    }
}
