<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Month as MonthModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Month extends Controller
{

  public function getMonth(Request $request)
  {
    $months = MonthModel::all();
    return response()->json([
      'data' => $months,
    ]);
  }

}
