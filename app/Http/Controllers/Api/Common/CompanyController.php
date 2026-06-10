<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $companies = Company::where('is_delete', false)->latest()->get();
        return $this->successResponse($companies);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:companies,name']);
        $company = Company::create($data);
        return $this->successResponse($company, 'Company created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        return $this->successResponse($company);
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255|unique:companies,name,' . $id,
            'isActive' => 'sometimes|boolean',
        ]);
        if (isset($data['isActive'])) { $data['is_active'] = $data['isActive']; unset($data['isActive']); }
        $company->update($data);
        return $this->successResponse($company, 'Company updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $company->update(['is_delete' => true]);
        return $this->successResponse(null, 'Company deleted successfully');
    }
}
