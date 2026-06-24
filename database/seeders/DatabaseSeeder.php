<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Material\Distributor;
use App\Models\Material\Supplier;
use App\Models\Material\Material;
use App\Models\Material\Category;
use App\Models\Warehouse;
use App\Models\Shop;
use App\Models\Salesman;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // ── Demo Supplier ──
        $demoSupplier = Supplier::firstOrCreate(
            ['name' => 'Demo Supplier', 'company_id' => $demoCompany->id],
            [
                'email'          => 'supplier@demo.com',
                'user_id'        => $superAdmin->id,
                'contact_person' => 'Mike Supplier',
                'contact_number' => '9999999998',
                'address'        => '123 Supplier St, Dubai',
                'company_name'   => 'Demo Supply Co.',
                'tax_number'     => 'TAX-001',
            ]
        );

        // ── Demo Distributor with Login ──
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
        $distributorUser = User::firstOrCreate(
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

        // ── Sample Data for Testing ──

        // Category
        $category = Category::firstOrCreate(
            ['name' => 'Cleaning Supplies', 'company_id' => $demoCompany->id],
            ['user_id' => $superAdmin->id]
        );

        // Materials (created by superadmin)
        $material1 = Material::firstOrCreate(
            ['material_name' => 'Floor Cleaner 5L', 'company_id' => $demoCompany->id],
            [
                'category_id'      => $category->id,
                'price'            => 25.00,
                'total_quantity'   => 100,
                'assigned_quantity' => 0,
                'description'      => 'Industrial floor cleaner',
                'status'           => 'Active',
                'supplier_id'      => $demoSupplier->id,
                'user_id'          => $superAdmin->id,
            ]
        );

        $material2 = Material::firstOrCreate(
            ['material_name' => 'Glass Cleaner 1L', 'company_id' => $demoCompany->id],
            [
                'category_id'      => $category->id,
                'price'            => 12.00,
                'total_quantity'   => 200,
                'assigned_quantity' => 0,
                'description'      => 'Streak-free glass cleaner',
                'status'           => 'Active',
                'supplier_id'      => $demoSupplier->id,
                'user_id'          => $superAdmin->id,
            ]
        );

        // Warehouse
        $warehouse = Warehouse::firstOrCreate(
            ['name' => 'Main Warehouse', 'company_id' => $demoCompany->id],
            ['location' => 'Dubai Industrial Area', 'manager_name' => 'Ahmed', 'phone' => '0501234567']
        );

        // Stock records
        Stock::firstOrCreate(
            ['product_id' => $material1->id, 'warehouse_id' => $warehouse->id, 'company_id' => $demoCompany->id],
            ['total_quantity' => 100, 'reserved_quantity' => 0, 'reorder_level' => 10]
        );
        Stock::firstOrCreate(
            ['product_id' => $material2->id, 'warehouse_id' => $warehouse->id, 'company_id' => $demoCompany->id],
            ['total_quantity' => 200, 'reserved_quantity' => 0, 'reorder_level' => 15]
        );

        // Salesman
        $salesman = Salesman::firstOrCreate(
            ['name' => 'Ahmed Salesman', 'company_id' => $demoCompany->id],
            [
                'email'          => 'ahmed@demo.com',
                'phone'          => '0509876543',
                'distributor_id' => $distributor->id,
                'territory'      => 'Downtown Dubai',
                'target_amount'  => 50000.00,
                'user_id'        => $superAdmin->id,
            ]
        );

        // Shop
        $shop = Shop::firstOrCreate(
            ['name' => 'Al Madina Supermarket', 'company_id' => $demoCompany->id],
            [
                'owner_name'    => 'Hassan Ali',
                'email'         => 'almadina@demo.com',
                'phone'         => '044567890',
                'address'       => 'Sheikh Zayed Road',
                'city'          => 'Dubai',
                'shop_type'     => 'supermarket',
                'salesman_id'   => $salesman->id,
                'contact_person' => 'Hassan',
                'credit_limit'  => 10000.00,
                'user_id'       => $superAdmin->id,
            ]
        );

        // ── Purchase Order (created by distributor) ──
        $po = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-20260624-0001', 'company_id' => $demoCompany->id],
            [
                'supplier_id'   => $demoSupplier->id,
                'warehouse_id'  => $warehouse->id,
                'ordered_by'    => $distributorUser->id,
                'order_date'    => '2026-06-24',
                'status'        => 'pending',
                'notes'         => 'Test order for demo',
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po->id, 'material_id' => $material1->id],
            ['quantity' => 50, 'supplier_id' => $demoSupplier->id]
        );
        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po->id, 'material_id' => $material2->id],
            ['quantity' => 30, 'supplier_id' => $demoSupplier->id]
        );

        // ── Second Purchase Order (by superadmin, for comparison) ──
        $po2 = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-20260624-0002', 'company_id' => $demoCompany->id],
            [
                'supplier_id'   => $demoSupplier->id,
                'warehouse_id'  => $warehouse->id,
                'ordered_by'    => $superAdmin->id,
                'order_date'    => '2026-06-24',
                'status'        => 'pending',
                'notes'         => 'Superadmin order',
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po2->id, 'material_id' => $material1->id],
            ['quantity' => 20, 'supplier_id' => $demoSupplier->id]
        );

        // ── Sales Order (created by superadmin) ──
        $so = SalesOrder::firstOrCreate(
            ['so_number' => 'SO-20260624-0001', 'company_id' => $demoCompany->id],
            [
                'shop_id'       => $shop->id,
                'salesman_id'   => $salesman->id,
                'warehouse_id'  => $warehouse->id,
                'order_date'    => '2026-06-24',
                'status'        => 'Draft',
                'subtotal'      => 370.00,
                'grand_total'   => 370.00,
                'user_id'       => $superAdmin->id,
            ]
        );

        SalesOrderItem::firstOrCreate(
            ['sales_order_id' => $so->id, 'product_id' => $material1->id],
            ['quantity' => 10, 'unit_price' => 25.00, 'total' => 250.00]
        );
        SalesOrderItem::firstOrCreate(
            ['sales_order_id' => $so->id, 'product_id' => $material2->id],
            ['quantity' => 10, 'unit_price' => 12.00, 'total' => 120.00]
        );

        echo "\n========================================\n";
        echo "  SEEDER COMPLETE - Test Credentials\n";
        echo "========================================\n";
        echo "SuperAdmin: username=superadmin  password=Admin@123   company=Demo Company\n";
        echo "Admin:      username=testadmin   password=Test@1234   company=Test Company\n";
        echo "Distributor:username=distributor password=Dist@1234   company=Demo Company\n";
        echo "========================================\n";
    }
}
