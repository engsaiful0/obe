<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Year as YearModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Year extends Controller
{
  public function index()
  {
    return view('content.settings.year');
  }

  public function getYear(Request $request)
  {
    $years = YearModel::all();
    return response()->json([
      'data' => $years,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'year_name' => 'required|string|max:255|unique:years,year_name',
    ]);
    $user = Auth::user();
    $userId = $user->id;
    $year = YearModel::create([
      'year_name' => $request->year_name,
      'user_id' => $userId,
    ]);
    return response()->json(['message' => 'Year created successfully.', 'data' => $year], Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'year_name' => 'required|string|max:255|unique:years,year_name,' . $id,
    ]);

    $year = YearModel::findOrFail($id);
    $year->update([
      'year_name' => $request->year_name,
    ]);
    return response()->json(['message' => 'Year updated successfully.', 'data' => $year]);
  }

  public function destroy($id)
  {
    $year = YearModel::findOrFail($id);
    $year->delete();
    return response()->json(['message' => 'Year deleted successfully.'], Response::HTTP_NO_CONTENT);
  }
}
