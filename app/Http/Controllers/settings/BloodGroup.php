<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup as BloodGroupModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BloodGroup extends Controller
{
    public function index()
    {
        return view('content.settings.blood-group');
    }

    public function getBloodGroup(Request $request)
    {
        $bloodGroups = BloodGroupModel::all();

        return response()->json([
            'data' => $bloodGroups
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blood_group_name' => 'required|string|max:255|unique:blood_groups,blood_group_name,NULL,id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bloodGroup = BloodGroupModel::create([
            'blood_group_name' => $request->blood_group_name,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Blood Group created successfully.', 'data' => $bloodGroup]);
    }

    public function update(Request $request, $id)
    {
        $bloodGroup = BloodGroupModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'blood_group_name' => 'required|string|max:255|unique:blood_groups,blood_group_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bloodGroup->update([
            'blood_group_name' => $request->blood_group_name,
        ]);

        return response()->json(['message' => 'Blood Group updated successfully.', 'data' => $bloodGroup]);
    }

    public function destroy($id)
    {
        $bloodGroup = BloodGroupModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $bloodGroup->delete();

        return response()->json(['message' => 'Blood Group deleted successfully.']);
    }
}
