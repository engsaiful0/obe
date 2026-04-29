<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Religion as ReligionModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Religion extends Controller
{
  public function index()
  {
    return view('content.settings.religion');
  }

  public function getReligions(Request $request)
  {
    $religions = ReligionModel::all();
    return response()->json([
      'data' => $religions,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'religion_name' => 'required|string|max:255|unique:religions,religion_name',
    ]);
    $user = Auth::user();
    $userId = $user->id;
    $religion = ReligionModel::create([
      'religion_name' => $request->religion_name,
      'user_id' => $userId,
    ]);

    return response()->json($religion, Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'religion_name' => 'required|string|max:255|unique:religions,religion_name,' . $id,
    ]);

    $religion = ReligionModel::findOrFail($id);
    $religion->update([
      'religion_name' => $request->religion_name,
    ]);

    return response()->json($religion);
  }

  public function destroy($id)
  {
    $religion = ReligionModel::findOrFail($id);
    $religion->delete();

    return response()->json(null, Response::HTTP_NO_CONTENT, ['message' => 'Religion deleted successfully']);
  }
}
