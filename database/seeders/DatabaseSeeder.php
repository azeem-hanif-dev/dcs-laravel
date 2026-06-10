<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Permission;
use App\Models\StaffManagement\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo Company (Super Admin) ──
        $demoCompany = Company::firstOrCreate(
            ['name' => 'Demo Company'],
            ['is_active' => true, 'is_delete' => false]
        );

        $superAdmin = Admin::firstOrCreate(
            ['username' => 'superadmin', 'company_id' => $demoCompany->id],
            [
                'full_name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'email' => 'superadmin@demo.com',
                'contact_number' => '9999999999',
                'role' => 'superAdmin',
                'gender' => 'Male',
                'is_active' => true,
                'is_delete' => false,
            ]
        );

        Staff::firstOrCreate(
            ['username' => 'superadmin', 'company_id' => $demoCompany->id],
            [
                'user_id' => $superAdmin->id,
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'email' => 'superadmin@demo.com',
                'employee_code' => 'SA-001',
                'phone' => '9999999999',
                'designation' => 'admin',
            ]
        );

        // Super admin permissions — full access
        Permission::firstOrCreate(
            ['role' => 'superAdmin'],
            [
                'permissions' => [
                    'product'       => ['create' => true, 'update' => true, 'statusChange' => true],
                    'category'      => ['create' => true, 'update' => true],
                    'subCategory'   => ['create' => true, 'update' => true],
                    'event'         => ['create' => true, 'update' => true, 'statusChange' => true],
                ],
            ]
        );

        // ── Test Company (Staff Login) ──
        $testCompany = Company::firstOrCreate(
            ['name' => 'Test Company'],
            ['is_active' => true, 'is_delete' => false]
        );

        $testAdmin = Admin::firstOrCreate(
            ['username' => 'testadmin', 'company_id' => $testCompany->id],
            [
                'full_name' => 'Test Admin',
                'password' => Hash::make('Test@1234'),
                'email' => 'admin@test.com',
                'contact_number' => '1234567890',
                'role' => 'admin',
                'is_active' => true,
                'is_delete' => false,
            ]
        );

        Staff::firstOrCreate(
            ['username' => 'teststaff', 'company_id' => $testCompany->id],
            [
                'user_id' => $testAdmin->id,
                'name' => 'Test Staff',
                'password' => Hash::make('Test@1234'),
                'email' => 'staff@test.com',
                'employee_code' => 'EMP001',
                'phone' => '1234567890',
                'designation' => 'admin',
            ]
        );

        // Admin permissions
        Permission::firstOrCreate(
            ['role' => 'admin'],
            [
                'permissions' => [
                    'product'       => ['create' => true, 'update' => true, 'statusChange' => true],
                    'category'      => ['create' => true, 'update' => true],
                    'subCategory'   => ['create' => true, 'update' => true],
                    'event'         => ['create' => true, 'update' => true, 'statusChange' => true],
                ],
            ]
        );
    }
}
