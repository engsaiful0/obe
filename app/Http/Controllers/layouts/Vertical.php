<?php

namespace App\Http\Controllers\layouts;

use App\Http\Controllers\Controller;


use App\Models\Expense;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\BusHelper;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\Damage;
use App\Models\Reward;
use App\Models\Punishment;
use App\Models\BusTrip;
use App\Models\BusSchedule;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\PurchaseItem;
use App\Models\DamageItem;
use App\Models\MonthlyBill;
use App\Models\Supplier;
use App\Models\Status as StatusModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Vertical extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'vertical'];


        return view('content.dashboard.dashboards-analytics', compact(
            'pageConfigs',
        ));
    }
}
