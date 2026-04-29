<?php

namespace App\Http\Controllers;

use App\Models\BusTrip;
use App\Models\Driver;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DriverTripReportController extends Controller
{
    /**
     * Process trips and group by date
     */
    private function processTrips($trips)
    {
        $reportData = [];
        
        // Group trips by date
        $tripsByDate = $trips->groupBy(function($trip) {
            return $trip->trip_date->format('Y-m-d');
        });
        
        foreach ($tripsByDate as $date => $dayTrips) {
            // Get unique bus and helper for this day (assuming same bus and helper for all trips in a day)
            $firstTrip = $dayTrips->first();
            $busNumber = $firstTrip->bus->bus_number ?? 'N/A';
            $helperName = $firstTrip->busHelper ? $firstTrip->busHelper->bus_helper_name : ($firstTrip->alternateBusHelper ? $firstTrip->alternateBusHelper->bus_helper_name : 'N/A');
            
            // Sort trips by trip_number
            $sortedTrips = $dayTrips->sortBy('trip_number')->values();
            
            // Get day name
            $dayName = Carbon::parse($date)->format('l'); // Full day name (Monday, Tuesday, etc.)
            
            $reportData[] = [
                'date' => $date,
                'day' => $dayName,
                'total_trips' => $sortedTrips->count(),
                'bus_number' => $busNumber,
                'helper' => $helperName,
                'trips' => $sortedTrips->map(function($trip) {
                    $from = $trip->startStoppage->stoppage_name ?? 'N/A';
                    $to = $trip->endStoppage->stoppage_name ?? 'N/A';
                    $tripType = strtoupper($trip->trip_type);
                    $time = $trip->trip_type == 'in' ? ($trip->in_time ? Carbon::parse($trip->in_time)->format('H:i') : 'N/A') : ($trip->out_time ? Carbon::parse($trip->out_time)->format('H:i') : 'N/A');
                    
                    return [
                        'trip_number' => $trip->trip_number ?? 0,
                        'route' => $from . ' → ' . $to,
                        'trip_type' => $tripType,
                        'time' => $time,
                        'passengers' => $trip->passengers ?? 0
                    ];
                })->toArray()
            ];
        }
        
        // Sort by date
        usort($reportData, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        
        return $reportData;
    }
    
    /**
     * Get maximum number of trips in a single day
     */
    private function getMaxTripsPerDay($reportData)
    {
        $maxTrips = 0;
        foreach ($reportData as $dayData) {
            $maxTrips = max($maxTrips, count($dayData['trips']));
        }
        return $maxTrips;
    }
    
    /**
     * Display the Driver Trip Report
     */
    public function index(Request $request)
    {
        // Get date range (default to current month)
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        $driverId = $request->input('driver_id');
        
        // Get all drivers for filter dropdown
        $drivers = Driver::orderBy('full_name')->get();
        
        $reportData = [];
        $maxTripsPerDay = 0;
        $driver = null;
        
        if ($fromDate && $toDate && $driverId) {
            // Get driver
            $driver = Driver::find($driverId);
            
            if ($driver) {
                // Get all trips for the driver in the date range
                $trips = BusTrip::where('driver_id', $driverId)
                    ->orWhere('alternate_driver_id', $driverId)
                    ->whereBetween('trip_date', [$fromDate, $toDate])
                    ->with([
                        'bus',
                        'busHelper',
                        'alternateBusHelper',
                        'startStoppage',
                        'endStoppage'
                    ])
                    ->orderBy('trip_date')
                    ->orderBy('trip_number')
                    ->get();
                
                $reportData = $this->processTrips($trips);
                $maxTripsPerDay = $this->getMaxTripsPerDay($reportData);
            }
        }
        
        // Get app settings for header
        $appSettings = AppSetting::first();
        
        return view('content.report.driver-trip-report', compact(
            'reportData',
            'drivers',
            'fromDate',
            'toDate',
            'driverId',
            'driver',
            'maxTripsPerDay',
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
        $driverId = $request->input('driver_id');
        
        $reportData = [];
        $maxTripsPerDay = 0;
        $driver = null;
        
        if ($fromDate && $toDate && $driverId) {
            $driver = Driver::find($driverId);
            
            if ($driver) {
                $trips = BusTrip::where('driver_id', $driverId)
                    ->orWhere('alternate_driver_id', $driverId)
                    ->whereBetween('trip_date', [$fromDate, $toDate])
                    ->with([
                        'bus',
                        'busHelper',
                        'alternateBusHelper',
                        'startStoppage',
                        'endStoppage'
                    ])
                    ->orderBy('trip_date')
                    ->orderBy('trip_number')
                    ->get();
                
                $reportData = $this->processTrips($trips);
                $maxTripsPerDay = $this->getMaxTripsPerDay($reportData);
            }
        }
        
        $appSettings = AppSetting::first();
        
        return view('content.report.driver-trip-report-print', compact(
            'reportData',
            'fromDate',
            'toDate',
            'driver',
            'maxTripsPerDay',
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
        $driverId = $request->input('driver_id');
        
        $reportData = [];
        $maxTripsPerDay = 0;
        $driver = null;
        
        if ($fromDate && $toDate && $driverId) {
            $driver = Driver::find($driverId);
            
            if ($driver) {
                $trips = BusTrip::where('driver_id', $driverId)
                    ->orWhere('alternate_driver_id', $driverId)
                    ->whereBetween('trip_date', [$fromDate, $toDate])
                    ->with([
                        'bus',
                        'busHelper',
                        'alternateBusHelper',
                        'startStoppage',
                        'endStoppage'
                    ])
                    ->orderBy('trip_date')
                    ->orderBy('trip_number')
                    ->get();
                
                $reportData = $this->processTrips($trips);
                $maxTripsPerDay = $this->getMaxTripsPerDay($reportData);
            }
        }
        
        $appSettings = AppSetting::first();
        
        $pdf = Pdf::loadView('content.report.driver-trip-report-pdf', compact(
            'reportData',
            'fromDate',
            'toDate',
            'driver',
            'maxTripsPerDay',
            'appSettings'
        ));
        
        $pdf->setPaper('a4', 'landscape');
        
        $driverName = $driver ? str_replace(' ', '_', $driver->full_name) : 'driver';
        return $pdf->download('driver-trip-report-' . $driverName . '-' . $fromDate . '-to-' . $toDate . '.pdf');
    }
    
    /**
     * Generate Excel export
     */
    public function excel(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        $driverId = $request->input('driver_id');
        
        $reportData = [];
        $maxTripsPerDay = 0;
        $driver = null;
        
        if ($fromDate && $toDate && $driverId) {
            $driver = Driver::find($driverId);
            
            if ($driver) {
                $trips = BusTrip::where('driver_id', $driverId)
                    ->orWhere('alternate_driver_id', $driverId)
                    ->whereBetween('trip_date', [$fromDate, $toDate])
                    ->with([
                        'bus',
                        'busHelper',
                        'alternateBusHelper',
                        'startStoppage',
                        'endStoppage'
                    ])
                    ->orderBy('trip_date')
                    ->orderBy('trip_number')
                    ->get();
                
                $reportData = $this->processTrips($trips);
                $maxTripsPerDay = $this->getMaxTripsPerDay($reportData);
            }
        }
        
        // Prepare data for Excel export
        $exportData = [];
        
        // Create header row
        $header = ['Date', 'Day', 'Total Trip', 'Bus Number', 'Helper'];
        for ($i = 1; $i <= $maxTripsPerDay; $i++) {
            $header[] = $i . 'st Trip';
        }
        $exportData[] = $header;
        
        // Add data rows
        foreach ($reportData as $dayData) {
            $row = [
                Carbon::parse($dayData['date'])->format('d M, Y'),
                $dayData['day'],
                $dayData['total_trips'],
                $dayData['bus_number'],
                $dayData['helper']
            ];
            
            // Add trip information
            for ($i = 0; $i < $maxTripsPerDay; $i++) {
                if (isset($dayData['trips'][$i])) {
                    $trip = $dayData['trips'][$i];
                    $row[] = $trip['route'] . ' (' . $trip['trip_type'] . ' - ' . $trip['time'] . ')';
                } else {
                    $row[] = '';
                }
            }
            
            $exportData[] = $row;
        }
        
        // Use simple CSV export
        $driverName = $driver ? str_replace(' ', '_', $driver->full_name) : 'driver';
        $filename = 'driver-trip-report-' . $driverName . '-' . $fromDate . '-to-' . $toDate . '.csv';
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


