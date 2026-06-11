<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sync admins with users (both ways)
        DB::statement("INSERT IGNORE INTO users (id, company_id, name, email, username, password, phone, role, is_active, is_delete, created_at, updated_at)
            SELECT id, company_id, full_name, email, username, password, contact_number, role, is_active, is_delete, created_at, updated_at FROM admins");
        DB::statement("INSERT IGNORE INTO admins (id, company_id, full_name, email, username, password, contact_number, role, is_active, is_delete, created_at, updated_at)
            SELECT id, company_id, name, email, username, COALESCE(password,''), COALESCE(phone,''), role, is_active, is_delete, created_at, updated_at FROM users");

        // Drop old FKs to admins and recreate to users
        $tables = [
            'materials' => 'user_id',
            'suppliers' => 'user_id',
            'categories' => 'user_id',
            'distributors' => 'user_id',
            'customers' => 'user_id',
            'projects' => 'user_id',
            'shops' => 'user_id',
            'salesmen' => 'user_id',
            'work_plans' => 'user_id',
            'sales_orders' => 'user_id',
            'invoices' => 'user_id',
            'payments' => 'user_id',
            'deliveries' => 'user_id',
            'sales_returns' => 'user_id',
            'purchase_returns' => 'user_id',
            'material_orders' => 'ordered_by',
        ];

        foreach ($tables as $table => $column) {
            $fkName = "{$table}_{$column}_foreign";
            try { Schema::table($table, fn($t) => $t->dropForeign($fkName)); } catch (\Exception $e) {}
            try {
                Schema::table($table, fn($t) => $t->foreign($column)->references('id')->on('users')->cascadeOnDelete());
            } catch (\Exception $e) {
                // Skip if column doesn't exist or data doesn't match
            }
        }
    }

    public function down(): void {}
};
