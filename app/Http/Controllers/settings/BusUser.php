<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusUser as BusUserModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BusUser extends Controller
{
    public function index()
    {
        return view('content.settings.bus-user');
    }

    public function getBusUser(Request $request)
    {
        $busUsers = BusUserModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $busUsers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bus_user_name' => 'required|string|max:255|unique:bus_users,bus_user_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $busUser = BusUserModel::create([
            'bus_user_name' => $request->bus_user_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Bus User created successfully.', 'data' => $busUser], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bus_user_name' => 'required|string|max:255|unique:bus_users,bus_user_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $busUser = BusUserModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $busUser->update([
            'bus_user_name' => $request->bus_user_name,
        ]);

        return response()->json(['message' => 'Bus User updated successfully.', 'data' => $busUser]);
    }

    public function destroy($id)
    {
        $busUser = BusUserModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $busUser->delete();

        return response()->json(['message' => 'Bus User deleted successfully.']);
    }
}
