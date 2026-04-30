<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\RelatedTo as RelatedToModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RelatedTo extends Controller
{
    public function index()
    {
        return view('content.settings.related-to');
    }

    public function getRelatedTo(Request $request)
    {
        $items = RelatedToModel::query()->orderBy('name')->get();

        return response()->json([
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:related_tos,name',
        ]);

        $userId = Auth::id();
        $item = RelatedToModel::create([
            'name' => $request->name,
            'user_id' => $userId,
        ]);

        return response()->json($item, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:related_tos,name,'.$id,
        ]);

        $item = RelatedToModel::findOrFail($id);
        $item->update([
            'name' => $request->name,
        ]);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = RelatedToModel::findOrFail($id);
        $item->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
