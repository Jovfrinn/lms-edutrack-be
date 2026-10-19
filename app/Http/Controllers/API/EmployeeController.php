<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search  = $request->search ?? '';
        $perPage = $request->per_page ?? 10;

        $employee = Employee::with(['user', 'department'])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->when($request->department_id, function ($query) use ($request) {
                $query->where('department_id', $request->department_id);
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $employee
        ]);
    }
    public function getDepartment(Request $request, $id = null)
    {
        if ($id == null) {
            $per_page = 10;
            $search = $request->search ? $request->search : '';

            $department = Department::when($search, fn($q, $search) => $q->where('name', 'LIKE', "%{$search}%"))
                ->orderBy('name', 'asc')
                ->paginate($per_page);

            return response()->json([
                'success' => true,
                'data' => $department
            ]);
        } else {
            $item = Department::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        }
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
    public function store(Request $request)
    {
        try {

            $department = Department::where('id', $request->department_id)->value('name');

            $departmentAbbr = strtoupper(substr(trim($department), 0, 3));

            do {
                $randomNumber = random_int(100, 999);
                $code = $departmentAbbr . '-' . $randomNumber;
            } while (
                Employee::where('employee_number', $code)->exists()
            );


            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role
            ]);

            Employee::create([
                'user_id' => $user->id,
                'department_id' => $request->department_id,
                'employee_number' => $code,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->addres,
                'position' => $request->position,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        try {
            $employee = Employee::findOrFail($id);

            $employee->update([
                'department_id' => $request->department_id,
                'name'          => $request->name,
                'phone'         => $request->phone,
                'position'      => $request->position,
                'address'       => $request->address,
            ]);

            $userPayload = [
                'username' => $request->username,
                'email'    => $request->email,
                'role'     => $request->role,
            ];

            if ($request->filled('password')) {
                $userPayload['password'] = bcrypt($request->password);
            }

            $employee->user->update($userPayload);

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Employee',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                ], 404);
            }
            $employee->delete();
            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
