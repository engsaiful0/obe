<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Unit as UnitModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Unit extends Controller
{
    public function index()
    {
        return view('content.settings.unit');
    }

    public function getUnit(Request $request)
    {
        $units = UnitModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255',
            'related_to' => ['required', 'string', 'max:50', Rule::in(['Bus', 'Fuel and Lubricant', 'Other'])],
        ]);

        $user = Auth::user();
        $userId = $user->id;

        $unit = UnitModel::create([
            'unit_name' => $request->unit_name,
            'related_to' => $request->related_to,
            'user_id' => $userId,
        ]);

        return response()->json(['message' => 'Unit created successfully.', 'data' => $unit], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255|unique:units,unit_name,' . $id . ',id,user_id,' . Auth::id(),
            'related_to' => ['nullable', 'string', 'max:50', Rule::in(['Bus', 'Fuel and Lubricant', 'Other'])],
        ]);

        $unit = UnitModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $unit->update([
            'unit_name' => $request->unit_name,
            'related_to' => $request->related_to,
        ]);

        return response()->json(['message' => 'Unit updated successfully.', 'data' => $unit]);
    }

    public function destroy($id)
    {
        $unit = UnitModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $unit->delete();

        return response()->json(['message' => 'Unit deleted successfully.']);
    }
}
