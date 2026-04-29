<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\BusTrip;
use App\Models\BusSubType;
use App\Models\Status as StatusModel;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DaywiseTripReportController extends Controller
{
    /**
     * Display the Daywise Trip Report
     */
    public function index(Request $request)
    {
        // Get date range (default to current month)
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        
        // Get selected bus sub type and bus ID
        $busSubTypeId = $request->input('bus_sub_type_id');
        $busId = $request->input('bus_id');
        
        // Get all bus sub types for filter dropdown
        $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
        
        // Get buses based on selected sub type
        $buses = collect();
        if ($busSubTypeId) {
            $busActiveStatus = StatusModel::where('related_to', 'bus')
                ->where('status_name', 'like', '%active%')
                ->first();
            
            $buses = Bus::with(['busSubType', 'brand', 'yearOfManufacture'])
                ->where('bus_sub_type_id', $busSubTypeId)
                ->where('status_id', $busActiveStatus->id ?? null)
                ->orderBy('bus_number')
                ->get();
        }
        
        $trips = collect();
        
        if ($fromDate && $toDate && $busId) {
            // Get trips for the selected date range and bus
            $trips = BusTrip::where('bus_id', $busId)
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->with([
                    'bus.busSubType',
                    'bus.brand',
                    'startStoppage',
                    'endStoppage',
                    'driver',
                    'busHelper'
                ])
                ->orderBy('trip_date')
                ->orderBy('trip_number')
                ->get();
        }
        
        return view('content.report.daywise-trip-report', compact(
            'trips',
            'buses',
            'busSubTypes',
            'fromDate',
            'toDate',
            'busSubTypeId',
            'busId'
        ));
    }

    /**
     * Get buses by sub type (AJAX)
     */
    public function getBusesBySubType(Request $request)
    {
        $busSubTypeId = $request->input('bus_sub_type_id');
        
        if (!$busSubTypeId) {
            return response()->json([
                'success' => false,
                'message' => 'Bus sub type is required'
            ], 400);
        }

        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();

        $buses = Bus::with(['busSubType', 'brand', 'yearOfManufacture'])
            ->where('bus_sub_type_id', $busSubTypeId)
            ->where('status_id', $busActiveStatus->id ?? null)
            ->orderBy('bus_number')
            ->get();

        return response()->json([
            'success' => true,
            'buses' => $buses
        ]);
    }

    /**
     * Print Daywise Trip Report
     */
    public function printList(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $busId = $request->input('bus_id');
        
        if (!$fromDate || !$toDate || !$busId) {
            abort(400, 'Date range and Bus are required');
        }
        
        $bus = Bus::with(['busSubType', 'brand', 'yearOfManufacture'])->findOrFail($busId);
        
        $trips = BusTrip::where('bus_id', $busId)
            ->whereBetween('trip_date', [$fromDate, $toDate])
            ->with([
                'bus.busSubType',
                'startStoppage',
                'endStoppage',
                'driver',
                'busHelper'
            ])
            ->orderBy('trip_date')
            ->orderBy('trip_number')
            ->get();
        
        // Calculate totals
        $totalDistance = 0;
        $totalPassengers = 0;
        foreach ($trips as $trip) {
            if ($trip->trip_type === 'in') {
                $totalDistance += $trip->startStoppage->distance ?? 0;
            } else {
                $totalDistance += $trip->endStoppage->distance ?? 0;
            }
            $totalPassengers += $trip->passengers ?? 0;
        }
        
        return view('content.report.daywise-trip-report-print-list', compact(
            'trips',
            'bus',
            'fromDate',
            'toDate',
            'totalDistance',
            'totalPassengers'
        ));
    }

    /**
     * Generate PDF for Daywise Trip Report
     */
    public function pdf(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $busId = $request->input('bus_id');
        
        if (!$fromDate || !$toDate || !$busId) {
            abort(400, 'Date range and Bus are required');
        }
        
        $bus = Bus::with(['busSubType', 'brand', 'yearOfManufacture'])->findOrFail($busId);
        
        $trips = BusTrip::where('bus_id', $busId)
            ->whereBetween('trip_date', [$fromDate, $toDate])
            ->with([
                'bus.busSubType',
                'startStoppage',
                'endStoppage',
                'driver',
                'busHelper'
            ])
            ->orderBy('trip_date')
            ->orderBy('trip_number')
            ->get();
        
        $appSetting = AppSetting::first();
        
        $pdf = Pdf::loadView('content.report.daywise-trip-report-pdf', compact(
            'trips',
            'bus',
            'fromDate',
            'toDate',
            'appSetting'
        ));
        
        $pdf->setPaper('A4', 'landscape');
        $filename = 'daywise-trip-report-' . $bus->bus_number . '-' . $fromDate . '-to-' . $toDate . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export Daywise Trip Report to Excel
     */
    public function excel(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $busId = $request->input('bus_id');
        
        if (!$fromDate || !$toDate || !$busId) {
            abort(400, 'Date range and Bus are required');
        }
        
        $bus = Bus::with(['busSubType'])->findOrFail($busId);
        
        $trips = BusTrip::where('bus_id', $busId)
            ->whereBetween('trip_date', [$fromDate, $toDate])
            ->with([
                'startStoppage',
                'endStoppage',
                'driver',
                'busHelper'
            ])
            ->orderBy('trip_date')
            ->orderBy('trip_number')
            ->get();
        
        $fileName = "daywise-trip-report-{$bus->bus_number}-{$fromDate}-to-{$toDate}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            'Serial',
            'Date',
            'Trip Number',
            'Trip Type',
            'From Stoppage',
            'To Stoppage',
            'Distance (KM)',
            'In Time',
            'Out Time',
            'Passengers',
            'Driver',
            'Bus Helper',
            'Remarks'
        ];
        
        $callback = function() use ($trips, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            $serial = 1;
            foreach ($trips as $trip) {
                // Calculate distance from stoppages
                $distance = 0;
                if ($trip->trip_type === 'in') {
                    $distance = $trip->startStoppage->distance ?? 0;
                } else {
                    $distance = $trip->endStoppage->distance ?? 0;
                }
                
                fputcsv($file, [
                    $serial++,
                    $trip->trip_date->format('Y-m-d'),
                    $trip->trip_number ?? '',
                    strtoupper($trip->trip_type),
                    $trip->startStoppage->stoppage_name ?? 'N/A',
                    $trip->endStoppage->stoppage_name ?? 'N/A',
                    number_format($distance, 2),
                    $trip->in_time ? Carbon::parse($trip->in_time)->format('h:i A') : 'N/A',
                    $trip->out_time ? Carbon::parse($trip->out_time)->format('h:i A') : 'N/A',
                    $trip->passengers ?? 0,
                    $trip->driver->full_name ?? 'N/A',
                    $trip->busHelper->bus_helper_name ?? 'N/A',
                    $trip->remarks ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}

