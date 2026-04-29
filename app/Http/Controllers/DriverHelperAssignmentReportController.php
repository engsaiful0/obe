<?php

namespace App\Http\Controllers;

use App\Models\DriverHelperAssignment;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\BusHelper;
use App\Models\Status as StatusModel;
use App\Models\BusSubType;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DriverHelperAssignmentReportController extends Controller
{
    /**
     * Display the Driver Helper Assignment Report
     */
    public function index(Request $request)
    {
        $query = DriverHelperAssignment::with([
            'bus.busType',
            'bus.busSubType',
            'driver',
            'busHelper',
            'status',
            'user'
        ]);

        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by bus helper
        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        // Filter by status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by assignment date range
        if ($request->filled('from_date')) {
            $query->whereDate('assignment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('assignment_date', '<=', $request->to_date);
        }

        $assignments = $query->orderBy('assignment_date', 'desc')
            ->orderBy('bus_id')
            ->get();

        // Get filter options
        $buses = Bus::where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
            ->orderBy('bus_number')
            ->get();
        
        $drivers = Driver::orderBy('full_name')->get();
        $busHelpers = BusHelper::orderBy('bus_helper_name')->get();
        $statuses = StatusModel::where('related_to', 'driver-helper-assignment')->get();

        // Get app settings for header
        $appSettings = AppSetting::first();

        return view('content.report.driver-helper-assignment-report', compact(
            'assignments',
            'buses',
            'drivers',
            'busHelpers',
            'statuses',
            'appSettings',
            'request'
        ));
    }

    /**
     * Display print view
     */
    public function print(Request $request)
    {
        $query = DriverHelperAssignment::with([
            'bus.busType',
            'bus.busSubType',
            'driver',
            'busHelper',
            'status',
            'user'
        ]);

        // Apply same filters as index
        $this->applyFilters($query, $request);

        $assignments = $query->orderBy('assignment_date', 'desc')
            ->orderBy('bus_id')
            ->get();

        $appSettings = AppSetting::first();

        return view('content.report.driver-helper-assignment-report-print', compact(
            'assignments',
            'appSettings',
            'request'
        ));
    }

    /**
     * Generate PDF export
     */
    public function pdf(Request $request)
    {
        $query = DriverHelperAssignment::with([
            'bus.busType',
            'bus.busSubType',
            'driver',
            'busHelper',
            'status',
            'user'
        ]);

        // Apply same filters as index
        $this->applyFilters($query, $request);

        $assignments = $query->orderBy('assignment_date', 'desc')
            ->orderBy('bus_id')
            ->get();

        $appSettings = AppSetting::first();

        $pdf = Pdf::loadView('content.report.driver-helper-assignment-report-pdf', compact(
            'assignments',
            'appSettings',
            'request'
        ));

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('driver-helper-assignment-report-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Generate Excel export
     */
    public function excel(Request $request)
    {
        $query = DriverHelperAssignment::with([
            'bus.busType',
            'bus.busSubType',
            'driver',
            'busHelper',
            'status',
            'user'
        ]);

        // Apply same filters as index
        $this->applyFilters($query, $request);

        $assignments = $query->orderBy('assignment_date', 'desc')
            ->orderBy('bus_id')
            ->get();

        // Prepare data for Excel export
        $exportData = [];
        $exportData[] = ['Bus Number', 'Bus Type', 'Bus Sub Type', 'Driver Name', 'Driver Mobile', 'Helper Name', 'Helper Mobile', 'Assignment Date', 'Status', 'Comment', 'Assigned By'];

        foreach ($assignments as $assignment) {
            $exportData[] = [
                $assignment->bus->bus_number ?? 'N/A',
                $assignment->bus->busType->bus_type_name ?? 'N/A',
                $assignment->bus->busSubType->sub_type_name ?? 'N/A',
                $assignment->driver->full_name ?? 'N/A',
                $assignment->driver->contact_number ?? 'N/A',
                $assignment->busHelper->bus_helper_name ?? 'N/A',
                $assignment->busHelper->mobile ?? 'N/A',
                $assignment->assignment_date ? $assignment->assignment_date->format('Y-m-d') : 'N/A',
                $assignment->status->status_name ?? 'N/A',
                $assignment->notes ?? '',
                $assignment->user->name ?? 'N/A'
            ];
        }

        // Use simple CSV export
        $filename = 'driver-helper-assignment-report-' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');
            foreach ($exportData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, $request)
    {
        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by bus helper
        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        // Filter by status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by assignment date range
        if ($request->filled('from_date')) {
            $query->whereDate('assignment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('assignment_date', '<=', $request->to_date);
        }
    }
}

