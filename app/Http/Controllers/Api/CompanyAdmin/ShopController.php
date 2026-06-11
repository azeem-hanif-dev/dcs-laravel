<?php

namespace App\Http\Controllers\Api\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Shop::where('company_id', $request->company_id)
            ->with('salesman')->latest();
        return $this->paginatedResponse($query, $request, 'Shops retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'area' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'shop_type' => 'nullable|in:retail,wholesale,supermarket,online,other',
            'contact_person' => 'nullable|string|max:255',
            'salesman_id' => 'nullable|exists:salesmen,id',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);
        $data['company_id'] = $request->company_id;
        $data['user_id'] = $request->auth_user->id;
        $shop = Shop::create($data);
        return $this->successResponse($shop->load('salesman'), 'Shop created', 201);
    }

    public function show($id)
    {
        return $this->successResponse(
            Shop::with('salesman', 'salesOrders', 'invoices')->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'area' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'shop_type' => 'nullable|in:retail,wholesale,supermarket,online,other',
            'contact_person' => 'nullable|string|max:255',
            'salesman_id' => 'nullable|exists:salesmen,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        $shop->update($data);
        return $this->successResponse($shop->load('salesman'), 'Shop updated');
    }

    public function destroy($id)
    {
        Shop::findOrFail($id)->delete();
        return $this->successResponse(null, 'Shop deleted');
    }
}
