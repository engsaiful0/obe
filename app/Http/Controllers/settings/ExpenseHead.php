<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ExpenseHead as ExpenseHeadModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ExpenseHead extends Controller
{
    public function index()
    {
        return view('content.settings.expense_head');
    }

    public function getExpenseHead(Request $request)
    {
        $expenseHeads = ExpenseHeadModel::all();
        return response()->json([
            'data' => $expenseHeads,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_heads,name',
        ]);
        $user = Auth::user();
        $userId = $user->id;
        $expenseHead = ExpenseHeadModel::create([
            'name' => $request->name,
            'user_id' => $userId,
        ]);

        return response()->json($expenseHead, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_heads,name,' . $id,
        ]);

        $expenseHead = ExpenseHeadModel::findOrFail($id);
        $expenseHead->update([
            'name' => $request->name,
        ]);

        return response()->json($expenseHead);
    }

    public function destroy($id)
    {
        $expenseHead = ExpenseHeadModel::findOrFail($id);
        $expenseHead->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
