<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\IncomeHead as IncomeHeadModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class IncomeHead extends Controller
{
    public function index()
    {
        return view('content.settings.income_head');
    }

    public function getIncomeHead(Request $request)
    {
        $incomeHeads = IncomeHeadModel::all();
        return response()->json([
            'data' => $incomeHeads,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:income_heads,name',
        ]);
        $user = Auth::user();
        $userId = $user->id;
        $incomeHead = IncomeHeadModel::create([
            'name' => $request->name,
            'user_id' => $userId,
        ]);

        return response()->json($incomeHead, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:income_heads,name,' . $id,
        ]);

        $incomeHead = IncomeHeadModel::findOrFail($id);
        $incomeHead->update([
            'name' => $request->name,
        ]);

        return response()->json($incomeHead);
    }

    public function destroy($id)
    {
        $incomeHead = IncomeHeadModel::findOrFail($id);
        $incomeHead->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
