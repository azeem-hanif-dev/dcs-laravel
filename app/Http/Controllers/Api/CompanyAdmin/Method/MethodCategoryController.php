<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Method;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Method\MethodCategory;
use Illuminate\Http\Request;

class MethodCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $categories = MethodCategory::latest()->get();
        return $this->successResponse($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:method_categories,name']);
        $category = MethodCategory::create($data);
        return $this->successResponse($category, 'Method category created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $category = MethodCategory::findOrFail($id);
        return $this->successResponse($category);
    }

    public function update(Request $request, $id)
    {
        $category = MethodCategory::findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:255|unique:method_categories,name,' . $id]);
        $category->update($data);
        return $this->successResponse($category, 'Method category updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $category = MethodCategory::findOrFail($id);
        $category->delete();
        return $this->successResponse(null, 'Method category deleted successfully');
    }
}
