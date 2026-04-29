<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item as ItemModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Item extends Controller
{
    public function index()
    {
        return view('content.settings.item');
    }

    public function getItem(Request $request)
    {
        $items = ItemModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255|unique:items,item_name,NULL,id,user_id,' . Auth::id(),
            'opening_stock' => 'nullable|numeric|min:0',
            'related_to' => ['nullable', 'string', 'max:50', Rule::in(['Bus', 'Fuel and Lubricant', 'Other'])],
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $item = ItemModel::create([
            'item_name' => $request->item_name,
            'opening_stock' => $request->opening_stock ?? 0,
            'related_to' => $request->related_to,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Item created successfully.', 'data' => $item], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'item_name' => 'required|string|max:255|unique:items,item_name,' . $id . ',id,user_id,' . Auth::id(),
            'opening_stock' => 'nullable|numeric|min:0',
            'related_to' => ['nullable', 'string', 'max:50', Rule::in(['Bus', 'Fuel and Lubricant', 'Other'])],
        ]);

        $item = ItemModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $item->update([
            'item_name' => $request->item_name,
            'opening_stock' => $request->opening_stock ?? 0,
            'related_to' => $request->related_to,
        ]);

        return response()->json(['message' => 'Item updated successfully.', 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = ItemModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully.']);
    }
}
