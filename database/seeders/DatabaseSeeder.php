<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Material\Distributor;
use App\Models\Material\Supplier;
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

        $superAdmin = User::firstOrCreate(
            ['username' => 'superadmin', 'company_id' => $demoCompany->id],
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('Admin@123'),
                'email'      => 'superadmin@demo.com',
                'phone'      => '9999999999',
                'role'       => 'superadmin',
                'gender'     => 'Male',
                'is_active'  => true,
                'is_delete'  => false,
            ]
        );

        // Super admin permissions — full access
        Permission::firstOrCreate(
            ['role' => 'superadmin'],
            [
                'permissions' => [
                    'product'       => ['create' => true, 'update' => true, 'statusChange' => true],
                    'category'      => ['create' => true, 'update' => true],
                    'subCategory'   => ['create' => true, 'update' => true],
                    'event'         => ['create' => true, 'update' => true, 'statusChange' => true],
                ],
            ]
        );

        // ── Test Company ──
        $testCompany = Company::firstOrCreate(
            ['name' => 'Test Company'],
            ['is_active' => true, 'is_delete' => false]
        );

        User::firstOrCreate(
            ['username' => 'testadmin', 'company_id' => $testCompany->id],
            [
                'name'       => 'Test Admin',
                'password'   => Hash::make('Test@1234'),
                'email'      => 'admin@test.com',
                'phone'      => '1234567890',
                'role'       => 'admin',
                'is_active'  => true,
                'is_delete'  => false,
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

        // ── Demo Distributor with Login ──
        // Create a demo supplier first (needed for FK)
        $demoSupplier = Supplier::firstOrCreate(
            ['name' => 'Demo Supplier', 'company_id' => $demoCompany->id],
            [
                'email'          => 'supplier@demo.com',
                'user_id'        => $superAdmin->id,
                'contact_number' => '9999999998',
            ]
        );

        $distributor = Distributor::firstOrCreate(
            ['name' => 'Demo Distributor', 'company_id' => $demoCompany->id],
            [
                'email'            => 'distributor@demo.com',
                'contact_person'   => 'John Distributor',
                'contact_number'   => '9999999997',
                'supplier_id'      => $demoSupplier->id,
                'user_id'          => $superAdmin->id,
                'is_active'        => true,
                'module_permissions' => [
                    'sales'       => true,
                    'inventory'   => false,
                    'procurement' => true,
                    'customers'   => true,
                    'reports'     => false,
                ],
            ]
        );

        // Create login user for the distributor
        User::firstOrCreate(
            ['username' => 'distributor', 'company_id' => $demoCompany->id],
            [
                'name'            => 'Demo Distributor',
                'email'           => 'distributor@demo.com',
                'password'        => Hash::make('Dist@1234'),
                'role'            => 'distributor',
                'distributor_id'  => $distributor->id,
                'is_active'       => true,
                'is_delete'       => false,
            ]
        );
    }
}
