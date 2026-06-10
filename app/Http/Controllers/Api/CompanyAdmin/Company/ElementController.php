<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Company\Element;
use Illuminate\Http\Request;

class ElementController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $elements = Element::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($elements);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:50']);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $element = Element::create($data);
        return $this->successResponse($element, 'Element created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $element = Element::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($element);
    }

    public function update(Request $request, $id)
    {
        $element = Element::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:50']);
        $element->update($data);
        return $this->successResponse($element, 'Element updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $element = Element::where('company_id', $request->company_id)->findOrFail($id);
        $element->delete();
        return $this->successResponse(null, 'Element deleted successfully');
    }
}
