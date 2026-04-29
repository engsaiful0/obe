<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand as BrandModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Brand extends Controller
{
    public function index()
    {
        return view('content.settings.brand');
    }

    public function getBrand(Request $request)
    {
        $brands = BrandModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $brands,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255|unique:brands,brand_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $brand = BrandModel::create([
            'brand_name' => $request->brand_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Brand created successfully.', 'data' => $brand], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255|unique:brands,brand_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $brand = BrandModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $brand->update([
            'brand_name' => $request->brand_name,
        ]);

        return response()->json(['message' => 'Brand updated successfully.', 'data' => $brand]);
    }

    public function destroy($id)
    {
        $brand = BrandModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $brand->delete();

        return response()->json(['message' => 'Brand deleted successfully.']);
    }
}
