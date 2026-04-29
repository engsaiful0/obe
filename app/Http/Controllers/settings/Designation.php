<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\designation as designationModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Designation extends Controller
{
  public function index()
  {
    return view('content.settings.designation');
  }

  public function getdesignation(Request $request)
  {
    $designations = designationModel::all();
    return response()->json([
      'data' => $designations,
    ]);
  }

  public function store(Request $request)
  {

    $request->validate([
      'designation_name' => 'required|string|max:255|unique:designations,designation_name',
      'designation_type' => 'required|in:Teacher,Employee,Management',
    ]);
    $user = Auth::user();
    $userId = $user->id;
    $designation = designationModel::create([
      'designation_name' => $request->designation_name,
      'designation_type' => $request->designation_type,
      'user_id' => $userId,
    ]);

    return response()->json($designation, Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'designation_name' => 'required|string|max:255|unique:designations,designation_name,' . $id,
      'designation_type' => 'required|in:Teacher,Employee,Management',
    ]);

    $designation = designationModel::findOrFail($id);
    $designation->update([
      'designation_name' => $request->designation_name,
      'designation_type' => $request->designation_type,
    ]);

    return response()->json($designation);
  }

  public function destroy($id)
  {
    $designation = designationModel::findOrFail($id);
    $designation->delete();

    return response()->json(null, Response::HTTP_NO_CONTENT);
  }
}
