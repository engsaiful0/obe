<?php

namespace App\Http\Controllers;

use App\Models\BusTrip;
use App\Models\Stoppage;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentOutReportController extends Controller
{
    /**
     * Helper method to process trips and group by stoppage and bus
     */
    private function processTrips($trips)
    {
        $reportData = [];
        
        // Group by stoppage name (normalized) and bus, sum passengers
        foreach ($trips as $trip) {
            if (!$trip->endStoppage || !$trip->bus) {
                continue;
            }
            
            // Normalize stoppage name (trim and lowercase for comparison)
            // For OUT trips, use end stoppage
            $stoppageName = trim($trip->endStoppage->stoppage_name);
            $stoppageKey = strtolower($stoppageName);
            $busId = $trip->bus_id;
            $busNumber = $trip->bus->bus_number;
            $busSubTypeName = $trip->busSubType ? $trip->busSubType->sub_type_name : ($trip->bus->busSubType ? $trip->bus->busSubType->sub_type_name : 'N/A');
            
            // Initialize stoppage if not exists
            if (!isset($reportData[$stoppageKey])) {
                $reportData[$stoppageKey] = [
                    'stoppage_name' => $stoppageName,
                    'buses' => []
                ];
            }
            
            // Initialize bus if not exists, or add to existing
            if (!isset($reportData[$stoppageKey]['buses'][$busId])) {
                $reportData[$stoppageKey]['buses'][$busId] = [
                    'bus_number' => $busNumber,
                    'bus_sub_type' => $busSubTypeName,
                    'total_students' => 0
                ];
            }
            
            // Sum passengers for this bus-stoppage combination
            $reportData[$stoppageKey]['buses'][$busId]['total_students'] += ($trip->passengers ?? 0);
        }
        
        // Convert buses array to indexed array and sort by bus number
        foreach ($reportData as $stoppageKey => $data) {
            $buses = array_values($data['buses']);
            // Sort buses by bus number
            usort($buses, function($a, $b) {
                return strnatcmp($a['bus_number'], $b['bus_number']);
            });
            $reportData[$stoppageKey]['buses'] = $buses;
        }
        
        // Sort by stoppage name
        uksort($reportData, function($a, $b) use ($reportData) {
            return strcasecmp($reportData[$a]['stoppage_name'], $reportData[$b]['stoppage_name']);
        });
        
        return $reportData;
    }
    
    /**
     * Display the Student OUT Report
     */
    public function index(Request $request)
    {
        // Get date range (default to current month)
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        
        // Get all stoppages
        $stoppages = Stoppage::orderBy('stoppage_name')->get();
        
        // Get report data - group by stoppage name and bus
        $reportData = [];
        
        if ($fromDate && $toDate) {
            // Get all OUT trips within the date range
            $trips = BusTrip::where('trip_type', 'out')
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->with(['endStoppage', 'bus.busSubType', 'busSubType'])
                ->get();
            
            $reportData = $this->processTrips($trips);
        }
        
        // Get app settings for header
        $appSettings = AppSetting::first();
        
        return view('content.report.student-out-report', compact(
            'reportData',
            'stoppages',
            'fromDate',
            'toDate',
            'appSettings'
        ));
    }
    
    /**
     * Display print view
     */
    public function print(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        
        // Get report data
        $reportData = [];
        
        if ($fromDate && $toDate) {
            // Get all OUT trips within the date range
            $trips = BusTrip::where('trip_type', 'out')
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->with(['endStoppage', 'bus.busSubType', 'busSubType'])
                ->get();
            
            $reportData = $this->processTrips($trips);
        }
        
        // Get app settings for header
        $appSettings = AppSetting::first();
        
        return view('content.report.student-out-report-print', compact(
            'reportData',
            'fromDate',
            'toDate',
            'appSettings'
        ));
    }
    
    /**
     * Generate PDF export
     */
    public function pdf(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        
        // Get report data
        $reportData = [];
        
        if ($fromDate && $toDate) {
            // Get all OUT trips within the date range
            $trips = BusTrip::where('trip_type', 'out')
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->with(['endStoppage', 'bus.busSubType', 'busSubType'])
                ->get();
            
            $reportData = $this->processTrips($trips);
        }
        
        // Get app settings for header
        $appSettings = AppSetting::first();
        
        $pdf = Pdf::loadView('content.report.student-out-report-pdf', compact(
            'reportData',
            'fromDate',
            'toDate',
            'appSettings'
        ));
        
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('student-out-report-' . $fromDate . '-to-' . $toDate . '.pdf');
    }
    
    /**
     * Generate Excel export
     */
    public function excel(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        
        // Get report data
        $reportData = [];
        
        if ($fromDate && $toDate) {
            // Get all OUT trips within the date range
            $trips = BusTrip::where('trip_type', 'out')
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->with(['endStoppage', 'bus.busSubType', 'busSubType'])
                ->get();
            
            $reportData = $this->processTrips($trips);
        }
        
        // Prepare data for Excel export
        $exportData = [];
        $exportData[] = ['End Stoppage Name', 'Bus Number', 'Bus Sub Type', 'No of Student'];
        
        $grandTotal = 0;
        foreach ($reportData as $stoppageData) {
            foreach ($stoppageData['buses'] as $busData) {
                $exportData[] = [
                    $stoppageData['stoppage_name'],
                    $busData['bus_number'],
                    $busData['bus_sub_type'],
                    $busData['total_students']
                ];
                $grandTotal += $busData['total_students'];
            }
        }
        
        $exportData[] = ['', '', 'Grand Total', $grandTotal];
        
        // Use simple CSV export
        $filename = 'student-out-report-' . $fromDate . '-to-' . $toDate . '.csv';
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
}


