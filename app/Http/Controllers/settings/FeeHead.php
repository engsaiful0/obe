<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeeHead as FeeHeadModel;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class FeeHead extends Controller
{
    public function index()
    {
        return view('content.settings.fee_head');
    }

    public function getFeeHead(Request $request)
    {
        $feeHeads = FeeHeadModel::with('semester')->with('month')->get();
        return response()->json([
            'data' => $feeHeads,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:fee_heads,name',
            'amount' => 'required|numeric|min:0',
            'is_discountable' => 'required|string',
            'fee_type' => 'required|string|in:Regular,Monthly',
            'month_id' => 'required_if:fee_type,Monthly|nullable|integer|exists:months,id',
            'semester_id' => 'required_if:fee_type,Regular|nullable|integer|exists:semesters,id',
        ]);
        $user = Auth::user();
        $userId = $user->id;

        $feeHead = FeeHeadModel::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'fee_type' => $request->fee_type,
            'is_discountable' => $request->is_discountable,
            'semester_id' => $request->semester_id,
            'month_id' => $request->month_id,
            'user_id' => $userId,
        ]);

        return response()->json(['message' => 'Fee head created successfully.', 'data' => $feeHead], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id = null)
    {
        // If ID is not provided in URL, get it from request body
        if ($id === null) {
            $id = $request->input('id');
        }

        $request->validate([
            'id' => 'required|integer|exists:fee_heads,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fee_heads')->ignore($id),
            ],
            'amount' => 'required|numeric|min:0',
            'is_discountable' => 'required|string',
            'fee_type' => 'required|string|in:Regular,Monthly',
            'month_id' => 'required_if:fee_type,Monthly|nullable|integer|exists:months,id',
            'semester_id' => 'required_if:fee_type,Regular|nullable|integer|exists:semesters,id',
        ]);

        $feeHead = FeeHeadModel::findOrFail($id);
        $feeHead->update([
            'name' => $request->name,
            'fee_type' => $request->fee_type,
            'semester_id' => $request->semester_id,
            'month_id' => $request->month_id,
            'is_discountable' => $request->is_discountable,
            'amount' => $request->amount,
        ]);
        return response()->json(['message' => 'Fee Head updated successfully.', 'data' => $feeHead]);
    }

    public function destroy($id)
    {
        $feeHead = FeeHeadModel::findOrFail($id);
        $feeHead->delete();
        return response()->json(['message' => 'Fee Head deleted successfully.', Response::HTTP_NO_CONTENT]);
    }
}
