<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Material;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Traits\DistributorVisibility;
use App\Models\Material\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ApiResponse, DistributorVisibility;

    public function index(Request $request)
    {
        $query = Subcategory::where('company_id', $request->company_id);
        if ($request->categoryId) {
            $query->where('category_id', $request->categoryId);
        }
        $query = $this->applyVisibility($query, $request);
        return $this->paginatedResponse($query->with('category')->latest(), $request, 'Subcategories retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'categoryId' => 'required|exists:categories,id',
        ]);
        $subcategory = Subcategory::create([
            'name' => $data['name'],
            'category_id' => $data['categoryId'],
            'user_id' => $request->auth_user->id,
            'company_id' => $request->company_id,
        ]);
        return $this->successResponse($subcategory, 'Subcategory created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $query = Subcategory::where('company_id', $request->company_id)->with('category');
        $query = $this->applyVisibility($query, $request);
        return $this->successResponse($query->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $query = Subcategory::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request);
        $subcategory = $query->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string',
            'categoryId' => 'sometimes|exists:categories,id',
        ]);
        if (isset($data['categoryId'])) { $data['category_id'] = $data['categoryId']; unset($data['categoryId']); }
        $subcategory->update($data);
        return $this->successResponse($subcategory, 'Subcategory updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $query = Subcategory::where('company_id', $request->company_id);
        $query = $this->applyVisibility($query, $request);
        $subcategory = $query->findOrFail($id);
        $subcategory->delete();
        return $this->successResponse(null, 'Subcategory deleted successfully');
    }
}
