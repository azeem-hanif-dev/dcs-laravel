<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_plan_id')->constrained('work_plans')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->integer('week_number');
            $table->enum('day_of_week', ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']);
            $table->timestamp('check_in_time');
            $table->timestamp('check_out_time')->nullable();
            $table->boolean('incomplete_check_out')->default(false);
            $table->enum('status', ['checked-in', 'checked-out'])->default('checked-in');
            $table->text('notes')->nullable();
            $table->json('location')->nullable();
            $table->timestamps();
            $table->unique(['work_plan_id', 'worker_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_checks');
    }
};
