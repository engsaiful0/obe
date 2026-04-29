<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nationality as NationalityModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Nationality extends Controller
{
  public function index()
  {
    return view('content.settings.nationality');
  }

  public function getNationalities(Request $request)
  {
    $nationalities = NationalityModel::all();
    return response()->json([
      'data' => $nationalities,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'nationality_name' => 'required|string|max:255|unique:nationalities,nationality_name',
    ]);
    $user = Auth::user();
    $userId = $user->id;
    $nationality = NationalityModel::create([
      'nationality_name' => $request->nationality_name,
      'user_id' => $userId,
    ]);

    return response()->json(['message' => 'Nationality added successfully.'], Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'nationality_name' => 'required|string|max:255|unique:nationalities,nationality_name,' . $id,
    ]);

    $nationality = NationalityModel::findOrFail($id);
    $nationality->update([
      'nationality_name' => $request->nationality_name,
    ]);

    return response()->json(['message' => 'Nationality updated successfully.']);
  }

  public function destroy($id)
  {
    $nationality = NationalityModel::findOrFail($id);
    $nationality->delete();

    return response()->json(['message' => 'Nationality deleted successfully.']);
  }
}
