<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\QuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportEmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with([
            'user:id,email',
            'department:id,name',
            'contentProgress',
            'courseAssignments.course.courseContents.contentType',
            'courseAssignments.course.courseContents.contentProgress',
            'courseAssignments.course.courseContents.questions.questionAnswers',
        ])->get();

        // dd($this->buildSummary($employees));

        return response()->json([
            'summary' => $this->buildSummary($employees),
            'departments' => Department::pluck('name'),
            'employees' => $employees->map(fn($e) => $this->mapEmployee($e)),
        ]);
    }


    private function mapEmployee(Employee $employee)
    {
        $courses = $employee->courseAssignments->map(function ($assignment) use ($employee) {

            $course = $assignment->course;

            $contents = $course->courseContents->map(function ($content) use ($employee) {

                $progress = $content->contentProgress
                    ->firstWhere('employee_id', $employee->id);

                $completed = $progress?->status === 'completed';

                $score = $content->questions
                    ->flatMap(
                        fn($q) =>
                        $q->questionAnswers->where('employee_id', $employee->id)
                    )
                    ->sum('points_awarded');

                return [
                    'type' => $content->contentType->code,
                    'title' => $content->title,
                    'completed' => $completed,
                    'score' => $content->contentType->code === 'quiz-pg'
                        ? round($score ?? 0)
                        : null,
                ];
            });

            $total = $contents->count();
            $done = $contents->where('completed', true)->count();

            $status = match (true) {
                $done === 0 => 'not-started',
                $done === $total => 'completed',
                default => 'in-progress',
            };

            return [
                'title' => $course->title,
                'status' => $status,
                'contents' => $contents,
            ];
        });

        $completedCourses = $courses->where('status', 'completed')->count();

        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'employee_number' => $employee->employee_number,
            'department' => optional($employee->department)->name,
            'email' => $employee->user->email,
            'total_courses' => $courses->count(),
            'completed_courses' => $completedCourses,
            'pending_courses' => $courses->count() - $completedCourses,
            'average_score' => $this->calculateAverageScore($employee),
            'completion_rate' => round(($completedCourses / max($courses->count(), 1)) * 100),
            'courses' => $courses,
        ];
    }


    private function buildSummary($employees)
{
    $ca = CourseAssignment::count();
    $cac = CourseAssignment::where('status', 'completed')->count();

    return [
        'total_employees' => $employees->count(),
        'total_courses' => $ca,
        'average_completion' => $ca > 0
            ? round(($cac / $ca) * 100)
            : 0,
        'total_completed_courses' => $cac,
    ];
}

    private function calculateAverageScore(Employee $employee): int
    {
        $quizAnswers = QuestionAnswer::query()
            ->where('employee_id', $employee->id)
            ->with('question.courseContent.contentType')
            ->get()
            ->filter(
                fn($a) =>
                $a->question &&
                    $a->question->courseContent &&
                    $a->question->courseContent->contentType?->code === 'quiz-pg'
            );

        $quizTotals = $quizAnswers
            ->groupBy(fn($a) => $a->question->course_content_id)
            ->map(fn($answers) => $answers->sum('points_awarded'));

        $quizCount = $quizTotals->count();

        if ($quizCount === 0) {
            return 0;
        }

        return (int) round($quizTotals->sum() / $quizCount);
    }



    public function indexEmployee()
    {

        $employee = Auth::user()->employee;

        $employee->load([
            'department',
            'contentProgress',
            'questionAnswers',
            'courseAssignments.course.courseContents.contentType',
            'courseAssignments.course.courseContents.questions.questionAnswers',
        ]);

        $courses = $employee->courseAssignments->map(function ($assignment) use ($employee) {

            $course = $assignment->course;

            $contents = $course->courseContents->map(function ($content) use ($employee) {

                $progress = $content->contentProgress
                    ->firstWhere('employee_id', $employee->id);

                $completed = $progress?->status === 'completed';

                $score = $content->questions
                    ->flatMap(
                        fn($q) =>
                        $q->questionAnswers->where('employee_id', $employee->id)
                    )
                    ->sum('points_awarded');

                return [
                    'type' => $content->contentType->code,
                    'title' => $content->title,
                    'completed' => $completed,
                    'score' => $content->contentType->code === 'quiz-pg'
                        ? round($score)
                        : null,
                ];
            });

            $total = $contents->count();
            $done = $contents->where('completed', true)->count();

            $status = match (true) {
                $done === 0 => 'not-started',
                $done === $total => 'completed',
                default => 'in-progress',
            };

            $averageScore = $contents
                ->pluck('score')
                ->filter(fn($v) => $v !== null)
                ->whenEmpty(fn() => collect([0]))
                ->avg();

            return [
                'id' => $course->id,
                'title' => $course->title,
                'level' => $course->level,
                'instructor' => $course->creator->employee->name ?? '-',
                'status' => $status,
                'progress' => $total > 0 ? round(($done / $total) * 100) : 0,
                'average_score' => round($averageScore),
                'contents' => $contents,
            ];
        });

        $completedCourses = $courses->where('status', 'completed')->count();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'employee_number' => $employee->employee_number,
                'department' => $employee->department?->name,
                'email' => $employee->user->email,
                'total_courses' => $courses->count(),
                'completed_courses' => $completedCourses,
                'in_progress_courses' => $courses->where('status', 'in-progress')->count(),
                'not_started_courses' => $courses->where('status', 'not-started')->count(),
                'average_score' => $this->calculateAverageScore($employee),
                'completion_rate' => round(($completedCourses / max($courses->count(), 1)) * 100),
            ],
            'courses' => $courses->values(),
        ]);
    }
}
