<?php

namespace App\Http\Controllers;

use App\Models\MonthlyBill;
use App\Models\Bus;
use App\Models\BusTrip;
use App\Models\Reward;
use App\Models\Punishment;
use App\Models\BusSubType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MonthlyBillController extends Controller
{
    /**
     * Display a listing of monthly bills
     */
    public function index(Request $request)
    {
        // Get current month and year by default
        $currentYear = $request->input('year', date('Y'));
        $currentMonth = $request->input('month', date('m'));
        
        // Set date range for current month
        $fromDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $toDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        
        // Get all buses with their sub types (Hired Bus and BRTC Bus)
        $buses = Bus::with(['busSubType'])
            ->whereHas('busSubType', function ($query) {
                $query->whereIn('sub_type_name', ['Hired Bus', 'BRTC Bus']);
            })
            ->get();
        
        // Generate monthly bills for each bus
        $bills = collect();
        
        foreach ($buses as $bus) {
            $bill = $this->calculateMonthlyBill($bus, $currentYear, $currentMonth, $fromDate, $toDate);
            if ($bill) {
                $bills->push($bill);
            }
        }
        
        // Apply filters
        if ($request->filled('bus_id')) {
            $bills = $bills->where('bus_id', $request->bus_id);
        }
        
        if ($request->filled('bus_type')) {
            $bills = $bills->where('bus_type', $request->bus_type);
        }
        
        // Convert to paginated collection
        $perPage = 15;
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedBills = $bills->slice($offset, $perPage)->values();
        
        // Create pagination object
        $bills = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedBills,
            $bills->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        // Get all buses for filter dropdown
        $allBuses = Bus::with('busSubType')
            ->whereHas('busSubType', function ($query) {
                $query->whereIn('sub_type_name', ['Hired Bus', 'BRTC Bus']);
            })
            ->get();
        
        // Get bus sub types for filter dropdown
        $busSubTypes = BusSubType::whereIn('sub_type_name', ['Hired Bus', 'BRTC Bus'])->get();
        
        // Get raw bills collection for summary calculations
        $rawBills = collect();
        foreach ($buses as $bus) {
            $bill = $this->calculateMonthlyBill($bus, $currentYear, $currentMonth, $fromDate, $toDate);
            if ($bill) {
                $rawBills->push($bill);
            }
        }
        
        // Apply same filters to raw bills for summary
        if ($request->filled('bus_id')) {
            $rawBills = $rawBills->where('bus_id', $request->bus_id);
        }
        
        if ($request->filled('bus_type')) {
            $rawBills = $rawBills->where('bus_type', $request->bus_type);
        }
        
        return view('content.monthly-bills.index', compact('bills', 'rawBills', 'allBuses', 'busSubTypes'));
    }

    /**
     * Calculate monthly bill for a bus
     */
    private function calculateMonthlyBill($bus, $year, $month, $fromDate, $toDate)
    {
        $subType = $bus->busSubType;
        if (!$subType) {
            return null;
        }

        $busType = null;
        $baseAmount = 0;
        $totalTrips = 0;
        $totalDistance = 0;
        $ratePerKm = null;
        $dailyRate = null;
        $fullDays = 0;
        $halfDays = 0;

        // Determine bus type and calculate base amount
        if ($subType->sub_type_name === 'Hired Bus') {
            $busType = 'hired';
            $dailyRate = $bus->fixed_price ?? 0;
            
            // For Hired buses: calculate based on trip completion
            $trips = BusTrip::forBus($bus->id)
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->get()
                ->groupBy('trip_date');

            $fullDays = 0; // Days with both In and Out trips (full rent)
            $halfDays = 0; // Days with only one trip (half rent)
            
            foreach ($trips as $date => $tripRecords) {
                $hasIn = $tripRecords->where('trip_type', 'in')->isNotEmpty();
                $hasOut = $tripRecords->where('trip_type', 'out')->isNotEmpty();
                
                if ($hasIn && $hasOut) {
                    // Both In and Out trips = full rent
                    $fullDays++;
                } elseif ($hasIn || $hasOut) {
                    // Only one trip = half rent
                    $halfDays++;
                }
            }
            
            $totalTrips = $fullDays + $halfDays;
            $baseAmount = ($fullDays * $dailyRate) + ($halfDays * ($dailyRate / 2));

        } elseif ($subType->sub_type_name === 'BRTC Bus') {
            $busType = 'brtc';
            $ratePerKm = $bus->rate_per_km ?? 0;
            
            // For BRTC buses: calculate by multiplying total distance by rate_per_km
            $totalDistance = BusTrip::forBus($bus->id)
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->sum('total_distance');
            
            $totalTrips = BusTrip::forBus($bus->id)
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->count();
            
            $baseAmount = $totalDistance * $ratePerKm;
        } else {
            return null; // Skip buses that are not Hired Bus or BRTC Bus
        }

        // Get total rewards using join for better performance
        $totalRewards = DB::table('rewards')
            ->where('bus_id', $bus->id)
            ->whereBetween('reward_date', [$fromDate, $toDate])
            ->sum('reward_amount');

        // Get total punishments using join for better performance
        $totalPunishments = DB::table('punishments')
            ->where('bus_id', $bus->id)
            ->whereBetween('punishment_date', [$fromDate, $toDate])
            ->sum('fine_amount');

        $finalAmount = $baseAmount + $totalRewards - $totalPunishments;

        // Return bill data as array (not model instance)
        return [
            'id' => $bus->id,
            'bus_id' => $bus->id,
            'bus' => $bus,
            'bill_month' => sprintf('%04d-%02d', $year, $month),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'bus_type' => $busType,
            'base_amount' => $baseAmount,
            'total_rewards' => $totalRewards,
            'total_punishments' => $totalPunishments,
            'final_amount' => $finalAmount,
            'total_trips' => $totalTrips,
            'total_distance' => $totalDistance,
            'rate_per_km' => $ratePerKm,
            'daily_rate' => $dailyRate,
            'full_days' => $busType === 'hired' ? $fullDays : 0,
            'half_days' => $busType === 'hired' ? $halfDays : 0,
            'formatted_bill_month' => Carbon::create($year, $month, 1)->format('F Y'),
            'bus_type_name' => $busType === 'hired' ? 'Hired Bus' : 'BRTC Bus',
        ];
    }

    /**
     * Show the form for creating a new monthly bill
     */
    public function create()
    {
        $buses = Bus::whereHas('busSubType', function ($q) {
            $q->whereIn('sub_type_name', ['Hired Bus', 'BRTC Hired Bus']);
        })->with('busSubType')->get();

        return view('content.monthly-bills.create', compact('buses'));
    }

    /**
     * Generate monthly bill for a specific bus and month
     */
    public function generate(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        try {
            DB::beginTransaction();

            $bus = Bus::with('busSubType')->find($request->bus_id);

            // Set default date range if not provided
            $fromDate = $request->from_date ? Carbon::parse($request->from_date) : null;
            $toDate = $request->to_date ? Carbon::parse($request->to_date) : null;

            $bill = MonthlyBill::generateMonthlyBill(
                $request->bus_id,
                $request->year,
                $request->month,
                $fromDate,
                $toDate
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Monthly bill generated successfully',
                'bill' => $bill->load(['bus.busSubType', 'user'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating bill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified monthly bill
     */
    public function show($busId)
    {
        // Get current month and year by default
        $currentYear = request()->input('year', date('Y'));
        $currentMonth = request()->input('month', date('m'));
        
        // Set date range for current month
        $fromDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $toDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        
        // Get the bus
        $bus = Bus::with(['busSubType'])
            ->whereHas('busSubType', function ($query) {
                $query->whereIn('sub_type_name', ['Hired Bus', 'BRTC Bus']);
            })
            ->findOrFail($busId);
        
        // Generate the monthly bill
        $monthlyBill = $this->calculateMonthlyBill($bus, $currentYear, $currentMonth, $fromDate, $toDate);
        
        if (!$monthlyBill) {
            abort(404, 'Monthly bill not found for this bus');
        }

        // Get detailed breakdown
        $trips = BusTrip::where('bus_id', $busId)
            ->whereBetween('trip_date', [$fromDate, $toDate])
            ->with(['startStoppage', 'endStoppage', 'driver', 'busHelper'])
            ->orderBy('trip_date')
            ->get();

        $rewards = Reward::where('bus_id', $busId)
            ->whereBetween('reward_date', [$fromDate, $toDate])
            ->with(['rewardType'])
            ->get();

        $punishments = Punishment::where('bus_id', $busId)
            ->whereBetween('punishment_date', [$fromDate, $toDate])
            ->with(['punishmentType', 'violationType'])
            ->get();

        return view('content.monthly-bills.show', compact('monthlyBill', 'trips', 'rewards', 'punishments'));
    }

    /**
     * Update the status of a monthly bill
     */
    public function updateStatus(Request $request, MonthlyBill $monthlyBill)
    {
        $request->validate([
            'status' => 'required|in:draft,generated,approved,paid'
        ]);

        $monthlyBill->update([
            'status' => $request->status,
            'remarks' => $request->remarks
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bill status updated successfully'
        ]);
    }

    /**
     * Generate bills for all buses for a specific month
     */
    public function generateAll(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            DB::beginTransaction();

            $buses = Bus::whereHas('busSubType', function ($q) {
                $q->whereIn('sub_type_name', ['Hired Bus', 'BRTC Hired Bus']);
            })->get();

            $generatedBills = [];
            $errors = [];

            foreach ($buses as $bus) {
                try {
                    $bill = MonthlyBill::generateMonthlyBill(
                        $bus->id,
                        $request->year,
                        $request->month
                    );
                    $generatedBills[] = $bill;
                } catch (\Exception $e) {
                    $errors[] = "Bus {$bus->registration_number}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bills generated for ' . count($generatedBills) . ' buses',
                'generated_count' => count($generatedBills),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating bills: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly bill summary for dashboard
     */
    public function getSummary(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        $summary = MonthlyBill::where('bill_month', sprintf('%04d-%02d', $year, $month))
            ->selectRaw('
                COUNT(*) as total_bills,
                SUM(final_amount) as total_amount,
                SUM(CASE WHEN status = "generated" THEN 1 ELSE 0 END) as generated_count,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count,
                SUM(total_rewards) as total_rewards,
                SUM(total_punishments) as total_punishments
            ')
            ->first();

        return response()->json($summary);
    }

    /**
     * Print monthly bills list
     */
    public function printList(Request $request)
    {
        // Get date range from request or use current month
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = Carbon::parse($request->from_date);
            $toDate = Carbon::parse($request->to_date);
            $currentYear = $fromDate->year;
            $currentMonth = $fromDate->month;
        } else {
            // Get current month and year by default
            $currentYear = $request->input('year', date('Y'));
            $currentMonth = $request->input('month', date('m'));
            
            // Set date range for current month
            $fromDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $toDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        }
        
        // Get all buses with their sub types (Hired Bus and BRTC Bus)
        $buses = Bus::with(['busSubType'])
            ->whereHas('busSubType', function ($query) {
                $query->whereIn('sub_type_name', ['Hired Bus', 'BRTC Bus']);
            })
            ->get();
        
        // Generate monthly bills for each bus
        $bills = collect();
        
        foreach ($buses as $bus) {
            $bill = $this->calculateMonthlyBill($bus, $currentYear, $currentMonth, $fromDate, $toDate);
            if ($bill) {
                $bills->push($bill);
            }
        }
        
        // Apply filters
        if ($request->filled('bus_id')) {
            $bills = $bills->where('bus_id', $request->bus_id);
        }
        
        if ($request->filled('bus_type')) {
            $bills = $bills->where('bus_type', $request->bus_type);
        }

        // Get all buses for filter display
        $allBuses = Bus::with('busSubType')
            ->whereHas('busSubType', function ($query) {
                $query->whereIn('sub_type_name', ['Hired Bus', 'BRTC Bus']);
            })
            ->get();

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('bus_id')) {
            $bus = Bus::find($request->bus_id);
            $filterInfo['bus'] = $bus ? $bus->bus_number : 'N/A';
        }
        if ($request->filled('bus_type')) {
            $filterInfo['bus_type'] = $request->bus_type == 'hired' ? 'Hired Bus' : 'BRTC Bus';
        }
        if ($request->filled('from_date')) {
            $filterInfo['from_date'] = Carbon::parse($request->from_date)->format('d M Y');
        }
        if ($request->filled('to_date')) {
            $filterInfo['to_date'] = Carbon::parse($request->to_date)->format('d M Y');
        }

        return view('content.monthly-bills.print-list', compact('bills', 'filterInfo', 'currentYear', 'currentMonth'));
    }

    /**
     * Export monthly bills to PDF
     */
    public function exportPdf(Request $request)
    {
        // Get date range from request or use current month
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = Carbon::parse($request->from_date);
            $toDate = Carbon::parse($request->to_date);
            $currentYear = $fromDate->year;
            $currentMonth = $fromDate->month;
        } else {
            // Get current month and year by default
            $currentYear = $request->input('year', date('Y'));
            $currentMonth = $request->input('month', date('m'));
            
            // Set date range for current month
            $fromDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $toDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        }
        
        // Get all buses with their sub types (Hired Bus and BRTC Bus)
        $buses = Bus::with(['busSubType'])
            ->whereHas('busSubType', function ($query) {
                $query->whereIn('sub_type_name', ['Hired Bus', 'BRTC Bus']);
            })
            ->get();
        
        // Generate monthly bills for each bus
        $bills = collect();
        
        foreach ($buses as $bus) {
            $bill = $this->calculateMonthlyBill($bus, $currentYear, $currentMonth, $fromDate, $toDate);
            if ($bill) {
                $bills->push($bill);
            }
        }
        
        // Apply filters
        if ($request->filled('bus_id')) {
            $bills = $bills->where('bus_id', $request->bus_id);
        }
        
        if ($request->filled('bus_type')) {
            $bills = $bills->where('bus_type', $request->bus_type);
        }

        // Calculate summary
        $totalBuses = $bills->count();
        $hiredBuses = $bills->where('bus_type', 'hired');
        $brtcBuses = $bills->where('bus_type', 'brtc');
        $totalHiredAmount = $hiredBuses->sum('final_amount');
        $totalBrtcAmount = $brtcBuses->sum('final_amount');
        $grandTotal = $bills->sum('final_amount');

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('bus_id')) {
            $bus = Bus::find($request->bus_id);
            $filterInfo['bus'] = $bus ? $bus->bus_number : 'N/A';
        }
        if ($request->filled('bus_type')) {
            $filterInfo['bus_type'] = $request->bus_type == 'hired' ? 'Hired Bus' : 'BRTC Bus';
        }
        if ($request->filled('from_date')) {
            $filterInfo['from_date'] = Carbon::parse($request->from_date)->format('d M Y');
        }
        if ($request->filled('to_date')) {
            $filterInfo['to_date'] = Carbon::parse($request->to_date)->format('d M Y');
        }

        $pdf = Pdf::loadView('content.monthly-bills.pdf', compact('bills', 'filterInfo', 'currentYear', 'currentMonth', 'totalBuses', 'hiredBuses', 'brtcBuses', 'totalHiredAmount', 'totalBrtcAmount', 'grandTotal'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('monthly-bills-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Export monthly bills to PDF
     */
    public function export(Request $request)
    {
        $query = MonthlyBill::with(['bus.busSubType', 'user']);

        // Apply same filters as index
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }
        if ($request->filled('bus_type')) {
            $query->where('bus_type', $request->bus_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->where('from_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('to_date', '<=', $request->to_date);
        }

        $bills = $query->orderBy('created_at', 'desc')->get();

        // This would typically use a PDF library like DomPDF
        // For now, return JSON response
        return response()->json([
            'success' => true,
            'bills' => $bills,
            'exported_at' => now()
        ]);
    }
}
