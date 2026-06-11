<?php

namespace App\Http\Middleware\Api;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            // Check if token is blacklisted (logout)
            if (DB::table('jwt_blacklist')->where('token_hash', hash('sha256', $token))->exists()) {
                return response()->json(['success'=>false,'message'=>'Token has been revoked. Please login again.'], 401);
            }

            $user = User::find($decoded->id);

            if (!$user || !$user->is_active || $user->is_delete) {
                return response()->json(['success'=>false,'message'=>'User not found or inactive'], 404);
            }

            $request->merge(['auth_user' => $user]);
            $request->merge(['company_id' => $user->company_id]);

        } catch (ExpiredException $e) {
            return response()->json(['success'=>false,'message'=>'Token expired. Please login again.','expired'=>true], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not Authorize to Access this Route',
            ], 401);
        }

        return $next($request);
    }
}
