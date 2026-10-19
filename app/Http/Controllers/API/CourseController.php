<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseListResource;
use App\Models\Choice;
use App\Models\ContentProgress;
use App\Models\ContentType;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\CourseContent;
use App\Models\CourseModule;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Question;
use App\Models\QuestionAnswer;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee->id ?? null;

        $quizPgCountSubquery = Question::query()
            ->join('course_contents', 'questions.course_content_id', '=', 'course_contents.id')
            ->join('content_types', 'course_contents.content_type_id', '=', 'content_types.id')
            ->where('content_types.code', 'quiz-pg')
            ->whereColumn('course_contents.course_id', 'courses.id')
            ->selectRaw('count(questions.id)');

        $query = Course::query()
            ->with('creator:id,username')
            ->with('creator.employee')
            ->with('courseContents')
            ->withCount([
                'courseContents as lesson_count',
                'courseContents as video_count' => fn($q) => $q->whereHas('contentType', fn($ct) => $ct->where('code', 'video')),
                'courseContents as audio_count' => fn($q) => $q->whereHas('contentType', fn($ct) => $ct->where('code', 'audio')),
                'courseContents as assigment_count' => fn($q) => $q->whereHas('contentType', fn($ct) => $ct->where('code', 'assigment')),
                'courseContents as ebook_count' => fn($q) => $q->whereHas('contentType', fn($ct) => $ct->where('code', 'ebook')),
            ])
            ->selectSub($quizPgCountSubquery, 'quiz_pg_count');

        if ($request->status == 'archived') {
            $query->where('is_published', 2);
        } else {
            $query->whereNotIn('is_published', [2, 3]);
        }

        if ($user->role === 'admin') {
            $query->where('created_by', $user->id);
        } else {
            if (!$employeeId) {
                return response()->json(['message' => 'Employee data not found for this user.'], 404);
            }
            $query->where('is_published', 1);
            $query->whereHas('courseAssignments', fn($q) => $q->where('employee_id', $employeeId));



            $query->with(['courseAssignments' => fn($q) => $q->where('employee_id', $employeeId)]);
        }

        if ($request->filled('query')) {
            $query->where('title', 'LIKE', '%' . $request->query('query') . '%');
        }


        if ($request->query('sort') === 'most-lessons') {
            $query->orderByDesc('lesson_count');
        } else {
            $query->latest('updated_at');
        }

        $courses = $query->paginate($request->get('per_page', 8));

        return CourseListResource::collection($courses);
    }

    public function store(Request $request, $slug = null)
    {

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'program_type' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
        ];

        try {
            $validatedData = $request->validate($rules);
            $courseData = null;

            // return response()->json([
            //     'success' => false,
            //     'message' =>
            // ], 500);

            DB::transaction(function () use ($request, $validatedData, $slug, &$courseData) {

                if ($slug) {
                    $course = Course::where('slug', $slug)->first();
                    $message = 'Course updated successfully';
                } else {
                    $course = new Course();
                    $course->created_by = Auth::id();
                    $message = 'Course created successfully';
                    $slug = Str::slug($validatedData['title']);
                    $originalSlug = $slug;
                    $counter = 1;

                    while (Course::where('slug', $slug)->exists()) {
                        $slug = $originalSlug . '-' . $counter++;
                    }
                    $course->slug = $slug;
                }

                $thumbnailPath = $course->thumbnail;




                if ($request->hasFile('thumbnail')) {
                    if ($course->thumbnail) {
                        Storage::disk('public')->delete($course->thumbnail);
                    }

                    $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
                }

                $course->title = $validatedData['title'];

                $course->description = $validatedData['description'] ?? null;
                $course->thumbnail = $thumbnailPath;
                $course->program_type = $validatedData['program_type'] ?? null;
                $course->level = $validatedData['level'] ?? null;
                $course->is_published = false;


                $course->save();

                $courseData = [
                    'course' => $course,
                    'message' => $message
                ];
            });

            return response()->json([
                'success' => true,
                'message' => $courseData['message'],
                'data' => $courseData['course']
            ], $slug ? 200 : 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $th) {

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. ' . $th->getMessage()
            ], 500);
        }
    }



    // public function storeLesson(Request $request, $slug)
    // {
    //     $request->validate([
    //         'content_list_json' => 'required|json',
    //     ]);

    //     $newFilePaths = [];
    //     $oldFilePaths = [];

    //     try {
    //         DB::beginTransaction();

    //         $course = Course::where('slug', $slug)->firstOrFail();

    //         $contentList = json_decode($request->input('content_list_json'), true);
    //         if (json_last_error() !== JSON_ERROR_NONE) {
    //             throw new \Exception('Invalid JSON data provided.');
    //         }

    //         $contentTypesMap = ContentType::pluck('id', 'name')->all();

    //         $oldContents = $course->courseContents()->with('questions.choices')->get()->keyBy('id');
    //         foreach ($oldContents as $content) {
    //             if ($content->file_path) {
    //                 $oldFilePaths[] = $content->file_path;
    //             }
    //             foreach ($content->questions as $question) {
    //                 if ($question->media_path) {
    //                     $oldFilePaths[] = $question->media_path;
    //                 }
    //                 foreach ($question->choices as $choice) {
    //                     if ($choice->media_path) {
    //                         $oldFilePaths[] = $choice->media_path;
    //                     }
    //                 }
    //             }
    //         }

    //         $oldFilePaths = [];

    //         $oldContents = $course->courseContents()->get();

    //         foreach ($oldContents as $content) {
    //             $oldFilePaths[$content->id] = $content->file_path;
    //         }
    //         $course->courseContents()->delete();

    //         foreach ($contentList as $index => $item) {


    //             $contentTypeName = $item['type'];
    //             $contentTypeId = $contentTypesMap[$contentTypeName] ?? null;

    //             if (!$contentTypeId) {
    //                 throw new \Exception("Content type '{$contentTypeName}' not found.");
    //             }

    //             $content = $course->courseContents()->create([
    //                 'content_type_id' => $contentTypeId,
    //                 'title' => $item['title'],
    //                 'description' => $item['description'] ?? null,
    //                 'order_index' => $index + 1,
    //             ]);


    //             if ($contentTypeName === 'Quiz PG') {
    //                 if (!is_array($item['value'])) {
    //                     throw new \Exception("Quiz content value must be an array of questions.");
    //                 }

    //                 foreach ($item['value'] as $qIndex => $q) {
    //                     $qData = [
    //                         'question_text' => $q['questionText'],
    //                         'points' => $q['points'] ?? 0,
    //                         'media_path' => null,
    //                     ];

    //                     $qImageKey = $q['questionImage']['file_key'] ?? null;
    //                     $qVideoKey = $q['questionVideo']['file_key'] ?? null;

    //                     if ($qImageKey && $request->hasFile($qImageKey)) {
    //                         $path = $request->file($qImageKey)->store('course_content_files', 'public');
    //                         $qData['media_path'] = $path;
    //                         $newFilePaths[] = $path;
    //                     } elseif ($qVideoKey && $request->hasFile($qVideoKey)) {
    //                         $path = $request->file($qVideoKey)->store('course_content_files', 'public');
    //                         $qData['media_path'] = $path;
    //                         $newFilePaths[] = $path;
    //                     }

    //                     $newQuestion = $content->questions()->create($qData);

    //                     foreach ($q['choices'] as $cIndex => $c) {
    //                         $cData = [
    //                             'choice_text' => $c['text'],
    //                             'is_correct' => $c['correct'],
    //                             'media_path' => null,
    //                         ];

    //                         $cImageKey = $c['img']['file_key'] ?? null;
    //                         if ($cImageKey && $request->hasFile($cImageKey)) {
    //                             $path = $request->file($cImageKey)->store('course_content_files', 'public');
    //                             $cData['media_path'] = $path;
    //                             $newFilePaths[] = $path;
    //                         }

    //                         $newQuestion->choices()->create($cData);
    //                     }
    //                 }
    //             } else {
    //                 $fileKey = $item['value']['file_key'] ?? null;
    //                 $oldId   = $item['id'] ?? null;
    //                 if ($fileKey && $request->hasFile($fileKey)) {
    //                     $path = $request->file($fileKey)->store('course_content_files', 'public');
    //                     $content->file_path = $path;
    //                     $newFilePaths[] = $path;
    //                 } else {
    //                     $content->file_path = $oldFilePaths[$oldId] ?? null;
    //                 }
    //                 $content->save();
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Course content saved successfully.',
    //             'data' => $course
    //         ], 201);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         if (!empty($newFilePaths)) Storage::disk('public')->delete($newFilePaths);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (Exception $th) {
    //         DB::rollBack();

    //         if (!empty($newFilePaths)) {
    //             Storage::disk('public')->delete($newFilePaths);
    //         }

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An unexpected error occurred: ' . $th->getMessage(),
    //             'line' => $th->getLine(),
    //         ], 500);
    //     }
    // }

    public function storeLesson(Request $request, $slug)
    {
        $request->validate([
            'content_list_json' => 'required|json',
        ]);

        $newFilePaths = [];
        $oldFilePaths = [];

        try {
            DB::beginTransaction();

            $course = Course::where('slug', $slug)->firstOrFail();

            $contentList = json_decode($request->input('content_list_json'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON data provided.');
            }

            $contentTypesMap = ContentType::pluck('id', 'name')->all();

            $oldContents = $course->courseContents()->get();
            foreach ($oldContents as $content) {
                $oldFilePaths[$content->id] = $content->file_path;
            }

            $course->courseContents()->delete();

            foreach ($contentList as $index => $item) {

                $contentTypeName = $item['type'];
                $contentTypeId   = $contentTypesMap[$contentTypeName] ?? null;

                if (!$contentTypeId) {
                    throw new \Exception("Content type '{$contentTypeName}' not found.");
                }

                $content = $course->courseContents()->create([
                    'content_type_id' => $contentTypeId,
                    'title'           => $item['title'],
                    'description'     => $item['description'] ?? null,
                    'order_index'     => $index + 1,
                    'show_answer'     => isset($item['show_answer']) && $item['type'] === 'Quiz PG'
                        ? (bool) $item['show_answer']
                        : false,
                ]);

                if ($contentTypeName === 'Quiz PG') {
                    if (!is_array($item['value'])) {
                        throw new \Exception("Quiz content value must be an array of questions.");
                    }

                    foreach ($item['value'] as $qIndex => $q) {
                        $qData = [
                            'question_text' => $q['questionText'],
                            'points'        => $q['points'] ?? 0,
                            'media_path'    => null,
                        ];

                        $qImageKey = $q['questionImage']['file_key'] ?? null;
                        $qVideoKey = $q['questionVideo']['file_key'] ?? null;

                        if ($qImageKey && $request->hasFile($qImageKey)) {
                            $path = $request->file($qImageKey)->store('course_content_files', 'public');
                            $qData['media_path'] = $path;
                            $newFilePaths[] = $path;
                        } elseif ($qVideoKey && $request->hasFile($qVideoKey)) {
                            $path = $request->file($qVideoKey)->store('course_content_files', 'public');
                            $qData['media_path'] = $path;
                            $newFilePaths[] = $path;
                        }

                        $newQuestion = $content->questions()->create($qData);

                        foreach ($q['choices'] as $cIndex => $c) {
                            $cData = [
                                'choice_text' => $c['text'],
                                'is_correct'  => $c['correct'],
                                'media_path'  => null,
                            ];

                            $cImageKey = $c['img']['file_key'] ?? null;
                            if ($cImageKey && $request->hasFile($cImageKey)) {
                                $path = $request->file($cImageKey)->store('course_content_files', 'public');
                                $cData['media_path'] = $path;
                                $newFilePaths[] = $path;
                            }

                            $newQuestion->choices()->create($cData);
                        }
                    }
                } else {
                    $fileKey = $item['value']['file_key'] ?? null;
                    $oldId   = $item['id'] ?? null;

                    if ($fileKey && $request->hasFile($fileKey)) {
                        $path = $request->file($fileKey)->store('course_content_files', 'public');
                        $content->file_path = $path;
                        $newFilePaths[] = $path;
                    } else {
                        $content->file_path = $oldFilePaths[$oldId] ?? null;
                    }

                    $content->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Course content saved successfully.',
                'data'    => $course,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            if (!empty($newFilePaths)) Storage::disk('public')->delete($newFilePaths);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $th) {
            DB::rollBack();
            if (!empty($newFilePaths)) Storage::disk('public')->delete($newFilePaths);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $th->getMessage(),
                'line'    => $th->getLine(),
            ], 500);
        }
    }



    public function getTargets(Request $request)
    {
        // dd("asdasd");
        $search = $request->query('search');

        $employeesQuery = Employee::query()
            ->with('department:id,name')
            ->select('id', 'name', 'department_id');

        $departmentsQuery = Department::query()
            ->withCount('employees')
            ->select('id', 'name');

        if ($search) {
            $searchTerm = "%{$search}%";

            $employeesQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', $searchTerm)
                    ->orWhereHas('department', function ($deptQuery) use ($searchTerm) {
                        $deptQuery->where('name', 'LIKE', $searchTerm);
                    });
            });

            $departmentsQuery->where('name', 'LIKE', $searchTerm);
        }

        $employees = $employeesQuery->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'type' => 'employee',
                'name' => $employee->name,
                'department' => $employee->department->name ?? null,
            ];
        });

        $departments = $departmentsQuery->get()->map(function ($department) {
            return [
                'id' => 'd' . $department->id,
                'type' => 'department',
                'name' => $department->name,
                'count' => $department->employees->count(),
            ];
        });

        $allTargets = $employees->merge($departments);

        return response()->json([
            'targets' => $allTargets
        ]);
    }


    public function assignCourse(Request $request, $slug)
    {
        $validated = $request->validate([
            'assignment_mode' => 'required|string|in:all,groups,individuals',
            'targets_department' => 'nullable|array',
            'targets_department.*' => 'string',
            'targets_employee' => 'nullable|array',
            // 'targets_employee.*' => 'string',
        ]);

        $course = Course::where('slug', $slug)->firstOrFail();
        $assignerId = $request->user()->id;
        $employeeIdsToAssign = collect();

        if ($validated['assignment_mode'] === 'all') {

            $employeeIdsToAssign = Employee::pluck('id');
        } elseif ($validated['assignment_mode'] === 'groups') {

            $departmentIds = collect($validated['targets_department'])
                ->map(fn($id) => (int) Str::after($id, 'd'))
                ->filter(fn($id) => $id > 0);

            if ($departmentIds->isNotEmpty()) {
                $employeeIdsToAssign = Employee::whereIn('department_id', $departmentIds)->pluck('id');
            }
        } elseif ($validated['assignment_mode'] === 'individuals') {

            $employeeIdsToAssign = collect($validated['targets_employee'])
                ->map(fn($id) => (int) Str::after($id, 'e'))
                ->filter(fn($id) => $id > 0);
        }

        DB::beginTransaction();
        try {
            // $course->update(['assignment_mode' => $validated['assignment_mode']]);

            $existingEmployeeIds = CourseAssignment::where('course_id', $course->id)
                ->pluck('employee_id')
                ->toArray();

            $newEmployeeIds = $employeeIdsToAssign
                ->diff($existingEmployeeIds)
                ->values();


            foreach ($newEmployeeIds as $employeeId) {
                $userId = Employee::where('id', $employeeId)->first()->user_id;
                sendNotification(
                    $userId,
                    $employeeId,
                    'Penugasan Kursus Baru',
                    'Anda telah ditugaskan ke kursus "' . $course->title . '". Silakan mulai pembelajaran Anda.'
                );
            }


            CourseAssignment::where('course_id', $course->id)->delete();

            $now = now();
            $assignmentsData = $employeeIdsToAssign->map(function ($employeeId) use ($course, $assignerId, $now) {
                return [
                    'course_id' => $course->id,
                    'employee_id' => $employeeId,
                    'assigned_by' => $assignerId,
                    'assigned_at' => $now,
                    'status' => 'pending',
                    'due_date' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

            if ($assignmentsData->isNotEmpty()) {
                CourseAssignment::insert($assignmentsData->all());
            }

            $course->is_published = 1;
            $course->save();

            DB::commit();

            return response()->json([
                'message' => 'Penugasan kursus berhasil diperbarui.',
                'assignments_created' => $assignmentsData->count(),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan penugasan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function showPublic(Request $request, string $slug)
    {
        try {
            $employeeId = Auth::user()->employee->id;
            $course = Course::where('slug', $slug)
                ->with([
                    'creator:id,username',
                    'creator.employee',
                    'courseContents' => function ($q) use ($employeeId) {
                        $q->with([
                            'myContentProgress' => function ($q) use ($employeeId) {
                                $q->where('employee_id', $employeeId);
                            },
                            'questions.choices',
                            'questions.questionAnswers',
                            'contentType:id,name,code,icon'
                        ]);
                    }
                ])
                ->firstOrFail();

            return response()->json($course);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Kursus tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }



    public function showReport(Request $request, $slug)
    {
        try {
            $course = Course::where('slug', $slug)->firstOrFail();

            $totalContentsCount = $course->courseContents()->count();

            $assignments = CourseAssignment::where('course_id', $course->id)
                ->with([
                    'employee:id,name,employee_number',
                ])
                ->addSelect([
                    'completed_contents_count' => DB::table('content_progress as cp')
                        ->join('course_contents as cc', 'cp.course_content_id', '=', 'cc.id')
                        ->whereColumn('cp.employee_id', 'course_assignments.employee_id')
                        ->where('cc.course_id', $course->id)
                        ->where('cp.status', 'completed')
                        ->selectRaw('COUNT(DISTINCT cp.course_content_id)')
                ])
                ->orderByDesc('completed_contents_count')
                ->get();

            $assignments->each(function ($assignment) use ($totalContentsCount, $course) {
                $assignment->progress_percent = $totalContentsCount > 0
                    ? round(($assignment->completed_contents_count / $totalContentsCount) * 100)
                    : 0;

                $quizContents = $course->courseContents()
                    ->where('content_type_id', 4)
                    ->with(['questions' => function ($q) use ($assignment) {
                        $q->with(['questionAnswers' => function ($qa) use ($assignment) {
                            $qa->where('employee_id', $assignment->employee_id);
                        }]);
                    }])
                    ->get();

                $assignment->quiz_scores = $quizContents->map(function ($content) {
                    $score = 0;

                    foreach ($content->questions as $question) {
                        $answer = $question->questionAnswers->first();

                        if ($answer && $answer->is_correct) {
                            $score += (int) $question->points;
                        }
                    }

                    return [
                        'content_id' => $content->id,
                        'content_title' => $content->title,
                        'score' => $score,
                        'total_questions' => $content->questions->count(),
                    ];
                });
            });

            return response()->json([
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                ],
                'total_contents_count' => $totalContentsCount,
                'assignments' => $assignments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showEdit(Request $request, string $slug)
    {
        try {
            $course = Course::where('slug', $slug)->first();

            $lesson = CourseContent::with(['contentType', 'questions.choices'])
                ->where('course_id', $course->id)
                ->get();

            $assignment = CourseAssignment::with(['employee.department', 'assigner'])->where('course_id', $course->id)->get();

            $data['course'] = $course;
            $data['lesson'] = $lesson;
            $data['assignment'] = $assignment;

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'cccccccc'], 404);
        }
    }


    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'course_content_id' => ['required', 'exists:course_contents,id'],
            'status' => ['required', Rule::in(['started', 'in_progress', 'completed'])],
            'progress_percent' => 'required|integer|min:0|max:100',
            'last_position_seconds' => 'required|integer|min:0',
        ]);

        $employeeId = Auth::user()->employee->id;

        if (!$employeeId) {
            return response()->json(['message' => 'Unauthorized or Employee ID missing'], 401);
        }

        $progress = ContentProgress::firstOrNew([
            'employee_id' => $employeeId,
            'course_content_id' => $request->course_content_id,
        ]);

        if (!$progress->exists) {
            $progress->started_at = now();
        }

        if ($request->progress_percent >= $progress->progress_percent) {
            $progress->progress_percent = $request->progress_percent;
            $progress->last_position_seconds = $request->last_position_seconds;
            $progress->status = $request->status;

            if ($request->status === 'completed' && !$progress->completed_at) {
            }
        }

        $progress->save();

        return response()->json(['message' => 'Content progress saved successfully', 'progress' => $progress], 200);
    }


    public function complete(Request $request, $contentId)
    {
        $employeeId = Auth::user()->employee->id;

        $cp = ContentProgress::updateOrCreate(
            [
                'course_content_id' => $contentId,
                'employee_id' => $employeeId,
            ],
            [
                'status' => 'completed',
            ]
        );

        $content = CourseContent::findOrFail($contentId);
        $courseId = $content->course_id;

        $totalContents = CourseContent::where('course_id', $courseId)->count();

        $completedContents = ContentProgress::where('employee_id', $employeeId)
            ->where('status', 'completed')
            ->whereIn('course_content_id', function ($query) use ($courseId) {
                $query->select('id')
                    ->from('course_contents')
                    ->where('course_id', $courseId);
            })
            ->count();

        if ($totalContents > 0 && $totalContents === $completedContents) {
            CourseAssignment::where('course_id', $courseId)
                ->where('employee_id', $employeeId)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
        }

        return response()->json([
            'message' => 'Content completed',
            'data' => $cp,
        ]);
    }
    public function checkAllow(Request $request, $contentId)
    {
        $employeeId = Auth::user()->employee->id;

        $currentContent = CourseContent::findOrFail($contentId);

        $previousContent = CourseContent::where('course_id', $currentContent->course_id)
            ->where('id', '<', $currentContent->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$previousContent) {
            return response()->json([
                'message' => 'Check allow content',
                'data' => true,
            ]);
        }

        $previousProgress = ContentProgress::where('course_content_id', $previousContent->id)
            ->where('employee_id', $employeeId)
            ->first();

        return response()->json([
            'message' => 'Check allow content',
            'data' => $previousProgress?->status === 'completed',
        ]);
    }

    public function handleAnswer($cId, $qId)
    {
        $employeeId = Auth::user()->employee->id;
        $choice = Choice::where('id', $cId)->first();
        $question = Question::where('id', $qId)->first();
        $qa = QuestionAnswer::where('employee_id', $employeeId)
            ->where('question_id', $qId)->first();
        if (empty($qa)) {
            $qa = new QuestionAnswer();
        }

        $qa->employee_id = $employeeId;
        $qa->choice_id = $cId;
        $qa->question_id = $qId;
        $qa->is_correct = $choice->is_correct;
        $qa->points_awarded = $choice->is_correct == 1 ? $question->points : 0;
        $qa->save();

        return response()->json([
            'message' => 'Success save answer',
            'data' => $choice->is_correct,
        ]);
    }

    public function changeStatus(Request $request, $slug)
    {
        try {
            $statusMap = [
                'restore'  => 1,
                'archived' => 2,
                'delete'   => 3,
            ];
            $status = $statusMap[$request->status] ?? null;

            $course = Course::where('slug', $slug)->first();
            $course->is_published = $status;
            $course->save();

            return response()->json([
                'success' => true,
                'message' => 'Success update status',
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed update status',
                'data' => null,
            ], 500);
        }
    }
}
