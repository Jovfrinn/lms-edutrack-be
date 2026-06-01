<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contentTypes = [
            ['name' => 'Video',  'code' => 'video',  'icon' => 'ic:round-videocam'],
            ['name' => 'Audio',  'code' => 'audio',  'icon' => 'ic:round-headphones'],
            ['name' => 'Assigment', 'code' => 'assigment', 'icon' => 'material-symbols:book-5'],
            ['name' => 'Quiz PG', 'code' => 'quiz-pg', 'icon' => 'ic:round-quiz'],
            ['name' => 'E Book', 'code' => 'ebook',  'icon' => 'ic:round-menu-book'],
        ];

        DB::table('content_types')->insert($contentTypes);
    }
}
