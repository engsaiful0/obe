<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Technology as TechnologyModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Technology extends Controller
{
  public function index()
  {
    return view('content.settings.technology');
  }

  public function getTechnology(Request $request)
  {
    $technologies = TechnologyModel::all();
    return response()->json([
      'data' => $technologies,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'technology_name' => 'required|string|max:255|unique:technologies,technology_name',
    ]);
    $user = Auth::user();
    $userId = $user->id;
    $technology = TechnologyModel::create([
      'technology_name' => $request->technology_name,
      'user_id' => $userId,
    ]);
    return response()->json(['message' => 'Technology created successfully.', 'data' => $technology], Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'technology_name' => 'required|string|max:255|unique:technologies,technology_name,' . $id,
    ]);

    $technology = TechnologyModel::findOrFail($id);
    $technology->update([
      'technology_name' => $request->technology_name,
    ]);
    return response()->json(['message' => 'Technology updated successfully.', 'data' => $technology]);
  }

  public function destroy($id)
  {
    $technology = TechnologyModel::findOrFail($id);
    $technology->delete();
    return response()->json(['message' => 'Technology deleted successfully.'], Response::HTTP_NO_CONTENT);
  }
}
