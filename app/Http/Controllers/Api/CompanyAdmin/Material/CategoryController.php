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
        $query = $this->applyVisibility($query, $request);
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
        $query = $this->applyVisibility($query, $request);
        return $this->successResponse($query->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $query = Category::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request);
        $category = $query->findOrFail($id);

        $data = $request->validate(['name' => 'required|string|max:255']);
        $category->update($data);
        return $this->successResponse($category, 'Category updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $query = Category::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request);
        $category = $query->findOrFail($id);
        $category->delete();
        return $this->successResponse(null, 'Category deleted successfully');
    }
}
