<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_contents', function (Blueprint $table) {
            $table->boolean('show_answer')
                ->default(false)
                ->after('order_index'); 
        });
    }

    public function down(): void
    {
        Schema::table('course_contents', function (Blueprint $table) {
            $table->dropColumn('show_answer');
        });
    }
};
