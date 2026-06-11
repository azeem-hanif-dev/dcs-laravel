<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->string('tax_number')->nullable()->after('contact_number');
            $table->string('payment_terms')->nullable()->after('tax_number');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'tax_number', 'payment_terms']);
        });
    }
};
