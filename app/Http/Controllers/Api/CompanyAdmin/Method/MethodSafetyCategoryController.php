<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Method;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Method\MethodSafetyCategory;
use Illuminate\Http\Request;

class MethodSafetyCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $categories = MethodSafetyCategory::latest()->get();
        return $this->successResponse($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:method_safety_categories,name']);
        $category = MethodSafetyCategory::create($data);
        return $this->successResponse($category, 'Safety category created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $category = MethodSafetyCategory::findOrFail($id);
        return $this->successResponse($category);
    }

    public function update(Request $request, $id)
    {
        $category = MethodSafetyCategory::findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:255|unique:method_safety_categories,name,' . $id]);
        $category->update($data);
        return $this->successResponse($category, 'Safety category updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $category = MethodSafetyCategory::findOrFail($id);
        $category->delete();
        return $this->successResponse(null, 'Safety category deleted successfully');
    }
}
