<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Company\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $floors = Floor::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($floors);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:50']);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $floor = Floor::create($data);
        return $this->successResponse($floor, 'Floor created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $floor = Floor::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($floor);
    }

    public function update(Request $request, $id)
    {
        $floor = Floor::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:50']);
        $floor->update($data);
        return $this->successResponse($floor, 'Floor updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $floor = Floor::where('company_id', $request->company_id)->findOrFail($id);
        $floor->delete();
        return $this->successResponse(null, 'Floor deleted successfully');
    }
}
