<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear as AcademicYearModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AcademicYear extends Controller
{
  public function index()
  {
    return view('content.settings.academic-year');
  }

  public function getAcademicYear(Request $request)
  {
    $academicYears = AcademicYearModel::all();
    return response()->json([
      'data' => $academicYears,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'academic_year_name' => 'required|string|max:255|unique:academic_years,academic_year_name',
    ]);
    $user = Auth::user();
    $userId = $user->id;

    $academicYear = AcademicYearModel::create([
      'academic_year_name' => $request->academic_year_name,
      'user_id' => $userId,
    ]);

    return response()->json($academicYear, Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'academic_year_name' => 'required|string|max:255|unique:academic_years,academic_year_name,' . $id,
    ]);

    $academicYear = AcademicYearModel::findOrFail($id);
    $academicYear->update([
      'academic_year_name' => $request->academic_year_name,
    ]);

    return response()->json($academicYear);
  }

  public function destroy($id)
  {
    $academicYear = AcademicYearModel::findOrFail($id);
    $academicYear->delete();

    return response()->json(null, Response::HTTP_NO_CONTENT);
  }
}
