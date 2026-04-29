<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department as DepartmentModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Department extends Controller
{
    public function index()
    {
        return view('content.settings.department');
    }

    public function getDepartment(Request $request)
    {
        $departments = DepartmentModel::orderBy('name')->get();
        return response()->json([
            'data' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,name',
        ]);

        $user = Auth::user();
        $department = DepartmentModel::create([
            'name' => $request->department_name,
            'user_id' => $user->id,
        ]);

        return response()->json($department, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,name,' . $id,
        ]);

        $department = DepartmentModel::findOrFail($id);
        $department->update([
            'name' => $request->department_name,
        ]);

        return response()->json($department);
    }

    public function destroy($id)
    {
        $department = DepartmentModel::findOrFail($id);
        $department->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
