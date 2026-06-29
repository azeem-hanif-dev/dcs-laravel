<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Material\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = Supplier::where('company_id', $request->company_id);
        $query = $this->applySupplierVisibility($query, $request);
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%");
            });
        }
        $query->latest();
        return $this->paginatedResponse($query, $request, 'Suppliers retrieved');
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contactPerson' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'contactNumber' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'payment_terms' => 'nullable|string',
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
        $query = Supplier::where('company_id', $request->company_id);
        $query = $this->applySupplierVisibility($query, $request);
        return $this->successResponse($query->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $query = Supplier::where('company_id', $request->company_id);
        $query = $this->applySupplierVisibility($query, $request);
        $supplier = $query->findOrFail($id);

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
        $this->authorizeAdmin($request);
        $query = Supplier::where('company_id', $request->company_id);
        $query = $this->applySupplierVisibility($query, $request);
        $supplier = $query->findOrFail($id);
        $supplier->delete();
        return $this->successResponse(null, 'Supplier deleted successfully');
    }

    /**
     * Distributor sees their linked supplier (via distributor.supplier_id) or suppliers they created.
     */
    private function applySupplierVisibility($query, Request $request)
    {
        $user = $request->auth_user;
        if (!$user || in_array($user->role, ['superadmin', 'admin'])) return $query;
        if ($user->role !== 'distributor') return $query->where('user_id', $user->id);

        $distributor = \App\Models\Material\Distributor::find($user->distributor_id);
        $supplierId = $distributor?->supplier_id;

        return $query->where(function ($q) use ($user, $supplierId) {
            $q->where('user_id', $user->id);
            if ($supplierId) $q->orWhere('id', $supplierId);
        });
    }

    private function authorizeAdmin(Request $request)
    {
        $user = $request->auth_user;
        if (!$user || !in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'Only superadmin/admin can manage suppliers.');
        }
    }
}
