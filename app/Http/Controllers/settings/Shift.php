<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift as ShiftModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Shift extends Controller
{
  public function index()
  {
    return view('content.settings.shift');
  }

  public function getShifts(Request $request)
  {
    $shifts = ShiftModel::all();
    return response()->json([
      'data' => $shifts,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'shift_name' => 'required|string|max:255|unique:shifts,shift_name',
    ]);
$user = Auth::user();
        $userId = $user->id;
    $shift = ShiftModel::create([
      'shift_name' => $request->shift_name,
      'user_id' => $userId,
    ]);

    return response()->json($shift, Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'shift_name' => 'required|string|max:255|unique:shifts,shift_name,' . $id,
    ]);

    $shift = ShiftModel::findOrFail($id);
    $shift->update([
      'shift_name' => $request->shift_name,
    ]);

    return response()->json($shift);
  }

  public function destroy($id)
  {
    $shift = ShiftModel::findOrFail($id);
    $shift->delete();

    return response()->json(null, Response::HTTP_NO_CONTENT);
  }
}
