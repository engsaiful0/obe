<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FuelType as FuelTypeModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class FuelType extends Controller
{
    public function index()
    {
        return view('content.settings.fuel-type');
    }

    public function getFuelType(Request $request)
    {
        $fuelTypes = FuelTypeModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $fuelTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fuel_type_name' => 'required|string|max:255|unique:fuel_types,fuel_type_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $fuelType = FuelTypeModel::create([
            'fuel_type_name' => $request->fuel_type_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Fuel Type created successfully.', 'data' => $fuelType], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fuel_type_name' => 'required|string|max:255|unique:fuel_types,fuel_type_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $fuelType = FuelTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $fuelType->update([
            'fuel_type_name' => $request->fuel_type_name,
        ]);

        return response()->json(['message' => 'Fuel Type updated successfully.', 'data' => $fuelType]);
    }

    public function destroy($id)
    {
        $fuelType = FuelTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $fuelType->delete();

        return response()->json(['message' => 'Fuel Type deleted successfully.']);
    }
}
