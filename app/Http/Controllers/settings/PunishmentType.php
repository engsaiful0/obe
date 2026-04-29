<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PunishmentType as PunishmentTypeModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PunishmentType extends Controller
{
    public function index()
    {
        return view('content.settings.punishment-type');
    }

    public function getPunishmentType(Request $request)
    {
        $punishmentTypes = PunishmentTypeModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $punishmentTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:punishment_types,name,NULL,id,user_id,' . Auth::id(),
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $punishmentType = PunishmentTypeModel::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Punishment type created successfully.', 'data' => $punishmentType], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:punishment_types,name,' . $id . ',id,user_id,' . Auth::id(),
            'description' => 'nullable|string|max:1000',
        ]);

        $punishmentType = PunishmentTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $punishmentType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Punishment type updated successfully.', 'data' => $punishmentType]);
    }

    public function destroy($id)
    {
        $punishmentType = PunishmentTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        // Check if this punishment type is being used by any punishments
        if ($punishmentType->punishments()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete punishment type. It is being used by existing punishments.',
                'error' => 'in_use'
            ], 422);
        }
        
        $punishmentType->delete();

        return response()->json(['message' => 'Punishment type deleted successfully.']);
    }
}
