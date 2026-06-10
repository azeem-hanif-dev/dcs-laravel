<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loggers', function (Blueprint $table) {
            $table->id();
            $table->string('method')->nullable();
            $table->string('route')->nullable();
            $table->string('user')->nullable();
            $table->string('username')->nullable();
            $table->string('ip')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->integer('status_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loggers');
    }
};
