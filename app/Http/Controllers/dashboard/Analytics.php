<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class Analytics extends Controller
{
  public function index()
  {
    $userId = Auth::id();
    
    return view('content.dashboard.dashboards-analytics', compact('pageConfigs'));
  }
}
