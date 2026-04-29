<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\DriverType as DriverTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DriverType extends Controller
{
    public function index()
    {
        return view('content.settings.driver-type');
    }

    public function getDriverType(Request $request)
    {
        $driverTypes = DriverTypeModel::all();

        return response()->json([
            'data' => $driverTypes
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_type_name' => 'required|string|max:255|unique:driver_types,driver_type_name,NULL,id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $driverType = DriverTypeModel::create([
            'driver_type_name' => $request->driver_type_name,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Driver Type created successfully.', 'data' => $driverType]);
    }

    public function update(Request $request, $id)
    {
        $driverType = DriverTypeModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'driver_type_name' => 'required|string|max:255|unique:driver_types,driver_type_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $driverType->update([
            'driver_type_name' => $request->driver_type_name,
        ]);

        return response()->json(['message' => 'Driver Type updated successfully.', 'data' => $driverType]);
    }

    public function destroy($id)
    {
        $driverType = DriverTypeModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $driverType->delete();

        return response()->json(['message' => 'Driver Type deleted successfully.']);
    }
}
