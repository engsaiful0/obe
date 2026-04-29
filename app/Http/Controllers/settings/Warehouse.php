<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Warehouse as WarehouseModel;

class Warehouse extends Controller
{
    public function index()
    {
        return view('content.settings.warehouse');
    }

    public function getData(Request $request)
    {
        $warehouses = WarehouseModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_name' => 'required|string|max:255|unique:warehouses,warehouse_name,NULL,id,user_id,' . Auth::id(),
            'warehouse_number' => 'required|string|max:100|unique:warehouses,warehouse_number,NULL,id,user_id,' . Auth::id(),
            'address' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        $warehouse = WarehouseModel::create([
            'warehouse_name' => $request->warehouse_name,
            'warehouse_number' => $request->warehouse_number,
            'address' => $request->address,
            'user_id' => $userId,
        ]);

        return response()->json(['message' => 'Warehouse created successfully.', 'data' => $warehouse], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'warehouse_name' => 'required|string|max:255|unique:warehouses,warehouse_name,' . $id . ',id,user_id,' . Auth::id(),
            'warehouse_number' => 'required|string|max:100|unique:warehouses,warehouse_number,' . $id . ',id,user_id,' . Auth::id(),
            'address' => 'nullable|string|max:500',
        ]);

        $warehouse = WarehouseModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $warehouse->update([
            'warehouse_name' => $request->warehouse_name,
            'warehouse_number' => $request->warehouse_number,
            'address' => $request->address,
        ]);

        return response()->json(['message' => 'Warehouse updated successfully.', 'data' => $warehouse]);
    }

    public function destroy($id)
    {
        $warehouse = WarehouseModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $warehouse->delete();

        return response()->json(['message' => 'Warehouse deleted successfully.']);
    }
}


