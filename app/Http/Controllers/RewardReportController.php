<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\RewardType;
use App\Models\Bus;
use App\Models\BusSubType;
use App\Models\Driver;
use App\Models\BusHelper;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RewardReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Reward::with(['bus', 'driver', 'busHelper', 'rewardType', 'user']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $perPage = $request->input('per_page', 10);

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('reward_type_id')) {
            $query->where('reward_type_id', $request->reward_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->whereHas('bus', function ($busQuery) use ($request) {
                $busQuery->where('bus_sub_type_id', $request->bus_sub_type_id);
            });
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        if ($fromDate) {
            $query->whereDate('reward_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('reward_date', '<=', $toDate);
        }

        $totalAmount = $query->sum('reward_amount');
        $rewards = $query->latest('reward_date')->paginate($perPage);
        
        $rewardTypes = RewardType::all();
        $buses = Bus::with(['brand', 'yearOfManufacture'])->get();
        $busSubTypes = BusSubType::all();
        $drivers = Driver::all();
        $busHelpers = BusHelper::all();

        return view('content.report.reward-report', compact(
            'rewards', 
            'rewardTypes', 
            'buses', 
            'busSubTypes',
            'drivers', 
            'busHelpers', 
            'totalAmount',
            'perPage'
        ));
    }

    public function ajax(Request $request)
    {
        $query = Reward::with(['bus', 'driver', 'busHelper', 'rewardType', 'user']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $perPage = $request->input('per_page', 10);

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('reward_type_id')) {
            $query->where('reward_type_id', $request->reward_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->whereHas('bus', function ($busQuery) use ($request) {
                $busQuery->where('bus_sub_type_id', $request->bus_sub_type_id);
            });
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        if ($fromDate) {
            $query->whereDate('reward_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('reward_date', '<=', $toDate);
        }

        $totalAmount = $query->sum('reward_amount');
        $rewards = $query->latest('reward_date')->paginate($perPage);

        return response()->json([
            'rewards' => $rewards,
            'totalAmount' => $totalAmount,
            'html' => view('content.report.reward-report-table', compact('rewards', 'totalAmount'))->render()
        ]);
    }

    public function printList(Request $request)
    {
        $query = Reward::with(['bus', 'driver', 'busHelper', 'rewardType', 'user']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('reward_type_id')) {
            $query->where('reward_type_id', $request->reward_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->whereHas('bus', function ($busQuery) use ($request) {
                $busQuery->where('bus_sub_type_id', $request->bus_sub_type_id);
            });
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        if ($fromDate) {
            $query->whereDate('reward_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('reward_date', '<=', $toDate);
        }

        // Get all rewards (no pagination for print)
        $rewards = $query->latest('reward_date')->get();
        $totalAmount = $rewards->sum('reward_amount');

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('reward_type_id')) {
            $rewardType = RewardType::find($request->reward_type_id);
            $filterInfo['reward_type'] = $rewardType ? $rewardType->name : 'N/A';
        }
        if ($request->filled('bus_sub_type_id')) {
            $busSubType = BusSubType::find($request->bus_sub_type_id);
            $filterInfo['bus_sub_type'] = $busSubType ? $busSubType->sub_type_name : 'N/A';
        }
        if ($request->filled('bus_id')) {
            $bus = Bus::find($request->bus_id);
            $filterInfo['bus'] = $bus ? $bus->bus_number : 'N/A';
        }
        if ($request->filled('driver_id')) {
            $driver = Driver::find($request->driver_id);
            $filterInfo['driver'] = $driver ? $driver->full_name : 'N/A';
        }
        if ($request->filled('bus_helper_id')) {
            $busHelper = BusHelper::find($request->bus_helper_id);
            $filterInfo['bus_helper'] = $busHelper ? $busHelper->bus_helper_name : 'N/A';
        }
        if ($request->filled('date_range')) {
            $filterInfo['date_range'] = ucfirst(str_replace('_', ' ', $request->date_range));
        }
        if ($request->filled('from_date')) {
            $filterInfo['from_date'] = Carbon::parse($request->from_date)->format('d M Y');
        }
        if ($request->filled('to_date')) {
            $filterInfo['to_date'] = Carbon::parse($request->to_date)->format('d M Y');
        }

        return view('content.report.reward-report-print-list', compact('rewards', 'totalAmount', 'filterInfo', 'request'));
    }

    public function pdf(Request $request)
    {
        $query = Reward::with(['bus', 'driver', 'busHelper', 'rewardType', 'user']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('reward_type_id')) {
            $query->where('reward_type_id', $request->reward_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->whereHas('bus', function ($busQuery) use ($request) {
                $busQuery->where('bus_sub_type_id', $request->bus_sub_type_id);
            });
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        if ($fromDate) {
            $query->whereDate('reward_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('reward_date', '<=', $toDate);
        }

        $rewards = $query->latest('reward_date')->get();
        $totalAmount = $rewards->sum('reward_amount');
        $appSetting = AppSetting::first();

        $pdf = Pdf::loadView('content.report.reward-report-pdf', compact('rewards', 'totalAmount', 'appSetting', 'request'));
        return $pdf->stream('reward-report.pdf');
    }

    public function excel(Request $request)
    {
        $query = Reward::with(['bus', 'driver', 'busHelper', 'rewardType', 'user']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('reward_type_id')) {
            $query->where('reward_type_id', $request->reward_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->whereHas('bus', function ($busQuery) use ($request) {
                $busQuery->where('bus_sub_type_id', $request->bus_sub_type_id);
            });
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        if ($fromDate) {
            $query->whereDate('reward_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('reward_date', '<=', $toDate);
        }

        $rewards = $query->latest('reward_date')->get();

        $fileName = "reward-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Date', 'Bus Sub Type', 'Bus', 'Driver', 'BusHelper', 'Reward Type', 'Reason', 'Amount', 'Remarks');

        $callback = function() use($rewards, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($rewards as $reward) {
                $row['Date'] = $reward->reward_date;
                $row['Bus Sub Type'] = $reward->bus && $reward->bus->busSubType ? $reward->bus->busSubType->sub_type_name : '';
                $row['Bus'] = $reward->bus ? $reward->bus->display_name : '';
                $row['Driver'] = $reward->driver ? $reward->driver->full_name : '';
                $row['BusHelper'] = $reward->busHelper ? $reward->busHelper->full_name : '';
                $row['Reward Type'] = $reward->rewardType ? $reward->rewardType->name : '';
                $row['Reason'] = $reward->reason;
                $row['Amount'] = $reward->reward_amount;
                $row['Remarks'] = $reward->remarks;

                fputcsv($file, array($row['Date'], $row['Bus Sub Type'], $row['Bus'], $row['Driver'], $row['BusHelper'], $row['Reward Type'], $row['Reason'], $row['Amount'], $row['Remarks']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
