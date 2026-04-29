<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Color as ColorModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Color extends Controller
{
    public function index()
    {
        return view('content.settings.color');
    }

    public function getColor(Request $request)
    {
        $colors = ColorModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $colors,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'color_name' => 'required|string|max:255|unique:colors,color_name,NULL,id,user_id,' . Auth::id(),
            'color_code' => 'required|string|max:7|unique:colors,color_code,NULL,id,user_id,' . Auth::id() . '|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
        ], [
            'color_code.regex' => 'Color code must be a valid hex color (e.g., #FF5733)',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $color = ColorModel::create([
            'color_name' => $request->color_name,
            'color_code' => strtoupper($request->color_code),
            'color_view' => strtoupper($request->color_code),
            'description' => $request->description,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Color created successfully.', 'data' => $color], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'color_name' => 'required|string|max:255|unique:colors,color_name,' . $id . ',id,user_id,' . Auth::id(),
            'color_code' => 'required|string|max:7|unique:colors,color_code,' . $id . ',id,user_id,' . Auth::id() . '|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
        ], [
            'color_code.regex' => 'Color code must be a valid hex color (e.g., #FF5733)',
        ]);

        $color = ColorModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $color->update([
            'color_name' => $request->color_name,
            'color_code' => strtoupper($request->color_code),
            'color_view' => strtoupper($request->color_code),
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Color updated successfully.', 'data' => $color]);
    }

    public function destroy($id)
    {
        $color = ColorModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $color->delete();

        return response()->json(['message' => 'Color deleted successfully.']);
    }
}
