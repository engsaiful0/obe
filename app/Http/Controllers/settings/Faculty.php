<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Faculty as FacultyModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Faculty extends Controller
{
    public function index()
    {
        return view('content.settings.faculty');
    }

    public function getFaculty(Request $request)
    {
        $faculties = FacultyModel::orderBy('faculty_name')->get();

        return response()->json([
            'data' => $faculties,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'faculty_name' => 'required|string|max:255|unique:faculties,faculty_name',
            'faculty_code' => 'required|string|max:50|unique:faculties,faculty_code',
            'dean_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'status' => 'required|in:Active,Inactive',
        ]);

        $user = Auth::user();

        $faculty = FacultyModel::create([
            'faculty_name' => $request->faculty_name,
            'faculty_code' => $request->faculty_code,
            'dean_name' => $request->dean_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
            'user_id' => $user->id,
        ]);

        return response()->json($faculty, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'faculty_name' => 'required|string|max:255|unique:faculties,faculty_name,' . $id,
            'faculty_code' => 'required|string|max:50|unique:faculties,faculty_code,' . $id,
            'dean_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'status' => 'required|in:Active,Inactive',
        ]);

        $faculty = FacultyModel::findOrFail($id);
        $faculty->update([
            'faculty_name' => $request->faculty_name,
            'faculty_code' => $request->faculty_code,
            'dean_name' => $request->dean_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
        ]);

        return response()->json($faculty);
    }

    public function destroy($id)
    {
        $faculty = FacultyModel::findOrFail($id);
        $faculty->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
