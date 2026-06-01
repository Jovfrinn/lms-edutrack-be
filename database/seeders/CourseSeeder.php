<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Services\SeederDataBank; // Panggil Bank Data

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $allCourses = SeederDataBank::getCourses();

        foreach ($allCourses as $courseData) {
            Course::factory()
                ->withSpecificData($courseData) 
                ->create();
        }


    }
}