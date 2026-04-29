<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board as BoardModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Board extends Controller
{
  public function index()
  {
    return view('content.settings.board');
  }

  public function getBoard(Request $request)
  {
    $boards = BoardModel::all();
    return response()->json([
      'data' => $boards,
    ]);
  }

  public function store(Request $request)
  {
    $user = Auth::user();
    $userId = $user->id;
    $request->validate([
      'board_name' => 'required|string|max:255|unique:boards,board_name',
    ]);

    $board = BoardModel::create([
      'board_name' => $request->board_name,
      'user_id' => $userId,
    ]);

    return response()->json($board, Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'board_name' => 'required|string|max:255|unique:boards,board_name,' . $id,
    ]);

    $board = BoardModel::findOrFail($id);
    $board->update([
      'board_name' => $request->board_name,
    ]);

    return response()->json($board);
  }

  public function destroy($id)
  {
    $board = BoardModel::findOrFail($id);
    $board->delete();

    return response()->json(null, Response::HTTP_NO_CONTENT);
  }
}
