<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('username')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('phone', 50)->nullable();
            $table->enum('role', ['superadmin', 'admin', 'salesman', 'customer', 'warehouse_manager', 'shop'])->default('customer');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 10, 2)->nullable()->default(0)->comment('For shop/customer users');
            $table->string('territory')->nullable()->comment('For salesman users');
            $table->decimal('target_amount', 10, 2)->nullable()->default(0)->comment('For salesman users');
            $table->string('designation')->nullable();
            $table->string('employee_code', 50)->nullable();
            $table->json('permissions')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Migrate existing admins
        DB::statement("INSERT INTO users (id, company_id, name, email, username, password, phone, role, is_active, is_delete, address, created_at, updated_at)
            SELECT id, company_id, full_name, email, username, password, contact_number,
                   IF(role='superAdmin','superadmin','admin'), is_active, is_delete, address, created_at, updated_at
            FROM admins");

        // Migrate existing staff
        DB::statement("INSERT INTO users (company_id, name, email, username, password, phone, role, is_active, designation, employee_code, created_at, updated_at)
            SELECT company_id, name, email, username, password, phone, designation, 1, designation, employee_code, created_at, updated_at
            FROM staff WHERE id NOT IN (SELECT id FROM users)");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
