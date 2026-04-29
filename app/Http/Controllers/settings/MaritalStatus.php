<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaritalStatus as MaritalStatusModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MaritalStatus extends Controller
{
    public function index()
    {
        return view('content.settings.marital-status');
    }

    public function getMaritalStatus(Request $request)
    {
        $maritalStatuses = MaritalStatusModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $maritalStatuses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'marital_status_name' => 'required|string|max:255|unique:marital_statuses,marital_status_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $maritalStatus = MaritalStatusModel::create([
            'marital_status_name' => $request->marital_status_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Marital Status created successfully.', 'data' => $maritalStatus], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'marital_status_name' => 'required|string|max:255|unique:marital_statuses,marital_status_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $maritalStatus = MaritalStatusModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $maritalStatus->update([
            'marital_status_name' => $request->marital_status_name,
        ]);

        return response()->json(['message' => 'Marital Status updated successfully.', 'data' => $maritalStatus]);
    }

    public function destroy($id)
    {
        $maritalStatus = MaritalStatusModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $maritalStatus->delete();

        return response()->json(['message' => 'Marital Status deleted successfully.']);
    }
}
