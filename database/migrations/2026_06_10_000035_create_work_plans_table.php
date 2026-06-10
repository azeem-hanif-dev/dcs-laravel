<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('project_jobs')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('staff')->cascadeOnDelete();
            $table->enum('job_type', ['daily', 'weekly', 'onetime', 'extra']);
            $table->json('days')->nullable();
            $table->json('weeks')->nullable();
            $table->date('date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
            $table->unique(['project_id', 'job_id', 'worker_id', 'job_type'], 'work_plan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plans');
    }
};
