<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\User;
use App\Models\Company;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Verify company name exists and has active users
     * POST /api/auth/verify-company
     */
    public function verifyCompany(Request $request)
    {
        $request->validate(['companyName' => 'required|string']);

        $company = Company::where('name', trim($request->companyName))
            ->where('is_active', true)
            ->where('is_delete', false)
            ->first();

        if (!$company) {
            return $this->errorResponse('Company not found or inactive', 404);
        }

        $userExists = User::where('company_id', $company->id)
            ->where('is_active', true)
            ->where('is_delete', false)
            ->whereIn('role', ['superadmin', 'admin', 'distributor'])
            ->exists();

        if (!$userExists) {
            return $this->errorResponse('No active users found for this company', 404);
        }

        return $this->successResponse([
            'companyId' => $company->id,
            'companyName' => $company->name,
        ], 'Company verified successfully');
    }

    /**
     * Login admin / distributor
     * POST /api/auth/login
     */
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'companyId' => 'required',
        ]);

        $user = User::where('username', strtolower($request->username))
            ->where('company_id', $request->companyId)
            ->whereIn('role', ['superadmin', 'admin', 'distributor'])
            ->where('is_active', true)
            ->with(['company:id,name', 'distributor:id,name,module_permissions'])
            ->first();

        if (!$user) {
            return $this->errorResponse('Invalid username or password', 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid username or password', 404);
        }

        $payload = [
            'id'            => $user->id,
            'username'      => $user->username,
            'companyId'     => $user->company_id,
            'userType'      => $user->role,
            'distributorId' => $user->distributor_id,
            'modules'       => $user->getAllowedModules(),
        ];

        $token = $this->generateJwt($payload);

        $user->makeHidden(['password']);
        $user->append('allowed_modules');

        return $this->authResponse($token, $user);
    }

    /**
     * Register admin
     * POST /api/v1/admin/register
     */
    public function signupAdmin(Request $request)
    {
        $request->validate([
            'fullName' => 'required|string|max:28',
            'email' => 'required|email',
            'username' => 'required|string',
            'password' => 'required|string|min:8',
            'contactNumber' => 'required|string',
            'gender' => 'required|in:Male,Female,Others',
            'role' => 'required|string',
            'companyId' => 'required|exists:companies,id',
            'address' => 'nullable|string',
            'dateOfBirth' => 'nullable|date|before:today',
        ]);

        $existing = Admin::where('username', strtolower($request->username))
            ->where('company_id', $request->companyId)
            ->where('is_delete', false)
            ->exists();

        if ($existing) {
            return $this->errorResponse('Username already exists for this company', 409);
        }

        $admin = Admin::create([
            'name' => $request->fullName,
            'email' => $request->email,
            'username' => strtolower($request->username),
            'password' => Hash::make($request->password),
            'phone' => $request->contactNumber,
            'gender' => $request->gender,
            'role' => $request->role,
            'company_id' => $request->companyId,
            'address' => $request->address,
            'date_of_birth' => $request->dateOfBirth,
        ]);

        $token = $this->generateJwt([
            'id' => $admin->id,
            'username' => $admin->username,
            'companyId' => $admin->company_id,
            'userType' => 'admin',
        ]);

        $admin->makeHidden(['password']);

        return response()->json([
            'status' => true,
            'message' => 'Admin registered successfully',
            'token' => $token,
            'data' => $admin,
        ], 201);
    }

    /**
     * Login staff
     * POST /api/auth/staff/login
     */
    public function loginStaff(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'companyId' => 'required',
        ]);

        $staff = Staff::where('username', strtolower($request->username))
            ->where('company_id', $request->companyId)
            ->with(['company:id,name', 'agency:id,name', 'jobType:id,name', 'shift:id,title'])
            ->first();

        if (!$staff || !Hash::check($request->password, $staff->password)) {
            return $this->errorResponse('Invalid username or password', 404);
        }

        $token = $this->generateJwt([
            'id' => $staff->id,
            'username' => $staff->username,
            'companyId' => $staff->company_id,
            'designation' => $staff->designation,
            'userType' => 'staff',
        ]);

        $staff->makeHidden(['password']);

        return $this->authResponse($token, $staff, 'Staff login successful');
    }

    /**
     * Staff mobile signup
     * POST /api/auth/staff/signup
     */
    public function signupStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|same:password',
            'companyId' => 'required|exists:companies,id',
        ]);

        $existing = Staff::where('username', strtolower($request->username))
            ->where('company_id', $request->companyId)
            ->exists();

        if ($existing) {
            return $this->errorResponse('Username already exists in this company', 409);
        }

        $staff = Staff::create([
            'user_id' => 1, // default admin user
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => $request->email,
            'employee_code' => 'EMP-001',
            'phone' => '00000000000',
            'designation' => 'worker',
            'password' => Hash::make($request->password),
            'company_id' => $request->companyId,
            'gender' => 'Male',
            'permission' => ['approveLeaves' => false, 'giveFeedback' => true],
            'mobile_signup' => true,
        ]);

        $token = $this->generateJwt([
            'id' => $staff->id,
            'username' => $staff->username,
            'companyId' => $staff->company_id,
            'designation' => $staff->designation,
            'userType' => 'staff',
        ]);

        $staff->makeHidden(['password']);

        return response()->json([
            'status' => true,
            'message' => 'Staff registered successfully',
            'token' => $token,
            'data' => $staff,
        ], 201);
    }

    /**
     * Forgot password - send reset link
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'companyId' => 'required',
        ]);

        $admin = Staff::where('email', $request->email)
            ->where('company_id', $request->companyId)
            ->first();

        if (!$admin) {
            return $this->errorResponse('Admin not found', 404);
        }

        $token = JWT::encode([
            'email' => $admin->email,
            'companyId' => $request->companyId,
            'exp' => time() + 300, // 5 minutes
        ], config('app.jwt_secret'), 'HS256');

        $frontendURL = $request->headers->get('origin');
        $resetURL = "{$frontendURL}/reset-password/{$token}";

        try {
            Mail::html(
                "<p>You requested for a password reset</p>
                 <p>Click this <a href=\"{$resetURL}\">link</a> to reset your password</p>",
                function ($message) use ($admin) {
                    $message->to($admin->email)
                        ->subject('Password Reset');
                }
            );
        } catch (\Exception $e) {
            // Log error but still return success
        }

        return $this->successResponse(null, 'Password reset link sent to your email');
    }

    /**
     * Reset password with token
     * POST /api/auth/reset-password/{S_S_Token}
     */
    public function resetPassword(Request $request, $S_S_Token)
    {
        $request->validate(['password' => 'required|string|min:8']);

        try {
            $decoded = JWT::decode($S_S_Token, new \Firebase\JWT\Key(config('app.jwt_secret'), 'HS256'));

            $admin = Admin::where('email', $decoded->email)
                ->where('company_id', $decoded->companyId)
                ->where('is_active', true)
                ->where('is_delete', false)
                ->first();

            if (!$admin) {
                return $this->errorResponse('Invalid or expired token', 400);
            }

            $admin->password = Hash::make($request->password);
            $admin->save();

            return $this->successResponse(null, 'Password has been reset');
        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->errorResponse('Token has expired', 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Invalid or expired token', 400);
        }
    }

    /**
     * Refresh JWT token
     * POST /api/auth/refresh
     */
    public function refreshToken(Request $request)
    {
        try {
            $token = $request->bearerToken() ?? $request->S_S_Token;
            $decoded = JWT::decode($token, new \Firebase\JWT\Key(config('app.jwt_secret'), 'HS256'));
            if (\Illuminate\Support\Facades\DB::table('jwt_blacklist')->where('token_hash', hash('sha256', $token))->exists()) {
                return $this->errorResponse('Token has been revoked', 401);
            }
            $user = User::find($decoded->id);
            if (!$user || !$user->is_active || $user->is_delete) {
                return $this->errorResponse('User not found or inactive', 404);
            }
            $newToken = $this->generateJwt(['id'=>$user->id,'username'=>$user->username,'companyId'=>$user->company_id,'userType'=>$user->role]);
            return response()->json(['status'=>true,'S_S_Token'=>$newToken,'message'=>'Token refreshed','expiresAt'=>time()+(3*24*60*60)],200);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json(['status'=>false,'message'=>'Token has expired, please login again'],401);
        } catch (\Exception $e) {
            return response()->json(['status'=>false,'message'=>'Invalid token'],401);
        }
    }

    /**
     * Logout - blacklist current token
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken() ?? $request->S_S_Token;
        if ($token) {
            try {
                $decoded = JWT::decode($token, new \Firebase\JWT\Key(config('app.jwt_secret'), 'HS256'));
                $expiresAt = isset($decoded->exp) ? date('Y-m-d H:i:s', $decoded->exp) : now()->addDays(3);
                \Illuminate\Support\Facades\DB::table('jwt_blacklist')->insertOrIgnore(['token_hash'=>hash('sha256',$token),'expires_at'=>$expiresAt,'created_at'=>now(),'updated_at'=>now()]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::table('jwt_blacklist')->insertOrIgnore(['token_hash'=>hash('sha256',$token),'expires_at'=>now()->addDays(3),'created_at'=>now(),'updated_at'=>now()]);
            }
        }
        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Validate current token
     * GET /api/auth/validate
     */
    public function validateToken(Request $request)
    {
        return $this->successResponse(['valid'=>true,'user'=>$request->auth_user->only(['id','name','username','role','company_id'])],'Token is valid');
    }

    /**
     * Get all companies for signup form
     * GET /api/auth/companies
     */
    public function getCompanies()
    {
        $companies = Company::where('is_active', true)
            ->where('is_delete', false)
            ->select('id', 'name')
            ->get();

        return $this->successResponse($companies);
    }

    /**
     * Generate JWT token matching the original format
     */
    private function generateJwt(array $payload): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + (3 * 24 * 60 * 60); // 3 days

        return JWT::encode($payload, config('app.jwt_secret'), 'HS256');
    }
}
