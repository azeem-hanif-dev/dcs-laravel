<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

class CompanyFilter
{
    public function handle(Request $request, Closure $next)
    {
        $companyId = $request->company_id;

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company ID is required for this operation',
            ], 400);
        }

        $request->merge(['company_id' => $companyId]);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $request->merge(['company_id' => $companyId]);
        }

        return $next($request);
    }
}
