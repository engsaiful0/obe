<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ViolationType as ViolationTypeModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ViolationType extends Controller
{
    public function index()
    {
        return view('content.settings.violation-type');
    }

    public function getViolationType(Request $request)
    {
        $violationTypes = ViolationTypeModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $violationTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:violation_types,name,NULL,id,user_id,' . Auth::id(),
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $violationType = ViolationTypeModel::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Violation type created successfully.', 'data' => $violationType], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:violation_types,name,' . $id . ',id,user_id,' . Auth::id(),
            'description' => 'nullable|string|max:1000',
        ]);

        $violationType = ViolationTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $violationType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Violation type updated successfully.', 'data' => $violationType]);
    }

    public function destroy($id)
    {
        $violationType = ViolationTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $violationType->delete();

        return response()->json(['message' => 'Violation type deleted successfully.']);
    }
}
