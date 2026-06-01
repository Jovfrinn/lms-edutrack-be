<?php

namespace App\Http\Controllers;

use App\Models\Assignments;
use App\Models\ContentAssignment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->employee->id;
        $search = $request->input('query') ? $request->input('query') : '';

        $assignment = Assignments::with(['contentAssignments' => function ($query) use ($user, $user_id) {
            if ($user->role !== 'admin') {
                $query->where('employee_id', $user_id);
            }
        }, 'creator.employee'])
            ->where('is_published', false)
            ->whereHas('contentAssignments', function ($query) use ($user, $user_id) {
                if ($user->role !== 'admin') {
                    $query->where('employee_id', $user_id);
                }
            })
            ->where('title', 'LIKE', '%'.$search.'%')
            ->get();

        return response()->json([
            'message' => 'Successfully',
            'data' => $assignment,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $slug = null)
    {

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
        ];

        try {
            $validatedData = $request->validate($rules);
            $assignData = null;

            DB::transaction(function () use ($validatedData, $slug, &$assignData) {

                if ($slug) {
                    $assignment = Assignments::where('slug', $slug)->first();
                    $message = 'Assignment updated successfully';
                } else {
                    $assignment = new Assignments;
                    $assignment->created_by = Auth::id();
                    $message = 'Assignment created successfully';
                }

                $slug = Str::slug($validatedData['title']);
                $originalSlug = $slug;
                $counter = 1;

                while (Assignments::where('slug', $slug)->exists()) {
                    $slug = $originalSlug.'-'.$counter++;
                }

                $assignment->title = $validatedData['title'];
                $assignment->slug = $slug;
                $assignment->type = $validatedData['type'];
                $assignment->description = $validatedData['description'] ?? null;
                $assignment->is_published = false;

                $assignment->save();

                $assignData = [
                    'course' => $assignment,
                    'message' => $message,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => $assignData['message'],
                'data' => $assignData['course'],
            ], $slug ? 200 : 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $th) {

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. '.$th->getMessage(),
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
                'id' => 'd'.$department->id,
                'type' => 'department',
                'name' => $department->name,
                'count' => $department->employees_count,
            ];
        });

        $allTargets = $employees->merge($departments);

        return response()->json([
            'targets' => $allTargets,
        ]);
    }

    public function assignAssignments(Request $request, $slug)
    {
        $validated = $request->validate([
            'assignment_mode' => 'required|string|in:all,groups,individuals',
            'targets_department' => 'nullable|array',
            'targets_department.*' => 'string',
            'targets_employee' => 'nullable|array',
            // 'targets_employee.*' => 'string',
        ]);

        $assignment = Assignments::where('slug', $slug)->firstOrFail();
        $assignerId = Auth::user()->id;
        $employeeIdsToAssign = collect();

        if ($validated['assignment_mode'] === 'all') {

            $employeeIdsToAssign = Employee::pluck('id');
        } elseif ($validated['assignment_mode'] === 'groups') {

            $departmentIds = collect($validated['targets_department'])
                ->map(fn ($id) => (int) Str::after($id, 'd'))
                ->filter(fn ($id) => $id > 0);

            if ($departmentIds->isNotEmpty()) {
                $employeeIdsToAssign = Employee::whereIn('department_id', $departmentIds)->pluck('id');
            }
        } elseif ($validated['assignment_mode'] === 'individuals') {

            $employeeIdsToAssign = collect($validated['targets_employee'])
                ->map(fn ($id) => (int) Str::after($id, 'e'))
                ->filter(fn ($id) => $id > 0);
        }

        DB::beginTransaction();
        try {

            $existingEmployeeIds = ContentAssignment::where('assignment_id', $assignment->id)
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
                    'Penugasan Baru',
                    'Anda mendapatkan penugasan pembelajaran baru. Silakan cek daftar penugasan Anda.'
                );
            }

            ContentAssignment::where('assignment_id', $assignment->id)->delete();

            $now = now();
            $assignmentsData = $employeeIdsToAssign->map(function ($employeeId) use ($assignment, $assignerId, $now) {
                return [
                    'assignment_id' => $assignment->id,
                    'employee_id' => $employeeId,
                    'assigned_by' => $assignerId,
                    // 'assigned_at' => $now,
                    // 'due_date' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

            if ($assignmentsData->isNotEmpty()) {
                ContentAssignment::insert($assignmentsData->all());
            }

            DB::commit();

            return response()->json([
                'message' => 'Penugasan berhasil diperbarui.',
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

    public function showEdit(Request $request, string $slug)
    {
        try {
            $assignments = Assignments::where('slug', $slug)->first();

            $contentAssignment = ContentAssignment::with(['employee.department', 'assigner'])->where('assignment_id', $assignments->id)->get();
            // dd($contentAssignment);

            //

            $data['assignments'] = $assignments;
            $data['contentAssignment'] = $contentAssignment;

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'cccccccc'], 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $slug)
    {
        try {
            $employeeId = Employee::where('user_id', $request->user()->id)->first()->id;
            $assignment = Assignments::where('slug', $slug)
                ->with([
                    'creator:id,username',
                    'contentAssignments' => function ($query) use ($employeeId) {
                        $query->where('employee_id', $employeeId);
                    },
                ])
                ->firstOrFail();

            return response()->json($assignment);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Kursus tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan server'], 500);
        }
    }

    public function showReport(Request $request, string $slug)
    {
        try {
            $assignments = Assignments::where('slug', $slug)->firstOrFail();
            $contentAssignments = $assignments->contentAssignments()->with('employee')->get();

            $totalParcitipants = $assignments->contentAssignments()->count();

            $parcitipantsCompleted = $assignments->contentAssignments()->whereNotNull('completed_at')->count();
            if ($totalParcitipants > 0) {
                $percentage = ($parcitipantsCompleted / $totalParcitipants) * 100;
            } else {
                $percentage = 0;
            }

            return response()->json([
                'assignment' => [
                    'id' => $assignments->id,
                    'title' => $assignments->title,
                    'slug' => $assignments->slug,
                ],
                'contentAssignments' => $contentAssignments,
                'totalParcitipants' => $totalParcitipants,
                'parcitipantsCompleted' => $parcitipantsCompleted,
                'percentage' => $percentage,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data laporan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function uploadAssign(Request $request, $slug)
    {
        $employeeId = Employee::where('user_id', $request->user()->id)->first()->id;

        $assignment = Assignments::where('slug', $slug)->first()->id;

        $contentAssignment = ContentAssignment::where('assignment_id', $assignment)
            ->where('employee_id', $employeeId)
            ->first();

        if (! $contentAssignment) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $file = $request->file('file');
        $path = '';
        if ($file != null) {
            $filename = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('assignments', $filename, 'public');
        }

        $contentAssignment->update([
            'path' => $path ? $path : null,
            'link' => $request->link == 'null' ? null : 'https://'.$request->link,
            'teks' => $request->teks ? $request->teks : null,
            'completed_at' => now(),
            'approve' => 0,
        ]);

        return response()->json([
            'message' => 'File berhasil diupload dan data diperbarui!',
            'data' => $contentAssignment,
        ]);
    }

    public function approve(Request $request, $slug)
    {
        $assignment = Assignments::where('slug', $slug)->first()->id;

        $contentAssignment = ContentAssignment::where('assignment_id', $assignment)
            ->where('employee_id', $request->employeeId)
            ->first();

        if (! $contentAssignment) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $contentAssignment->update([
            'approve' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil di Approve!',
        ]);
    }

    public function reject(Request $request, $slug)
    {
        $assignment = Assignments::where('slug', $slug)->first();
        $employee = Employee::where('id', $request->employeeId)->first();
        $user_id = User::where('id', $employee->user_id)->first()->id;

        $contentAssignment = ContentAssignment::where('assignment_id', $assignment->id)
            ->where('employee_id', $request->employeeId)
            ->first();

        if (! $contentAssignment) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $contentAssignment->update([
            'approve' => 2,
        ]);

        sendNotification($user_id, $request->employeeId, 'Reject, '.$assignment->title, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil di Reject!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
