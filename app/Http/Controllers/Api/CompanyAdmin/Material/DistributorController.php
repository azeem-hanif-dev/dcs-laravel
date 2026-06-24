<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Material\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DistributorController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = Distributor::where('company_id', $request->company_id)
            ->with('supplier');
        $query = $this->applyVisibility($query, $request);
        $query->latest();
        return $this->paginatedResponse($query, $request, 'Distributors retrieved');
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
        // Only admins and superadmins may create distributors or set module permissions / login credentials
        $adminRoles = ['superadmin', 'admin'];
        $isAdmin = in_array($request->auth_user->role, $adminRoles);

        // Decode JSON fields that arrive as strings via FormData
        if ($request->has('modulePermissions') && is_string($request->modulePermissions)) {
            $request->merge(['modulePermissions' => json_decode($request->modulePermissions, true) ?? []]);
        }
        if ($request->has('createLogin') && is_string($request->createLogin)) {
            $request->merge(['createLogin' => filter_var($request->createLogin, FILTER_VALIDATE_BOOLEAN)]);
        }

        // Reject non-admin attempts to set module permissions or create login accounts
        if (!$isAdmin && ($request->has('modulePermissions') || $request->boolean('createLogin'))) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contactPerson' => 'nullable|string',
            'contactNumber' => 'nullable|string',
            'address' => 'nullable|string',
            'supplierId' => 'required|exists:suppliers,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'modulePermissions' => 'nullable|array',
            'createLogin' => 'nullable|boolean',
            'loginUsername' => 'nullable|string|required_if:createLogin,true',
            'loginPassword' => 'nullable|string|min:6|required_if:createLogin,true',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $data['supplier_id'] = $data['supplierId'];
        unset($data['supplierId']);
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        if (isset($data['contactNumber'])) { $data['contact_number'] = $data['contactNumber']; unset($data['contactNumber']); }
        if (isset($data['modulePermissions'])) { $data['module_permissions'] = $data['modulePermissions']; unset($data['modulePermissions']); }
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('distributors/logos', 'public');
        }

        $createLogin = $data['createLogin'] ?? false;
        $loginUsername = $data['loginUsername'] ?? null;
        $loginPassword = $data['loginPassword'] ?? null;
        unset($data['createLogin'], $data['loginUsername'], $data['loginPassword']);

        $distributor = Distributor::create($data);

        // Optionally create a login user account for this distributor
        if ($createLogin && $loginUsername && $loginPassword) {
            $distributor->syncLoginUser([
                'name'     => $data['contact_person'] ?? $data['name'],
                'username' => strtolower($loginUsername),
                'email'    => $data['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($loginPassword),
            ]);
        }

        $distributor->load('supplier');
        return $this->successResponse($distributor, 'Distributor created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $query = Distributor::where('company_id', $request->company_id)->with('supplier');
        $query = $this->applyVisibility($query, $request);
        return $this->successResponse($query->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $adminRoles = ['superadmin', 'admin'];
        $isAdmin = in_array($request->auth_user->role, $adminRoles);

        // Decode JSON fields that arrive as strings via FormData
        if ($request->has('modulePermissions') && is_string($request->modulePermissions)) {
            $request->merge(['modulePermissions' => json_decode($request->modulePermissions, true) ?? []]);
        }

        // Reject non-admin attempts to set module permissions
        if (!$isAdmin && $request->has('modulePermissions')) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $query = Distributor::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request);
        $distributor = $query->findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'contactPerson' => 'nullable|string',
            'contactNumber' => 'nullable|string',
            'address' => 'nullable|string',
            'supplierId' => 'sometimes|exists:suppliers,id',
            'isActive' => 'sometimes|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'modulePermissions' => 'nullable|array',
        ]);
        if (isset($data['contactPerson'])) { $data['contact_person'] = $data['contactPerson']; unset($data['contactPerson']); }
        if (isset($data['contactNumber'])) { $data['contact_number'] = $data['contactNumber']; unset($data['contactNumber']); }
        if (isset($data['supplierId'])) { $data['supplier_id'] = $data['supplierId']; unset($data['supplierId']); }
        if (isset($data['isActive'])) { $data['is_active'] = $data['isActive']; unset($data['isActive']); }
        if (isset($data['modulePermissions'])) { $data['module_permissions'] = $data['modulePermissions']; unset($data['modulePermissions']); }
        if ($request->hasFile('logo')) {
            if ($distributor->logo) { Storage::disk('public')->delete($distributor->logo); }
            $data['logo'] = $request->file('logo')->store('distributors/logos', 'public');
        }
        $distributor->update($data);
        return $this->successResponse($distributor, 'Distributor updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $query = Distributor::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request);
        $distributor = $query->findOrFail($id);

        if ($distributor->logo) { Storage::disk('public')->delete($distributor->logo); }
        $distributor->delete();
        return $this->successResponse(null, 'Distributor deleted successfully');
    }
}
