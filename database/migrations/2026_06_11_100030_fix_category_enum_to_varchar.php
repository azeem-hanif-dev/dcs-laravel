<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE categories MODIFY COLUMN name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE subcategories MODIFY COLUMN name VARCHAR(255) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE categories MODIFY COLUMN name ENUM('Chemical','Hard Material','Textile','Paper','Machine') NOT NULL");
    }
};
