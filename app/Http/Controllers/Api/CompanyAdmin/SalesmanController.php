<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Salesman;
use Illuminate\Http\Request;

class SalesmanController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Salesman::where('company_id', $request->company_id)
            ->with('distributor')->latest();
        return $this->paginatedResponse($query, $request, 'Salesmen retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'distributor_id' => 'nullable|exists:distributors,id',
            'territory' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:0',
        ]);
        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;
        $salesman = Salesman::create($data);
        return $this->successResponse($salesman, 'Salesman created', 201);
    }

    public function show($id) { return $this->successResponse(Salesman::with('distributor')->findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $salesman = Salesman::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'distributor_id' => 'nullable|exists:distributors,id',
            'territory' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        $salesman->update($data);
        return $this->successResponse($salesman, 'Salesman updated');
    }

    public function destroy($id)
    {
        Salesman::findOrFail($id)->delete();
        return $this->successResponse(null, 'Salesman deleted');
    }
}
