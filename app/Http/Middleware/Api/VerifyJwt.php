<?php

namespace App\Http\Middleware\Api;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Http\Request;

class VerifyJwt
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            $token = $request->query('S_S_Token');
        }

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Not Authorize to Access this Route',
            ], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(config('app.jwt_secret'), 'HS256'));
            $user = User::find($decoded->id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or Invalid S_S_Token',
                ], 404);
            }

            $request->merge(['auth_user' => $user]);
            $request->merge(['company_id' => $user->company_id]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not Authorize to Access this Route',
            ], 401);
        }

        return $next($request);
    }
}
