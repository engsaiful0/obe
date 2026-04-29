<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\BusTrip;
use App\Models\Reward;
use App\Models\Punishment;
use App\Models\BusSubType;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BRTCBusMonthlyBillController extends Controller
{
    /**
     * Display the BRTC Bus Monthly Bill Report
     */
    public function index(Request $request)
    {
        // Get current month and year by default
        $currentYear = $request->input('year', date('Y'));
        $currentMonth = $request->input('month', date('m'));
        
        // Set date range for current month
        $fromDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $toDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        
        // Get selected bus
        $busId = $request->input('bus_id');
        
        // Get all BRTC buses for filter dropdown
        $buses = Bus::with(['busSubType'])
            ->whereHas('busSubType', function ($query) {
                $query->where('sub_type_name', 'BRTC Bus');
            })
            ->get();
        
        $dailyBills = [];
        
        if ($busId) {
            $bus = Bus::with(['busSubType'])->find($busId);
            
            if ($bus && $bus->busSubType && $bus->busSubType->sub_type_name === 'BRTC Bus') {
                $dailyBills = $this->calculateDailyBills($bus, $fromDate, $toDate);
            }
        }
        
        return view('content.report.brtc-bus-monthly-bill', compact(
            'dailyBills',
            'buses',
            'currentYear',
            'currentMonth',
            'fromDate',
            'toDate',
            'busId'
        ));
    }

    /**
     * Calculate daily bills for a BRTC bus
     */
    private function calculateDailyBills($bus, $fromDate, $toDate)
    {
        $dailyBills = [];
        $ratePerKm = $bus->rate_per_km ?? 0;
        $seatingCapacity = $bus->seating_capacity ?? 0;
        
        // Get all trips for the date range with stoppage relationships
        $trips = BusTrip::where('bus_id', $bus->id)
            ->whereBetween('trip_date', [$fromDate, $toDate])
            ->with(['startStoppage', 'endStoppage'])
            ->orderBy('trip_date')
            ->get();
        
        // Get all rewards for the date range
        $rewards = Reward::where('bus_id', $bus->id)
            ->whereBetween('reward_date', [$fromDate, $toDate])
            ->get()
            ->groupBy(function ($reward) {
                return $reward->reward_date->format('Y-m-d');
            });
        
        // Get all punishments for the date range
        $punishments = Punishment::where('bus_id', $bus->id)
            ->whereBetween('punishment_date', [$fromDate, $toDate])
            ->get()
            ->groupBy(function ($punishment) {
                return $punishment->punishment_date->format('Y-m-d');
            });
        
        // Group trips by date
        $tripsByDate = $trips->groupBy(function ($trip) {
            return $trip->trip_date->format('Y-m-d');
        });
        
        // Generate daily bills
        $currentDate = $fromDate->copy();
        $serial = 1;
        
        while ($currentDate <= $toDate) {
            $dateKey = $currentDate->format('Y-m-d');
            
            // Get trips for this date
            $dayTrips = $tripsByDate->get($dateKey, collect());
            $numberOfTrips = $dayTrips->count();
            
            // Calculate achieved distance from stoppages
            // Logic: 
            // - For In trips: Use start_stoppage.distance (e.g., In trip from Kulshi to IIUC = use Kulshi distance)
            // - For Out trips: Use end_stoppage.distance (e.g., Out trip from IIUC to 2No Gate = use 2No Gate distance)
            // Then sum all trip distances for the day
            // Example: In trip (Kulshi 15km to IIUC) = 15km (use Kulshi distance)
            //          Out trip (IIUC to 2No Gate 8km) = 8km (use 2No Gate distance)
            //          Total achieved distance = 15 + 8 = 23km
            $achievedDistance = $dayTrips->sum(function ($trip) {
                if ($trip->trip_type === 'in') {
                    // For In trips: use the start stoppage distance
                    return $trip->startStoppage->distance ?? 0;
                } else {
                    // For Out trips: use the end stoppage distance
                    return $trip->endStoppage->distance ?? 0;
                }
            });
            
            // Calculate daily rent
            $dailyRent = $achievedDistance * $seatingCapacity * $ratePerKm;
            
            // Calculate 15% VAT (Exclude)
            $vat = $dailyRent * 0.15;
            
            // Get rewards for this date
            $dayRewards = $rewards->get($dateKey, collect());
            $totalReward = $dayRewards->sum('reward_amount');
            
            // Get punishments for this date
            $dayPunishments = $punishments->get($dateKey, collect());
            $totalPunishment = $dayPunishments->sum('fine_amount');
            
            // Calculate daily total rent
            $dailyTotalRent = $dailyRent + $vat + $totalReward - $totalPunishment;
            
            // Get comments from trips (combine remarks)
            $comments = $dayTrips->pluck('remarks')->filter()->implode('; ');
            if (empty($comments)) {
                $comments = '-';
            }
            
            $dailyBills[] = [
                'serial' => $serial++,
                'date' => $currentDate->copy(),
                'number_of_trips' => $numberOfTrips,
                'achieved_distance' => $achievedDistance,
                'daily_rent' => $dailyRent,
                'vat' => $vat,
                'reward' => $totalReward,
                'punishment' => $totalPunishment,
                'daily_total_rent' => $dailyTotalRent,
                'comment' => $comments,
            ];
            
            $currentDate->addDay();
        }
        
        return $dailyBills;
    }

    /**
     * Print BRTC Bus Monthly Bill
     */
    public function printList(Request $request)
    {
        $currentYear = $request->input('year', date('Y'));
        $currentMonth = $request->input('month', date('m'));
        
        $fromDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $toDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        
        $busId = $request->input('bus_id');
        
        if (!$busId) {
            abort(400, 'Bus ID is required');
        }
        
        $bus = Bus::with(['busSubType'])->findOrFail($busId);
        
        if (!$bus->busSubType || $bus->busSubType->sub_type_name !== 'BRTC Bus') {
            abort(400, 'Selected bus is not a BRTC Bus');
        }
        
        $dailyBills = $this->calculateDailyBills($bus, $fromDate, $toDate);
        
        // Calculate totals
        $totals = [
            'total_trips' => collect($dailyBills)->sum('number_of_trips'),
            'total_distance' => collect($dailyBills)->sum('achieved_distance'),
            'total_daily_rent' => collect($dailyBills)->sum('daily_rent'),
            'total_vat' => collect($dailyBills)->sum('vat'),
            'total_reward' => collect($dailyBills)->sum('reward'),
            'total_punishment' => collect($dailyBills)->sum('punishment'),
            'total_daily_total_rent' => collect($dailyBills)->sum('daily_total_rent'),
        ];
        
        return view('content.report.brtc-bus-monthly-bill-print-list', compact(
            'bus',
            'dailyBills',
            'totals',
            'currentYear',
            'currentMonth',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * Generate PDF for BRTC Bus Monthly Bill
     */
    public function pdf(Request $request)
    {
        $currentYear = $request->input('year', date('Y'));
        $currentMonth = $request->input('month', date('m'));
        
        $fromDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $toDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        
        $busId = $request->input('bus_id');
        
        if (!$busId) {
            abort(400, 'Bus ID is required');
        }
        
        $bus = Bus::with(['busSubType'])->findOrFail($busId);
        
        if (!$bus->busSubType || $bus->busSubType->sub_type_name !== 'BRTC Bus') {
            abort(400, 'Selected bus is not a BRTC Bus');
        }
        
        $dailyBills = $this->calculateDailyBills($bus, $fromDate, $toDate);
        
        // Calculate totals
        $totals = [
            'total_trips' => collect($dailyBills)->sum('number_of_trips'),
            'total_distance' => collect($dailyBills)->sum('achieved_distance'),
            'total_daily_rent' => collect($dailyBills)->sum('daily_rent'),
            'total_vat' => collect($dailyBills)->sum('vat'),
            'total_reward' => collect($dailyBills)->sum('reward'),
            'total_punishment' => collect($dailyBills)->sum('punishment'),
            'total_daily_total_rent' => collect($dailyBills)->sum('daily_total_rent'),
        ];
        
        $appSetting = AppSetting::first();
        
        $pdf = Pdf::loadView('content.report.brtc-bus-monthly-bill-pdf', compact(
            'bus',
            'dailyBills',
            'totals',
            'currentYear',
            'currentMonth',
            'fromDate',
            'toDate',
            'appSetting'
        ));
        
        $pdf->setPaper('A4', 'landscape');
        $filename = 'brtc-bus-monthly-bill-' . $bus->bus_number . '-' . $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export BRTC Bus Monthly Bill to Excel
     */
    public function excel(Request $request)
    {
        $currentYear = $request->input('year', date('Y'));
        $currentMonth = $request->input('month', date('m'));
        
        $fromDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $toDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        
        $busId = $request->input('bus_id');
        
        if (!$busId) {
            abort(400, 'Bus ID is required');
        }
        
        $bus = Bus::with(['busSubType'])->findOrFail($busId);
        
        if (!$bus->busSubType || $bus->busSubType->sub_type_name !== 'BRTC Bus') {
            abort(400, 'Selected bus is not a BRTC Bus');
        }
        
        $dailyBills = $this->calculateDailyBills($bus, $fromDate, $toDate);
        
        $fileName = "brtc-bus-monthly-bill-{$bus->bus_number}-{$currentYear}-{$currentMonth}.csv";
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
            'Number of Trips',
            'Achieved Distance',
            'Daily Rent',
            '15% VAT (Exclude)',
            'Reward (Include)',
            'Punishment (Exclude)',
            'Daily Total Rent',
            'Comment'
        ];
        
        $callback = function() use ($dailyBills, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($dailyBills as $bill) {
                fputcsv($file, [
                    $bill['serial'],
                    $bill['date']->format('Y-m-d'),
                    $bill['number_of_trips'],
                    number_format($bill['achieved_distance'], 2),
                    number_format($bill['daily_rent'], 2),
                    number_format($bill['vat'], 2),
                    number_format($bill['reward'], 2),
                    number_format($bill['punishment'], 2),
                    number_format($bill['daily_total_rent'], 2),
                    $bill['comment']
                ]);
            }
            
            // Add totals row
            $totals = [
                'Total',
                '',
                collect($dailyBills)->sum('number_of_trips'),
                number_format(collect($dailyBills)->sum('achieved_distance'), 2),
                number_format(collect($dailyBills)->sum('daily_rent'), 2),
                number_format(collect($dailyBills)->sum('vat'), 2),
                number_format(collect($dailyBills)->sum('reward'), 2),
                number_format(collect($dailyBills)->sum('punishment'), 2),
                number_format(collect($dailyBills)->sum('daily_total_rent'), 2),
                ''
            ];
            fputcsv($file, $totals);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}

