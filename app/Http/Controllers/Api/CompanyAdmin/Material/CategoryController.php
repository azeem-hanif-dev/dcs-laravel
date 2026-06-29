<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Material\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = Category::where('company_id', $request->company_id)->with('subcategories');
        $query = $this->applyCategoryVisibility($query, $request);
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $query->latest();
        return $this->paginatedResponse($query, $request, 'Categories retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $category = Category::create($data);
        return $this->successResponse($category, 'Category created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $query = Category::where('company_id', $request->company_id)->with('subcategories');
        $query = $this->applyCategoryVisibility($query, $request);
        return $this->successResponse($query->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $query = Category::where('company_id', $request->company_id);
        $query = $this->applyCategoryVisibility($query, $request);
        $category = $query->findOrFail($id);

        $data = $request->validate(['name' => 'required|string|max:255']);
        $category->update($data);
        return $this->successResponse($category, 'Category updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $query = Category::where('company_id', $request->company_id);
        $query = $this->applyCategoryVisibility($query, $request);
        $category = $query->findOrFail($id);
        $category->delete();
        return $this->successResponse(null, 'Category deleted successfully');
    }

    /**
     * Distributor sees: categories they created + categories used by their products.
     */
    private function applyCategoryVisibility($query, Request $request)
    {
        $user = $request->auth_user;
        if (!$user || in_array($user->role, ['superadmin', 'admin'])) return $query;
        if ($user->role !== 'distributor') return $query->where('user_id', $user->id);

        // Get product IDs visible to this distributor
        $productQuery = \App\Models\Material\Material::where('company_id', $request->company_id);
        $distributor = \App\Models\Material\Distributor::find($user->distributor_id);
        $supplierId = $distributor?->supplier_id;

        $productIds = $productQuery->where(function ($q) use ($user, $supplierId) {
            $q->where('user_id', $user->id);
            if ($supplierId) $q->orWhere('supplier_id', $supplierId);
        })->pluck('category_id')->unique()->filter()->toArray();

        return $query->where(function ($q) use ($user, $productIds) {
            $q->where('user_id', $user->id);
            if (!empty($productIds)) $q->orWhereIn('id', $productIds);
        });
    }
}
