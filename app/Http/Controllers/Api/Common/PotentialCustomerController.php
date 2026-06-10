<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\PotentialCustomer;
use Illuminate\Http\Request;

class PotentialCustomerController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $customers = PotentialCustomer::latest()->get();
        return $this->successResponse($customers);
    }
}
