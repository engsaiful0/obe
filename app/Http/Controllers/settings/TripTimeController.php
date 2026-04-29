<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TripTime;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class TripTimeController extends Controller
{
    public function index()
    {
        return view('content.settings.trip-time');
    }

    public function getTripTime(Request $request)
    {
        $tripTimes = TripTime::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $tripTimes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'time_name' => 'required|string|max:255',
            'time_value' => 'required|date_format:H:i|unique:trip_times,time_value',
            'time_period' => 'required|in:AM,PM',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $userId = $user->id;

        $tripTime = TripTime::create([
            'time_name' => $request->time_name,
            'time_value' => $request->time_value,
            'time_period' => $request->time_period,
            'description' => $request->description,
            'user_id' => $userId,
        ]);

        return response()->json(['message' => 'Trip time created successfully.', 'data' => $tripTime], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'time_name' => 'required|string|max:255',
            'time_value' => [
                'required',
                'date_format:H:i',
                Rule::unique('trip_times', 'time_value')->ignore($id),
            ],
            'time_period' => 'required|in:AM,PM',
            'description' => 'nullable|string|max:1000',
        ]);

        $tripTime = TripTime::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $tripTime->update([
            'time_name' => $request->time_name,
            'time_value' => $request->time_value,
            'time_period' => $request->time_period,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Trip time updated successfully.',
            'data' => $tripTime
        ]);
    }

    public function destroy($id)
    {
        $tripTime = TripTime::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $tripTime->delete();

        return response()->json(['message' => 'Trip time deleted successfully.']);
    }
}
