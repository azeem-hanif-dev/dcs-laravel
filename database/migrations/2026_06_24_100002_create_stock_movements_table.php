<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->foreignId('product_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Movement details
            $table->enum('type', [
                'purchase_received',     // PO delivered → stock in
                'sales_shipped',         // Delivery → stock out
                'sales_returned',        // Sales return → stock in
                'purchase_returned',     // Purchase return → stock out
                'manual_addition',       // Manual stock add
                'manual_removal',        // Manual stock remove
                'reserved',              // SO confirmed → reserve
                'released',              // SO cancelled → release reserve
            ]);

            $table->integer('quantity_change'); // positive = in, negative = out
            $table->integer('quantity_before');
            $table->integer('quantity_after');

            // Polymorphic reference to the source record
            $table->string('reference_type')->nullable();  // PurchaseOrder, SalesOrder, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable(); // PO number, SO number, etc.

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
