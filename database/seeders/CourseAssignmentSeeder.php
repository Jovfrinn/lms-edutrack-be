<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Tambahkan ini
use App\Models\Course;
use App\Models\Employee;
use App\Models\User;
use App\Models\CourseAssignment;
use Carbon\Carbon;

class CourseAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('course_assignments')->delete();

        $admin = User::find(1);

        $courses = Course::all();

        $employees = Employee::where('id', '>', 1)->get(); 

        if (!$admin || $courses->isEmpty() || $employees->isEmpty()) {
            $this->command->error('Gagal assign: Data (Admin/Course/Employee) tidak lengkap.');
            return;
        }

        $totalAssignments = 0;

        foreach ($employees as $employee) {
            
            $numCoursesToAssign = rand(2, 5);
            $randomCourses = $courses->random($numCoursesToAssign);

            foreach ($randomCourses as $course) {
                

                CourseAssignment::create([
                    'course_id' => $course->id,
                    'employee_id' => $employee->id,
                    'assigned_by' => $admin->id,
                    'assigned_at' => Carbon::now()->subDays(rand(1, 60)),
                    'due_date' => null,
                    'status' => 'pending',
                    'completed_at' =>  null,
                ]);

                $totalAssignments++;
            }
        }
        
        $this->command->info('CourseAssignmentSeeder: ' . $totalAssignments . ' penugasan kursus berhasil dibuat.');
    }
}