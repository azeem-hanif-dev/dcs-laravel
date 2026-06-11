<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename material_orders → purchase_orders
        Schema::rename('material_orders', 'purchase_orders');

        // Add new columns
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('po_number')->unique()->nullable()->after('id');
            $table->foreignId('supplier_id')->nullable()->after('po_number')->constrained('suppliers')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->after('supplier_id')->constrained('warehouses')->nullOnDelete();
            $table->date('expected_date')->nullable()->after('order_date');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['po_number', 'supplier_id', 'warehouse_id', 'expected_date']);
        });

        Schema::rename('purchase_orders', 'material_orders');
    }
};
