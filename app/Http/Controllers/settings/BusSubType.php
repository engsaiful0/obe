<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusSubType as BusSubTypeModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BusSubType extends Controller
{
    public function index()
    {
        return view('content.settings.bus-sub-type');
    }

    public function getBusSubType(Request $request)
    {
        $busSubTypes = BusSubTypeModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $busSubTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_type_name' => 'required|string|max:255|unique:bus_sub_types,sub_type_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $busSubType = BusSubTypeModel::create([
            'sub_type_name' => $request->sub_type_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Bus Sub-Type created successfully.', 'data' => $busSubType], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sub_type_name' => 'required|string|max:255|unique:bus_sub_types,sub_type_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $busSubType = BusSubTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $busSubType->update([
            'sub_type_name' => $request->sub_type_name,
        ]);

        return response()->json(['message' => 'Bus Sub-Type updated successfully.', 'data' => $busSubType]);
    }

    public function destroy($id)
    {
        $busSubType = BusSubTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $busSubType->delete();

        return response()->json(['message' => 'Bus Sub-Type deleted successfully.']);
    }
}

