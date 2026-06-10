<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('potential_customers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->enum('source', ['subscribe', 'enquire', 'get-in-touch'])->default('subscribe');
            $table->integer('visit_count')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('potential_customers');
    }
};
