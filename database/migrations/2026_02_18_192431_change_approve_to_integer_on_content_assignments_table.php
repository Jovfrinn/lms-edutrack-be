<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE content_assignments
            MODIFY approve INT NOT NULL DEFAULT 0
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE content_assignments
            MODIFY approve BOOLEAN NOT NULL DEFAULT FALSE
        ");
    }
};