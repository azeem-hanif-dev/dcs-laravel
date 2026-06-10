<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('username');
            $table->string('email');
            $table->string('employee_code');
            $table->string('phone');
            $table->enum('designation', ['worker', 'supervisor', 'manager', 'admin', 'store manager', 'planner', 'quality controller']);
            $table->foreignId('agency_id')->nullable()->constrained('employment_agencies')->nullOnDelete();
            $table->foreignId('job_type_id')->nullable()->constrained('staff_roles')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->json('permission')->nullable();
            $table->string('gender')->nullable();
            $table->date('visa_expiry')->nullable();
            $table->date('health_expiry')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('password');
            $table->boolean('mobile_signup')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
