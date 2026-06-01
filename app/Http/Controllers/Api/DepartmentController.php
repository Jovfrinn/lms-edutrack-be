<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? "";
        $per_page = $request->per_page ?? 10;
        $department = Department::orderBy('created_at', 'desc')->where('name', 'LIKE','%'. $search .'%')->paginate($per_page);


        return response()->json([
           "success" => true,
            "data"=> $department
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
    public function store(Request $request)
    {
         try {
            Department::create([
                'name' => $request->name,
                'description'=> $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Department',
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
            $department = Department::findOrFail($id);
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department not found',
                ], 404);
            }
            $department->update([
                'name' => $request->name,
                'description'=> $request->description,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         try {
            $department = Department::findOrFail($id);
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department not found',
                ], 404);
            }
            $department->delete();
            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Department',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
