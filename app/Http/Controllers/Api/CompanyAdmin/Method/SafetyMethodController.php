<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Method;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Method\SafetyMethod;
use Illuminate\Http\Request;

class SafetyMethodController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $methods = SafetyMethod::where('company_id', $request->company_id)
            ->with('category')
            ->latest()->get();
        return $this->successResponse($methods);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'videoLink' => 'required|string',
            'categoryId' => 'required|exists:method_safety_categories,id',
        ]);
        $data['company_id'] = $request->company_id;
        if (isset($data['videoLink'])) { $data['video_link'] = $data['videoLink']; unset($data['videoLink']); }
        if (isset($data['categoryId'])) { $data['category_id'] = $data['categoryId']; unset($data['categoryId']); }
        $method = SafetyMethod::create($data);
        return $this->successResponse($method, 'Safety method created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $method = SafetyMethod::where('company_id', $request->company_id)
            ->with('category')
            ->findOrFail($id);
        return $this->successResponse($method);
    }

    public function update(Request $request, $id)
    {
        $method = SafetyMethod::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'videoLink' => 'sometimes|string',
            'categoryId' => 'sometimes|exists:method_safety_categories,id',
            'isActive' => 'sometimes|boolean',
        ]);
        if (isset($data['videoLink'])) { $data['video_link'] = $data['videoLink']; unset($data['videoLink']); }
        if (isset($data['categoryId'])) { $data['category_id'] = $data['categoryId']; unset($data['categoryId']); }
        if (isset($data['isActive'])) { $data['is_active'] = $data['isActive']; unset($data['isActive']); }
        $method->update($data);
        return $this->successResponse($method, 'Safety method updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $method = SafetyMethod::where('company_id', $request->company_id)->findOrFail($id);
        $method->delete();
        return $this->successResponse(null, 'Safety method deleted successfully');
    }
}
