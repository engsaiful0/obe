<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\SscPassingSession as SscPassingSessionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SscPassingSession extends Controller
{
    public function index()
    {
        return view('content.settings.ssc-session');
    }

    public function getSscSession(Request $request)
    {
        $sscSessions = SscPassingSessionModel::all();

        return response()->json([
            'data' => $sscSessions
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_name' => 'required|string|max:255|unique:ssc_passing_sessions,session_name,NULL,id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sscSession = SscPassingSessionModel::create([
            'session_name' => $request->session_name,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'SSC Session created successfully.', 'data' => $sscSession]);
    }

    public function edit($id)
    {
        $sscSession = SscPassingSessionModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json(['data' => $sscSession]);
    }

    public function update(Request $request, $id)
    {
        $sscSession = SscPassingSessionModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'session_name' => 'required|string|max:255|unique:ssc_passing_sessions,session_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sscSession->update([
            'session_name' => $request->session_name,
        ]);

        return response()->json(['message' => 'SSC Session updated successfully.', 'data' => $sscSession]);
    }

    public function destroy($id)
    {
        $sscSession = SscPassingSessionModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $sscSession->delete();

        return response()->json(['message' => 'SSC Session deleted successfully.']);
    }
}
