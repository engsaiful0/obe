<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeeSettings as FeeSettingsModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class FeeSettings extends Controller
{
    public function index()
    {
        return view('content.settings.fee-settings');
    }

    public function getFeeSettings(Request $request)
    {
        $feeSettings = FeeSettingsModel::all();
        return response()->json([
            'data' => $feeSettings,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:fee_settings,name',
            'amount' => 'required|numeric|min:0',
            'fine_type' => 'required|string|in:fixed,percentage',
            'fine_amount_per_day' => 'required_if:fine_type,fixed|nullable|numeric|min:0',
            'fine_percentage' => 'required_if:fine_type,percentage|nullable|numeric|min:0|max:100',
            'payment_deadline_day' => 'required|integer|min:1|max:31',
            'maximum_fine_amount' => 'nullable|numeric|min:0',
            'grace_period_days' => 'required|integer|min:0|max:365',
        ]);

        $user = Auth::user();
        $userId = $user->id;

        $feeSettings = FeeSettingsModel::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'fine_type' => $request->fine_type,
            'fine_amount_per_day' => $request->fine_type === 'fixed' ? $request->fine_amount_per_day : 0,
            'fine_percentage' => $request->fine_type === 'percentage' ? $request->fine_percentage : null,
            'is_percentage_fine' => $request->fine_type === 'percentage',
            'payment_deadline_day' => $request->payment_deadline_day,
            'maximum_fine_amount' => $request->maximum_fine_amount,
            'grace_period_days' => $request->grace_period_days,
            'is_active' => true,
            'notes' => $request->notes,
            'user_id' => $userId,
        ]);

        return response()->json(['message' => 'Fee Settings created successfully.', 'data' => $feeSettings], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:fee_settings,name,' . $id,
            'amount' => 'required|numeric|min:0',
            'fine_type' => 'required|string|in:fixed,percentage',
            'fine_amount_per_day' => 'required_if:fine_type,fixed|nullable|numeric|min:0',
            'fine_percentage' => 'required_if:fine_type,percentage|nullable|numeric|min:0|max:100',
            'payment_deadline_day' => 'required|integer|min:1|max:31',
            'maximum_fine_amount' => 'nullable|numeric|min:0',
            'grace_period_days' => 'required|integer|min:0|max:365',
        ]);

        $feeSettings = FeeSettingsModel::findOrFail($id);
        $feeSettings->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'fine_type' => $request->fine_type,
            'fine_amount_per_day' => $request->fine_type === 'fixed' ? $request->fine_amount_per_day : 0,
            'fine_percentage' => $request->fine_type === 'percentage' ? $request->fine_percentage : null,
            'is_percentage_fine' => $request->fine_type === 'percentage',
            'payment_deadline_day' => $request->payment_deadline_day,
            'maximum_fine_amount' => $request->maximum_fine_amount,
            'grace_period_days' => $request->grace_period_days,
            'notes' => $request->notes,
        ]);

        return response()->json(['message' => 'Fee Settings updated successfully.', 'data' => $feeSettings]);
    }

    public function destroy($id)
    {
        $feeSettings = FeeSettingsModel::findOrFail($id);
        $feeSettings->delete();

        return response()->json(['message' => 'Fee Settings deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
