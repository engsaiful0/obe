<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RewardType as RewardTypeModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RewardType extends Controller
{
    public function index()
    {
        return view('content.settings.reward-type');
    }

    public function getRewardType(Request $request)
    {
        $rewardTypes = RewardTypeModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $rewardTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:reward_types,name,NULL,id,user_id,' . Auth::id(),
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $rewardType = RewardTypeModel::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Reward type created successfully.', 'data' => $rewardType], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:reward_types,name,' . $id . ',id,user_id,' . Auth::id(),
            'description' => 'nullable|string|max:1000',
        ]);

        $rewardType = RewardTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $rewardType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Reward type updated successfully.', 'data' => $rewardType]);
    }

    public function destroy($id)
    {
        $rewardType = RewardTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $rewardType->delete();

        return response()->json(['message' => 'Reward type deleted successfully.']);
    }
}

