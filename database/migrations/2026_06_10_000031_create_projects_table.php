<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('admins')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('country_code')->nullable();
            $table->string('phone');
            $table->foreignId('supervisor_id')->constrained('staff')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('project_code')->nullable();
            $table->integer('breaktime')->default(0);
            $table->string('location_url');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'project_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
