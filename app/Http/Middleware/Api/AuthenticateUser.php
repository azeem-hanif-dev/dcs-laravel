<?php

namespace App\Http\Middleware\Api;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\EndUser;
use Illuminate\Http\Request;

class AuthenticateUser
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['message' => 'Unauthorized: No S_S_Token provided'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key(config('app.user_jwt_secret'), 'HS256'));
            $user = EndUser::where('email', $decoded->email)->first();

            if (!$user) {
                return response()->json(['message' => 'Invalid S_S_Token'], 403);
            }

            $request->merge(['end_user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }

        return $next($request);
    }
}
