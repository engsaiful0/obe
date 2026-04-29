<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusType as BusTypeModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BusType extends Controller
{
    public function index()
    {
        return view('content.settings.bus-type');
    }

    public function getBusType(Request $request)
    {
        $busTypes = BusTypeModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $busTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bus_type_name' => 'required|string|max:255|unique:bus_types,bus_type_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $busType = BusTypeModel::create([
            'bus_type_name' => $request->bus_type_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Bus Type created successfully.', 'data' => $busType], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bus_type_name' => 'required|string|max:255|unique:bus_types,bus_type_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $busType = BusTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $busType->update([
            'bus_type_name' => $request->bus_type_name,
        ]);

        return response()->json(['message' => 'Bus Type updated successfully.', 'data' => $busType]);
    }

    public function destroy($id)
    {
        $busType = BusTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $busType->delete();

        return response()->json(['message' => 'Bus Type deleted successfully.']);
    }
}
