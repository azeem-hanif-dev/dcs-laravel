<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Warehouse::where('company_id', $request->company_id)->latest();
        return $this->paginatedResponse($query, $request, 'Warehouses retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);
        $data['company_id'] = $request->company_id;
        $warehouse = Warehouse::create($data);
        return $this->successResponse($warehouse, 'Warehouse created', 201);
    }

    public function show($id) { return $this->successResponse(Warehouse::findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);
        $warehouse->update($data);
        return $this->successResponse($warehouse, 'Warehouse updated');
    }

    public function destroy($id)
    {
        Warehouse::findOrFail($id)->delete();
        return $this->successResponse(null, 'Warehouse deleted');
    }
}
