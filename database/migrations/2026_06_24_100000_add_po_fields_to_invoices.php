<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Make sales_order_id nullable (PO invoices won't have a sales order)
            $table->foreignId('sales_order_id')->nullable()->change();

            // Make shop_id nullable (PO invoices won't have a shop)
            $table->foreignId('shop_id')->nullable()->change();

            // Add purchase_order_id for supplier/procurement invoices
            $table->foreignId('purchase_order_id')->nullable()->after('shop_id')
                ->constrained('purchase_orders')->nullOnDelete();

            // Add supplier_id for supplier/procurement invoices
            $table->foreignId('supplier_id')->nullable()->after('purchase_order_id')
                ->constrained('suppliers')->nullOnDelete();

            // Add invoice_type to distinguish sales vs procurement invoices
            $table->string('invoice_type')->default('sales')->after('supplier_id')
                ->comment('sales or procurement');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['purchase_order_id', 'supplier_id', 'invoice_type']);

            // Revert nullable changes
            $table->foreignId('sales_order_id')->nullable(false)->change();
            $table->foreignId('shop_id')->nullable(false)->change();
        });
    }
};
