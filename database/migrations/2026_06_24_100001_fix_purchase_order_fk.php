<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix purchase_orders FK: ordered_by should reference users not admins
        $tables = [
            'purchase_orders' => 'ordered_by',
        ];

        foreach ($tables as $table => $column) {
            $fkName = 'material_orders_ordered_by_foreign';
            try {
                Schema::table($table, function (Blueprint $t) use ($fkName) {
                    $t->dropForeign($fkName);
                });
            } catch (\Exception $e) {
                // FK might already have been dropped or renamed
            }
            try {
                Schema::table($table, function (Blueprint $t) use ($column) {
                    $t->foreign($column)->references('id')->on('users')->cascadeOnDelete();
                });
            } catch (\Exception $e) {
                // FK might already exist
            }
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
