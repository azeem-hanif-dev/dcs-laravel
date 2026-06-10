<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('full_name', 28);
            $table->string('email');
            $table->string('username');
            $table->string('password');
            $table->string('contact_number');
            $table->enum('gender', ['Male', 'Female', 'Others'])->default('Male');
            $table->string('role')->default('Member');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->timestamps();
            $table->unique(['username', 'company_id']);
            $table->index(['company_id', 'is_active', 'is_delete']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
