<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusHelper;
use App\Models\Gender;
use App\Models\Status;
use App\Models\EmployeeType;
use App\Models\Bus;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BusHelperExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AppSetting;

class BusHelperReportController extends Controller
{
    public function index(Request $request)
    {
        $query = BusHelper::with([
            'gender',
            'maritalStatus',
            'religion',
            'employeeType',
            'status',
            'assignedBus'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get pagination per page
        $perPage = $request->get('per_page', 20);
        
        $busHelpers = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        // Get filter options
        $genders = Gender::all();
        $statuses = Status::where('related_to', 'bus-helper')->get();
        $employeeTypes = EmployeeType::all();
        $buses = Bus::select('id', 'bus_number', 'model_name')->get();
        
        // Calculate summary based on filtered results
        $summaryQuery = BusHelper::query();
        $this->applyFilters($summaryQuery, $request);
        $totalCount = $summaryQuery->count();
        $totalSalary = $summaryQuery->sum('gross_salary');
        $avgSalary = $summaryQuery->avg('gross_salary');

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.report.bus-helper-report-table', compact('busHelpers', 'totalCount', 'totalSalary', 'avgSalary'))->render(),
                'pagination' => $busHelpers->appends($request->query())->links()->toHtml(),
                'totalCount' => $totalCount,
                'totalSalary' => $totalSalary,
                'avgSalary' => $avgSalary
            ]);
        }

        return view('content.report.bus-helper-report', compact(
            'busHelpers',
            'genders',
            'statuses',
            'employeeTypes',
            'buses',
            'totalCount',
            'totalSalary',
            'avgSalary'
        ));
    }

    public function ajax(Request $request)
    {
        $query = BusHelper::with([
            'gender',
            'maritalStatus',
            'religion',
            'employeeType',
            'status',
            'assignedBus'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get pagination per page
        $perPage = $request->get('per_page', 20);
        
        $busHelpers = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        // Calculate summary based on filtered results
        $summaryQuery = BusHelper::query();
        $this->applyFilters($summaryQuery, $request);
        $totalCount = $summaryQuery->count();
        $totalSalary = $summaryQuery->sum('gross_salary');
        $avgSalary = $summaryQuery->avg('gross_salary');

        return response()->json([
            'success' => true,
            'html' => view('content.report.bus-helper-report-table', compact('busHelpers', 'totalCount', 'totalSalary', 'avgSalary'))->render(),
            'pagination' => $busHelpers->appends($request->query())->links()->toHtml(),
            'totalCount' => $totalCount,
            'totalSalary' => $totalSalary,
            'avgSalary' => $avgSalary
        ]);
    }

    public function pdf(Request $request)
    {
        $query = BusHelper::with([
            'gender',
            'maritalStatus',
            'religion',
            'employeeType',
            'status',
            'assignedBus'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        $busHelpers = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate summary
        $totalCount = $busHelpers->count();
        $totalSalary = $busHelpers->sum('gross_salary');
        $avgSalary = $busHelpers->avg('gross_salary');
        $appSetting = AppSetting::first();

        $pdf = Pdf::loadView('content.report.bus-helper-report-pdf', compact(
            'busHelpers',
            'totalCount',
            'totalSalary',
            'avgSalary',
            'appSetting',
            'request'
        ));
        
        return $pdf->stream('bus-helper-report-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function excel(Request $request)
    {
        $query = BusHelper::with([
            'gender',
            'maritalStatus',
            'religion',
            'employeeType',
            'status',
            'assignedBus'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        $busHelpers = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(new BusHelperExport($busHelpers), 'bus-helper-report-' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function print(Request $request)
    {
        $query = BusHelper::with([
            'gender',
            'maritalStatus',
            'religion',
            'employeeType',
            'status',
            'assignedBus'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        $busHelpers = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate summary
        $totalCount = $busHelpers->count();
        $totalSalary = $busHelpers->sum('gross_salary');
        $avgSalary = $busHelpers->avg('gross_salary');
        $appSetting = AppSetting::first();

        return view('content.report.bus-helper-report-print', compact(
            'busHelpers',
            'totalCount',
            'totalSalary',
            'avgSalary',
            'appSetting',
            'request'
        ));
    }

    private function applyFilters($query, $request)
    {
        // Search filter
        if ($request->filled('search')) {
            $searchValue = $request->search;
            $query->where(function ($q) use ($searchValue) {
                $q->where('bus_helper_name', 'like', "%{$searchValue}%")
                  ->orWhere('bus_helper_id', 'like', "%{$searchValue}%")
                  ->orWhere('mobile', 'like', "%{$searchValue}%")
                  ->orWhere('nid_number', 'like', "%{$searchValue}%")
                  ->orWhere('father_name', 'like', "%{$searchValue}%")
                  ->orWhereHas('gender', function ($genderQuery) use ($searchValue) {
                      $genderQuery->where('gender_name', 'like', "%{$searchValue}%");
                  })
                  ->orWhereHas('employeeType', function ($typeQuery) use ($searchValue) {
                      $typeQuery->where('employee_type_name', 'like', "%{$searchValue}%");
                  });
            });
        }

        // Gender filter
        if ($request->filled('gender_id')) {
            $query->where('gender_id', $request->gender_id);
        }

        // Status filter
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Employee type filter
        if ($request->filled('employee_type_id')) {
            $query->where('employee_type_id', $request->employee_type_id);
        }

        // Assigned bus filter
        if ($request->filled('assigned_bus_id')) {
            $query->where('assigned_bus_id', $request->assigned_bus_id);
        }

        // Experience filter
        if ($request->filled('experience_filter')) {
            switch ($request->experience_filter) {
                case 'beginner':
                    $query->where('years_of_experience', '<=', 1);
                    break;
                case 'intermediate':
                    $query->whereBetween('years_of_experience', [2, 3]);
                    break;
                case 'experienced':
                    $query->whereBetween('years_of_experience', [4, 5]);
                    break;
                case 'senior':
                    $query->where('years_of_experience', '>', 5);
                    break;
            }
        }

        // Salary range filter
        if ($request->filled('min_salary')) {
            $query->where('gross_salary', '>=', $request->min_salary);
        }
        if ($request->filled('max_salary')) {
            $query->where('gross_salary', '<=', $request->max_salary);
        }

        // Date range filter (for created_at)
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

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }
    }
}

