<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Material\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = Material::where('company_id', $request->company_id)
            ->with(['category', 'subcategory', 'supplier']);
        $query = $this->applyProductVisibility($query, $request);
        $query->latest();
        return $this->paginatedResponse($query, $request, 'Materials retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'materialName' => 'required|string',
            'categoryId' => 'required|exists:categories,id',
            'subcategoryId' => 'nullable|exists:subcategories,id',
            'price' => 'required|numeric|min:0',
            'totalQuantity' => 'required|integer|min:0',
            'assignedQuantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|in:Active,Inactive',
            'uses' => 'nullable|integer|min:0',
            'supplierId' => 'nullable|exists:suppliers,id',
        ]);
        $data['material_name'] = $data['materialName']; unset($data['materialName']);
        $data['category_id'] = $data['categoryId']; unset($data['categoryId']);
        if (isset($data['subcategoryId'])) { $data['subcategory_id'] = $data['subcategoryId']; unset($data['subcategoryId']); }
        $data['total_quantity'] = $data['totalQuantity']; unset($data['totalQuantity']);
        $data['assigned_quantity'] = $data['assignedQuantity']; unset($data['assignedQuantity']);
        if (isset($data['supplierId'])) { $data['supplier_id'] = $data['supplierId']; unset($data['supplierId']); }
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $material = Material::create($data);
        return $this->successResponse($material, 'Material created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $query = Material::where('company_id', $request->company_id)
            ->with(['category', 'subcategory', 'supplier']);
        $query = $this->applyProductVisibility($query, $request);
        return $this->successResponse($query->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $query = Material::where('company_id', $request->company_id);
        $query = $this->applyProductVisibility($query, $request);
        $material = $query->findOrFail($id);

        $data = $request->validate([
            'materialName' => 'sometimes|string',
            'categoryId' => 'sometimes|exists:categories,id',
            'subcategoryId' => 'nullable|exists:subcategories,id',
            'price' => 'sometimes|numeric|min:0',
            'totalQuantity' => 'sometimes|integer|min:0',
            'assignedQuantity' => 'sometimes|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|in:Active,Inactive',
            'uses' => 'nullable|integer|min:0',
            'supplierId' => 'nullable|exists:suppliers,id',
        ]);
        if (isset($data['materialName'])) { $data['material_name'] = $data['materialName']; unset($data['materialName']); }
        if (isset($data['categoryId'])) { $data['category_id'] = $data['categoryId']; unset($data['categoryId']); }
        if (isset($data['subcategoryId'])) { $data['subcategory_id'] = $data['subcategoryId']; unset($data['subcategoryId']); }
        if (isset($data['totalQuantity'])) { $data['total_quantity'] = $data['totalQuantity']; unset($data['totalQuantity']); }
        if (isset($data['assignedQuantity'])) { $data['assigned_quantity'] = $data['assignedQuantity']; unset($data['assignedQuantity']); }
        if (isset($data['supplierId'])) { $data['supplier_id'] = $data['supplierId']; unset($data['supplierId']); }
        $material->update($data);
        return $this->successResponse($material, 'Material updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $query = Material::where('company_id', $request->company_id);
        $query = $this->applyProductVisibility($query, $request);
        $material = $query->findOrFail($id);
        $material->delete();
        return $this->successResponse(null, 'Material deleted successfully');
    }

    public function worker(Request $request)
    {
        $materials = Material::where('company_id', $request->company_id)->where('status', 'Active')->get();
        return $this->successResponse($materials);
    }

    public function remaining(Request $request, $id)
    {
        $material = Material::where('company_id', $request->company_id)->findOrFail($id);
        $remaining = $material->total_quantity - $material->assigned_quantity;
        return $this->successResponse(['remaining' => $remaining]);
    }

    /**
     * Products visible to distributor: created by them OR from their linked supplier.
     */
    private function applyProductVisibility($query, Request $request)
    {
        $user = $request->auth_user;
        if (!$user || in_array($user->role, ['superadmin', 'admin'])) return $query;
        if ($user->role !== 'distributor') return $query->where('user_id', $user->id);

        // Get distributor's supplier_id
        $distributor = \App\Models\Material\Distributor::find($user->distributor_id);
        $supplierId = $distributor?->supplier_id;

        return $query->where(function ($q) use ($user, $supplierId) {
            $q->where('user_id', $user->id);
            if ($supplierId) $q->orWhere('supplier_id', $supplierId);
        });
    }
}
