<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('total_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
        });

        // MySQL 8+ computed column via raw SQL
        DB::statement('ALTER TABLE stocks ADD available_quantity INT GENERATED ALWAYS AS (total_quantity - reserved_quantity) STORED');
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
