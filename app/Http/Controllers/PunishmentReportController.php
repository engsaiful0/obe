<?php

namespace App\Http\Controllers;

use App\Models\Punishment;
use App\Models\PunishmentType;
use App\Models\ViolationType;
use App\Models\Bus;
use App\Models\BusSubType;
use App\Models\Driver;
use App\Models\BusHelper;
use App\Models\Employee;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PunishmentReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Punishment::with(['bus', 'driver', 'busHelper', 'punishmentType', 'violationType', 'witnessEmployee', 'user']);

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

        if ($request->filled('punishment_type_id')) {
            $query->where('punishment_type_id', $request->punishment_type_id);
        }

        if ($request->filled('violation_type_id')) {
            $query->where('violation_type_id', $request->violation_type_id);
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($fromDate) {
            $query->whereDate('punishment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('punishment_date', '<=', $toDate);
        }

        $totalFineAmount = $query->sum('fine_amount');
        $totalSuspensionDays = $query->sum('suspension_days');
        $punishments = $query->latest('punishment_date')->paginate($perPage);
        
        $punishmentTypes = PunishmentType::all();
        $violationTypes = ViolationType::all();
        $buses = Bus::with(['brand', 'yearOfManufacture'])->get();
        $busSubTypes = BusSubType::all();
        $drivers = Driver::all();
        $busHelpers = BusHelper::all();
        $employees = Employee::all();

        return view('content.report.punishment-report', compact(
            'punishments', 
            'punishmentTypes', 
            'violationTypes',
            'buses', 
            'busSubTypes',
            'drivers', 
            'busHelpers', 
            'employees',
            'totalFineAmount',
            'totalSuspensionDays',
            'perPage'
        ));
    }

    public function ajax(Request $request)
    {
        $query = Punishment::with(['bus', 'driver', 'busHelper', 'punishmentType', 'violationType', 'witnessEmployee', 'user']);

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

        if ($request->filled('punishment_type_id')) {
            $query->where('punishment_type_id', $request->punishment_type_id);
        }

        if ($request->filled('violation_type_id')) {
            $query->where('violation_type_id', $request->violation_type_id);
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($fromDate) {
            $query->whereDate('punishment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('punishment_date', '<=', $toDate);
        }

        $totalFineAmount = $query->sum('fine_amount');
        $totalSuspensionDays = $query->sum('suspension_days');
        $punishments = $query->latest('punishment_date')->paginate($perPage);

        return response()->json([
            'punishments' => $punishments,
            'totalFineAmount' => $totalFineAmount,
            'totalSuspensionDays' => $totalSuspensionDays,
            'html' => view('content.report.punishment-report-table', compact('punishments', 'totalFineAmount', 'totalSuspensionDays'))->render()
        ]);
    }

    public function printList(Request $request)
    {
        $query = Punishment::with(['bus', 'driver', 'busHelper', 'punishmentType', 'violationType', 'witnessEmployee', 'user']);

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

        if ($request->filled('punishment_type_id')) {
            $query->where('punishment_type_id', $request->punishment_type_id);
        }

        if ($request->filled('violation_type_id')) {
            $query->where('violation_type_id', $request->violation_type_id);
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($fromDate) {
            $query->whereDate('punishment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('punishment_date', '<=', $toDate);
        }

        // Get all punishments (no pagination for print)
        $punishments = $query->latest('punishment_date')->get();
        $totalFineAmount = $punishments->sum('fine_amount');
        $totalSuspensionDays = $punishments->sum('suspension_days');

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('punishment_type_id')) {
            $punishmentType = PunishmentType::find($request->punishment_type_id);
            $filterInfo['punishment_type'] = $punishmentType ? $punishmentType->name : 'N/A';
        }
        if ($request->filled('violation_type_id')) {
            $violationType = ViolationType::find($request->violation_type_id);
            $filterInfo['violation_type'] = $violationType ? $violationType->name : 'N/A';
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
        if ($request->filled('status')) {
            $filterInfo['status'] = ucfirst($request->status);
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

        return view('content.report.punishment-report-print-list', compact('punishments', 'totalFineAmount', 'totalSuspensionDays', 'filterInfo', 'request'));
    }

    public function pdf(Request $request)
    {
        $query = Punishment::with(['bus', 'driver', 'busHelper', 'punishmentType', 'violationType', 'witnessEmployee', 'user']);

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

        if ($request->filled('punishment_type_id')) {
            $query->where('punishment_type_id', $request->punishment_type_id);
        }

        if ($request->filled('violation_type_id')) {
            $query->where('violation_type_id', $request->violation_type_id);
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($fromDate) {
            $query->whereDate('punishment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('punishment_date', '<=', $toDate);
        }

        $punishments = $query->latest('punishment_date')->get();
        $totalFineAmount = $punishments->sum('fine_amount');
        $totalSuspensionDays = $punishments->sum('suspension_days');
        $appSetting = AppSetting::first();

        $pdf = Pdf::loadView('content.report.punishment-report-pdf', compact('punishments', 'totalFineAmount', 'totalSuspensionDays', 'appSetting', 'request'));
        return $pdf->stream('punishment-report.pdf');
    }

    public function excel(Request $request)
    {
        $query = Punishment::with(['bus', 'driver', 'busHelper', 'punishmentType', 'violationType', 'witnessEmployee', 'user']);

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

        if ($request->filled('punishment_type_id')) {
            $query->where('punishment_type_id', $request->punishment_type_id);
        }

        if ($request->filled('violation_type_id')) {
            $query->where('violation_type_id', $request->violation_type_id);
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($fromDate) {
            $query->whereDate('punishment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('punishment_date', '<=', $toDate);
        }

        $punishments = $query->latest('punishment_date')->get();

        $fileName = "punishment-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Date', 'Bus Sub Type', 'Bus', 'Driver', 'Bus Helper', 'Punishment Type', 'Violation Type', 'Description', 'Fine Amount', 'Suspension Days', 'Status', 'Witness', 'Remarks');

        $callback = function() use($punishments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($punishments as $punishment) {
                $row['Date'] = $punishment->punishment_date;
                $row['Bus Sub Type'] = $punishment->bus && $punishment->bus->busSubType ? $punishment->bus->busSubType->sub_type_name : '';
                $row['Bus'] = $punishment->bus ? $punishment->bus->display_name : '';
                $row['Driver'] = $punishment->driver ? $punishment->driver->full_name : '';
                $row['Bus Helper'] = $punishment->busHelper ? $punishment->busHelper->bus_helper_name : '';
                $row['Punishment Type'] = $punishment->punishmentType ? $punishment->punishmentType->name : '';
                $row['Violation Type'] = $punishment->violationType ? $punishment->violationType->name : '';
                $row['Description'] = $punishment->description;
                $row['Fine Amount'] = $punishment->fine_amount;
                $row['Suspension Days'] = $punishment->suspension_days;
                $row['Status'] = ucfirst($punishment->status);
                $row['Witness'] = $punishment->witnessEmployee ? $punishment->witnessEmployee->employee_name : '';
                $row['Remarks'] = $punishment->remarks;

                fputcsv($file, array($row['Date'], $row['Bus Sub Type'], $row['Bus'], $row['Driver'], $row['Bus Helper'], $row['Punishment Type'], $row['Violation Type'], $row['Description'], $row['Fine Amount'], $row['Suspension Days'], $row['Status'], $row['Witness'], $row['Remarks']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
