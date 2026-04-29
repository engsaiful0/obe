<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Semester as SemesterModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Semester extends Controller
{
  public function index()
  {
    return view('content.settings.semester');
  }

  public function getSemester(Request $request)
  {
    $semesters = SemesterModel::all();
    return response()->json([
      'data' => $semesters,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'semester_name' => 'required|string|max:255|unique:semesters,semester_name',
    ]);
$user = Auth::user();
        $userId = $user->id;
    $semester = SemesterModel::create([
      'semester_name' => $request->semester_name,
      'user_id' => $userId,
    ]);
     return response()->json(['message' => 'Semester created successfully.', 'data' => $semester], Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'semester_name' => 'required|string|max:255|unique:semesters,semester_name,' . $id,
    ]);

    $semester = SemesterModel::findOrFail($id);
    $semester->update([
      'semester_name' => $request->semester_name,
    ]);

    return response()->json(['message' => 'Semester updated successfully.', 'data' => $semester]);
  }

  public function destroy($id)
  {
    $semester = SemesterModel::findOrFail($id);
    $semester->delete();

    return response()->json(['message' => 'Semester deleted successfully.']);
  }
}
