<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Admin;
use App\Models\Company;
use App\Models\StaffManagement\Staff;
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

        $adminExists = Admin::where('company_id', $company->id)
            ->where('is_active', true)
            ->where('is_delete', false)
            ->exists();

        if (!$adminExists) {
            return $this->errorResponse('No active users found for this company', 404);
        }

        return $this->successResponse([
            'companyId' => $company->id,
            'companyName' => $company->name,
        ], 'Company verified successfully');
    }

    /**
     * Login admin/staff
     * POST /api/auth/login
     */
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'companyId' => 'required',
        ]);

        $admin = Staff::where('username', strtolower($request->username))
            ->where('company_id', $request->companyId)
            ->with('company:id,name')
            ->first();

        if (!$admin) {
            return $this->errorResponse('Invalid username or password', 404);
        }

        $role = strtolower($admin->designation ?? '');

        if ($role === 'supervisor' || $role === 'worker') {
            return $this->errorResponse('Access denied: Supervisor and Worker are not allowed to log in.', 404);
        }

        if (!Hash::check($request->password, $admin->password)) {
            return $this->errorResponse('Invalid username or password', 404);
        }

        $token = $this->generateJwt([
            'id' => $admin->id,
            'username' => $admin->username,
            'companyId' => $admin->company_id,
            'userType' => $admin->designation,
        ]);

        $admin->makeHidden(['password']);

        return $this->authResponse($token, $admin);
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
            'full_name' => $request->fullName,
            'email' => $request->email,
            'username' => strtolower($request->username),
            'password' => Hash::make($request->password),
            'contact_number' => $request->contactNumber,
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
