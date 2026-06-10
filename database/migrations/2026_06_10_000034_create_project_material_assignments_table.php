<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_material_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('worker_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('category')->nullable();
            $table->integer('assigned_quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);
            $table->integer('daily_consumption')->default(0);
            $table->integer('used_quantity')->default(0);
            $table->integer('life_used')->default(0)->comment('For Textile');
            $table->string('type')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['Active', 'Completed', 'Consumed', 'Expired', 'Returned'])->default('Active');
            $table->integer('returned_quantity')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_material_assignments');
    }
};
