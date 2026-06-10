<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_job_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_job_id')->constrained('project_jobs')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->enum('status', ['pending', 'in-progress', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_job_tasks');
    }
};
