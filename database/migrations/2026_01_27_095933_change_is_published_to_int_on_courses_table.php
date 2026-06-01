<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE courses
            MODIFY is_published TINYINT(1) NOT NULL DEFAULT 1
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE courses
            MODIFY is_published BOOLEAN NOT NULL DEFAULT TRUE
        ");
    }
};