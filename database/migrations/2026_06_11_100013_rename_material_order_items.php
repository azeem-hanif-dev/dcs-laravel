<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('material_order_items', 'purchase_order_items');

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->renameColumn('material_order_id', 'purchase_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->renameColumn('purchase_order_id', 'material_order_id');
        });

        Schema::rename('purchase_order_items', 'material_order_items');
    }
};
