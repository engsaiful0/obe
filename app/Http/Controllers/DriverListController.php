<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\DriverType;
use App\Models\Status;
use App\Models\LicenseType;
use App\Models\ExperienceYear;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AppSetting;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Bus;

class DriverListController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with([
            'driverType',
            'status',
            'licenseType',
            'experienceYear',
            'educationalQualification'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get pagination per page
        $perPage = $request->get('per_page', 20);
        
        $drivers = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        // Get filter options
        $driverTypes = DriverType::all();
        $statuses = Status::where('related_to', 'driver')->get();
        $licenseTypes = LicenseType::all();
        $experienceOptions = ExperienceYear::all();
        
        // Calculate summary based on filtered results
        $summaryQuery = Driver::query();
        $this->applyFilters($summaryQuery, $request);
        $totalCount = $summaryQuery->count();
        $totalSalary = $summaryQuery->sum('gross_salary');
        $avgSalary = $summaryQuery->avg('gross_salary');

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.report.driver-list-table', compact('drivers', 'totalCount', 'totalSalary', 'avgSalary'))->render(),
                'pagination' => $drivers->appends($request->query())->links()->toHtml(),
                'totalCount' => $totalCount,
                'totalSalary' => $totalSalary,
                'avgSalary' => $avgSalary
            ]);
        }

        return view('content.report.driver-list', compact(
            'drivers',
            'driverTypes',
            'statuses',
            'licenseTypes',
            'experienceOptions',
            'totalCount',
            'totalSalary',
            'avgSalary'
        ));
    }

    public function ajax(Request $request)
    {
        $query = Driver::with([
            'driverType',
            'status',
            'licenseType',
            'experienceYear',
            'educationalQualification'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get pagination per page
        $perPage = $request->get('per_page', 20);
        
        $drivers = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        // Calculate summary based on filtered results
        $summaryQuery = Driver::query();
        $this->applyFilters($summaryQuery, $request);
        $totalCount = $summaryQuery->count();
        $totalSalary = $summaryQuery->sum('gross_salary');
        $avgSalary = $summaryQuery->avg('gross_salary');

        return response()->json([
            'success' => true,
            'html' => view('content.report.driver-list-table', compact('drivers', 'totalCount', 'totalSalary', 'avgSalary'))->render(),
            'pagination' => $drivers->appends($request->query())->links()->toHtml(),
            'totalCount' => $totalCount,
            'totalSalary' => $totalSalary,
            'avgSalary' => $avgSalary
        ]);
    }

    public function pdf(Request $request)
    {
        $query = Driver::with([
            'driverType',
            'status',
            'licenseType',
            'experienceYear',
            'educationalQualification'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        $drivers = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate summary
        $totalCount = $drivers->count();
        $totalSalary = $drivers->sum('gross_salary');
        $avgSalary = $drivers->avg('gross_salary');
        $appSetting = AppSetting::first();

        $pdf = Pdf::loadView('content.report.driver-list-pdf', compact(
            'drivers',
            'totalCount',
            'totalSalary',
            'avgSalary',
            'appSetting',
            'request'
        ));
        
        return $pdf->stream('driver-list-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function print(Request $request)
    {
        $query = Driver::with([
            'driverType',
            'status',
            'licenseType',
            'experienceYear',
            'educationalQualification'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        $drivers = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate summary
        $totalCount = $drivers->count();
        $totalSalary = $drivers->sum('gross_salary');
        $avgSalary = $drivers->avg('gross_salary');
        $appSetting = AppSetting::first();

        return view('content.report.driver-list-print', compact(
            'drivers',
            'totalCount',
            'totalSalary',
            'avgSalary',
            'appSetting',
            'request'
        ));
    }

    public function excel(Request $request)
    {
        // Placeholder for excel export if needed in future
        // return Excel::download(new DriverExport($drivers), 'driver-list-' . date('Y-m-d_H-i-s') . '.xlsx');
        return redirect()->back();
    }

    private function applyFilters($query, $request)
    {
        // Search filter
        if ($request->filled('search')) {
            $searchValue = $request->search;
            $query->where(function ($q) use ($searchValue) {
                $q->where('full_name', 'like', "%{$searchValue}%")
                  ->orWhere('driver_unique_id', 'like', "%{$searchValue}%")
                  ->orWhere('contact_number', 'like', "%{$searchValue}%")
                  ->orWhere('national_id_passport', 'like', "%{$searchValue}%")
                  ->orWhere('license_number', 'like', "%{$searchValue}%");
            });
        }

        // Driver Type filter
        if ($request->filled('driver_type_id')) {
            $query->where('driver_type_id', $request->driver_type_id);
        }

        // Status filter
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // License Type filter
        if ($request->filled('license_type_id')) {
            $query->where('license_type_id', $request->license_type_id);
        }

        // Experience filter
        if ($request->filled('experience_year_id')) {
             $query->where('experience_year_id', $request->experience_year_id);
        }

        // Salary range filter
        if ($request->filled('min_salary')) {
            $query->where('gross_salary', '>=', $request->min_salary);
        }
        if ($request->filled('max_salary')) {
            $query->where('gross_salary', '<=', $request->max_salary);
        }

        // Date range filter (for created_at or joining_date)
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
            $query->whereDate('joining_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('joining_date', '<=', $toDate);
        }
    }
}
