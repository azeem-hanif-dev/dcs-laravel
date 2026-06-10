<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('staff')->cascadeOnDelete();
            $table->integer('total_workers')->default(1);
            $table->decimal('rate', 10, 2)->default(0);
            $table->integer('hours')->default(0);
            $table->integer('days')->default(0);
            $table->integer('extra_hours')->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('net_rate', 10, 2)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_details');
    }
};
