<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Company;
use App\Models\StaffManagement\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'Test Company'],
            ['is_active' => true, 'is_delete' => false]
        );

        $admin = Admin::firstOrCreate(
            ['username' => 'testadmin', 'company_id' => $company->id],
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
            ['username' => 'teststaff', 'company_id' => $company->id],
            [
                'user_id' => $admin->id,
                'name' => 'Test Staff',
                'password' => Hash::make('Test@1234'),
                'email' => 'staff@test.com',
                'employee_code' => 'EMP001',
                'phone' => '1234567890',
                'designation' => 'admin',
            ]
        );
    }
}
