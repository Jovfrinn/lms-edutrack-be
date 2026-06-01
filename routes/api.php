<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\ReportEmployeeController;
use App\Http\Controllers\AssignmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route di sini akan otomatis memiliki prefix "/api"
| dan middleware "api" sesuai dengan konfigurasi RouteServiceProvider.
|
*/

Route::get('/ping', fn() => response()->json(['message' => 'API aktif 🚀']));

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'checkToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/getNotification', [AuthController::class, 'getNotification']);
    Route::get('/getCount', [AuthController::class, 'getCount']);
    Route::post('/is-read/{id}', [AuthController::class, 'isRead']);
});

Route::prefix('profile')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::post('/update', [ProfileController::class, 'update']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);
});
Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/weekly', [DashboardController::class, 'weeklyReport']);
    Route::get('/report', [ReportEmployeeController::class, 'index']);
    Route::get('/report-employee', [ReportEmployeeController::class, 'indexEmployee']);
});

Route::prefix('courses')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::post('/change-status/{slug}', [CourseController::class, 'changeStatus']);
    Route::get('/{slug}/report', [CourseController::class, 'showReport']);
    Route::get('/edit/{slug}', [CourseController::class, 'showEdit']);
    Route::get('/show/{slug}', [CourseController::class, 'showPublic']);
    Route::post('/store/{slug?}', [CourseController::class, 'store']);
    Route::post('/submit-video', [CourseController::class, 'submitVideo']);
    Route::post('/submit-quiz', [CourseController::class, 'submitQuiz']);
    Route::post('/store-lesson/{id?}', [CourseController::class, 'storeLesson']);
    Route::post('/assign-course/{id?}', [CourseController::class, 'assignCourse']);
    Route::get('/assignment-targets', [CourseController::class, 'getTargets']);
    Route::get('/complete/{contentId}', [CourseController::class, 'complete']);
    Route::get('/check-allow/{contentId}', [CourseController::class, 'checkAllow']);
    Route::get('/submit-answer/{choice_id}/{question_id}', [CourseController::class, 'handleAnswer']);
});

Route::prefix('assignment')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [AssignmentController::class, 'index']);
    Route::get('/edit/{slug}', [AssignmentController::class, 'showEdit']);
    Route::get('/show/{slug}', [AssignmentController::class, 'show']);
    Route::get('/show/list/{slug}', [AssignmentController::class, 'showReport']);
    Route::post('/store/{slug?}', [AssignmentController::class, 'store']);
    Route::post('/assign/{id?}', [AssignmentController::class, 'assignAssignments']);
    Route::get('/assignment-targets', [AssignmentController::class, 'getTargets']);
    Route::post('/upload-assign/{slug}', [AssignmentController::class, 'uploadAssign']);
    Route::post('/approve/{slug}', [AssignmentController::class, 'approve']);
    Route::post('/reject/{slug}', [AssignmentController::class, 'reject']);
});

Route::prefix('department')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [DepartmentController::class, 'index']);
    Route::post('/', [DepartmentController::class, 'store']);
    Route::put('/{id}', [DepartmentController::class, 'update']);
    Route::delete('/{id}', [DepartmentController::class, 'destroy']);
});

Route::prefix('employee')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/', [EmployeeController::class, 'store']);
    Route::put('/{id}', [EmployeeController::class, 'update']);
    Route::delete('/{id}', [EmployeeController::class, 'destroy']);
    Route::get('/get-department/{id?}', [EmployeeController::class, 'getDepartment']);
});

Route::prefix('quiz')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [QuizController::class, 'index']);
    Route::get('/detail/{id}', [QuizController::class, 'show']);
    Route::post('/create', [QuizController::class, 'store']);
    Route::put('/update/{id}', [QuizController::class, 'update']);
    Route::delete('/delete/{id}', [QuizController::class, 'destroy']);
    Route::get('/show/detail/{id}', [QuizController::class, 'showDetail']);
    Route::post('/submit', [QuizController::class, 'storeResult']);
    Route::get('/leaderboard/{id}', [QuizController::class, 'leaderboard']);
    Route::get('/leaderboard-all', [QuizController::class, 'leaderboardAll']);
});
