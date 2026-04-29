<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EducationalQualification as EducationalQualificationModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EducationalQualification extends Controller
{
    public function index()
    {
        return view('content.settings.educational-qualification');
    }

    public function getEducationalQualification(Request $request)
    {
        $qualifications = EducationalQualificationModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $qualifications,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'qualification_name' => 'required|string|max:255|unique:educational_qualifications,qualification_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $qualification = EducationalQualificationModel::create([
            'qualification_name' => $request->qualification_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Educational qualification created successfully.', 'data' => $qualification], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'qualification_name' => 'required|string|max:255|unique:educational_qualifications,qualification_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $qualification = EducationalQualificationModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $qualification->update([
            'qualification_name' => $request->qualification_name,
        ]);

        return response()->json(['message' => 'Educational qualification updated successfully.', 'data' => $qualification]);
    }

    public function destroy($id)
    {
        $qualification = EducationalQualificationModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $qualification->delete();

        return response()->json(['message' => 'Educational qualification deleted successfully.']);
    }
}
