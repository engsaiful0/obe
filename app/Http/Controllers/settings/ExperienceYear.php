<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExperienceYear as ExperienceYearModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ExperienceYear extends Controller
{
    public function index()
    {
        return view('content.settings.experience-year');
    }

    public function getExperienceYear(Request $request)
    {
        $experienceYears = ExperienceYearModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $experienceYears,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'experience_year' => 'required|string|max:255|unique:experience_years,experience_year,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $experienceYear = ExperienceYearModel::create([
            'experience_year' => $request->experience_year,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Experience Year created successfully.', 'data' => $experienceYear], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'experience_year' => 'required|string|max:255|unique:experience_years,experience_year,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $experienceYear = ExperienceYearModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $experienceYear->update([
            'experience_year' => $request->experience_year,
        ]);

        return response()->json(['message' => 'Experience Year updated successfully.', 'data' => $experienceYear]);
    }

    public function destroy($id)
    {
        $experienceYear = ExperienceYearModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $experienceYear->delete();

        return response()->json(['message' => 'Experience Year deleted successfully.']);
    }
}
