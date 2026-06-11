<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('sku', 50)->nullable()->after('description');
            $table->string('unit', 20)->default('pcs')->after('sku');
            $table->integer('reorder_level')->default(10)->after('assigned_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['sku', 'unit', 'reorder_level']);
        });
    }
};
