<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gender as GenderModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Gender extends Controller
{
    public function index()
    {
        return view('content.settings.gender');
    }

    public function getGender(Request $request)
    {
        $genders = GenderModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $genders,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'gender_name' => 'required|string|max:255|unique:genders,gender_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $gender = GenderModel::create([
            'gender_name' => $request->gender_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Gender created successfully.', 'data' => $gender], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'gender_name' => 'required|string|max:255|unique:genders,gender_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $gender = GenderModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $gender->update([
            'gender_name' => $request->gender_name,
        ]);

        return response()->json(['message' => 'Gender updated successfully.', 'data' => $gender]);
    }

    public function destroy($id)
    {
        $gender = GenderModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $gender->delete();

        return response()->json(['message' => 'Gender deleted successfully.']);
    }
}
