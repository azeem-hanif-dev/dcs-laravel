<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Company\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $areas = Area::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($areas);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:50']);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $area = Area::create($data);
        return $this->successResponse($area, 'Area created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $area = Area::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($area);
    }

    public function update(Request $request, $id)
    {
        $area = Area::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:50']);
        $area->update($data);
        return $this->successResponse($area, 'Area updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $area = Area::where('company_id', $request->company_id)->findOrFail($id);
        $area->delete();
        return $this->successResponse(null, 'Area deleted successfully');
    }
}
