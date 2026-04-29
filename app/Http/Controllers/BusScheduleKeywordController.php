<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusScheduleKeyword;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BusScheduleKeywordController extends Controller
{
    public function index()
    {
        return view('content.bus-schedule-keywords.index');
    }

    public function getData(Request $request)
    {
        $keywords = BusScheduleKeyword::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $keywords,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'keyword_name' => 'required|string|max:255|unique:bus_schedule_keywords,keyword_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $keyword = BusScheduleKeyword::create([
            'keyword_name' => $request->keyword_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Bus Schedule Keyword created successfully.', 'data' => $keyword], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'keyword_name' => 'required|string|max:255|unique:bus_schedule_keywords,keyword_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $keyword = BusScheduleKeyword::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $keyword->update([
            'keyword_name' => $request->keyword_name,
        ]);

        return response()->json(['message' => 'Bus Schedule Keyword updated successfully.', 'data' => $keyword]);
    }

    public function destroy($id)
    {
        $keyword = BusScheduleKeyword::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $keyword->delete();

        return response()->json(['message' => 'Bus Schedule Keyword deleted successfully.']);
    }
}