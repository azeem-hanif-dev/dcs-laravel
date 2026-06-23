<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add 'distributor' to users.role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','salesman','customer','warehouse_manager','shop','distributor') NOT NULL DEFAULT 'customer'");

        // 2. Add module_permissions JSON to distributors table
        Schema::table('distributors', function (Blueprint $table) {
            $table->json('module_permissions')->nullable()->after('is_active')
                ->comment('{"sales":true,"inventory":false,"procurement":true,"customers":true,"reports":false}');
        });

        // 3. Add distributor_id FK to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'distributor_id')) {
                $table->foreignId('distributor_id')->nullable()->after('company_id')
                    ->constrained('distributors')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'distributor_id')) {
                $table->dropForeign(['distributor_id']);
                $table->dropColumn('distributor_id');
            }
        });

        Schema::table('distributors', function (Blueprint $table) {
            if (Schema::hasColumn('distributors', 'module_permissions')) {
                $table->dropColumn('module_permissions');
            }
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','salesman','customer','warehouse_manager','shop') NOT NULL DEFAULT 'customer'");
    }
};
