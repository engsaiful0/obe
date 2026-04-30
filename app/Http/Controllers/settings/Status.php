<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Status as StatusModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Status extends Controller
{
    public function index()
    {
        return view('content.settings.status');
    }

    public function getStatus(Request $request)
    {
        $statuses = StatusModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $statuses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'status_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('statuses', 'status_name')
                    ->where('related_to', $request->related_to)
            ],
            'related_to' => ['required', 'string', 'max:255', Rule::exists('related_tos', 'name')],
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $status = StatusModel::create([
            'status_name' => $request->status_name,
            'related_to' => $request->related_to,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Status created successfully.', 'data' => $status], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('statuses', 'status_name')
                    ->ignore($id)
                    ->where('related_to', $request->related_to)
            ],
            'related_to' => ['required', 'string', 'max:255', Rule::exists('related_tos', 'name')],
        ]);

        $status = StatusModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $status->update([
            'status_name' => $request->status_name,
            'related_to' => $request->related_to,
        ]);

        return response()->json(['message' => 'Status updated successfully.', 'data' => $status]);
    }

    public function destroy($id)
    {
        $status = StatusModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $status->delete();

        return response()->json(['message' => 'Status deleted successfully.']);
    }
}
