<?php

namespace App\Http\Resources;

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
}
