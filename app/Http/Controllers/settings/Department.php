<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use App\Models\Department as DepartmentModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Department extends Controller
{
    public function index()
    {
        $faculties = Faculty::orderBy('faculty_name')->get(['id', 'faculty_name', 'faculty_code']);

        return view('content.settings.department', compact('faculties'));
    }

    public function getDepartment(Request $request)
    {
        $departments = DepartmentModel::with('faculty:id,faculty_name,faculty_code')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'department_name' => ['required', 'string', 'max:255', 'unique:departments,name'],
         
           
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $user = Auth::user();
        $department = DepartmentModel::create([
            'faculty_id' => $request->faculty_id,
            'name' => $request->department_name,
            'department_code' => $request->department_code,
            'head_chairman_name' => $request->head_chairman_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
            'user_id' => $user->id,
        ]);

        $department->load('faculty:id,faculty_name,faculty_code');

        return response()->json($department, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'department_name' => ['required', 'string', 'max:255', 'unique:departments,name,'.$id],
        
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $department = DepartmentModel::findOrFail($id);
        $department->update([
            'faculty_id' => $request->faculty_id,
            'name' => $request->department_name,
            'department_code' => $request->department_code,
            'head_chairman_name' => $request->head_chairman_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
        ]);

        $department->load('faculty:id,faculty_name,faculty_code');

        return response()->json($department);
    }

    public function destroy($id)
    {
        $department = DepartmentModel::findOrFail($id);
        $department->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
