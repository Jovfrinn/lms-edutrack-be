<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CourseAssignment;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;
        $data = [
            'point'           => QuizResult::where('user_id', $userId)->sum('total_point'),
            'quizCompleted'   => QuizResult::where('user_id', $userId)->count(),
            'courseTotal'     => CourseAssignment::where('employee_id', $userId)->count(),
            'courseCompleted' => CourseAssignment::where('employee_id', $userId)->where('status', 'completed')->count(),
        ];

        return response()->json($data);
    }

    public function weeklyReport()
    {
        $userId = Auth::user()->id;

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $days[] = Carbon::now()->subDays($i)->format('d M');
        }

        $weeklyPoints = QuizResult::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_point) as total')
            )
            ->groupBy('date')
            ->pluck('total', 'date');

        $pointsData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateKey = Carbon::now()->subDays($i)->format('Y-m-d');
            $pointsData[] = (int) $weeklyPoints->get($dateKey, 0);
        }

        $totalWeeklyPoints = array_sum($pointsData);

        return response()->json([
            'data'   => $pointsData,
            'total_weekly_points' => $totalWeeklyPoints,
        ]);
    }
}
