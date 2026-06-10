<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Logger;
use Illuminate\Http\Request;

class LoggerController extends Controller
{
    use ApiResponse;

    public function getAll(Request $request)
    {
        $loggers = Logger::latest()->paginate($request->per_page ?? 50);
        return $this->successResponse($loggers);
    }
}
