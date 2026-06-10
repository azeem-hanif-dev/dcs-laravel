<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function successResponse($data = null, $message = 'Success', $code = 200)
    {
        $response = ['status' => true, 'message' => $message];
        if ($data !== null) $response['data'] = $data;
        return response()->json($response, $code);
    }

    protected function errorResponse($message = 'Error', $code = 400)
    {
        return response()->json(['status' => false, 'message' => $message], $code);
    }

    protected function authResponse($token, $data, $message = 'Login successful')
    {
        return response()->json([
            'status' => true,
            'S_S_Token' => $token,
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    /**
     * Paginate a query builder and return a standardized API response.
     * If per_page param is provided, paginate; otherwise return all results.
     */
    protected function paginatedResponse($query, $request, $message = 'Success')
    {
        $perPage = (int) ($request->get('per_page', $request->get('perPage', 0)));

        if ($perPage > 0) {
            $page = (int) $request->get('page', 1);
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => $paginator,
            ], 200);
        }

        $items = $query->get();
        return $this->successResponse($items, $message);
    }
}
