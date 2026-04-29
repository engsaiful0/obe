<?php

namespace App\Http\Controllers;

use App\Models\Fuel;
use App\Models\Bus;
use App\Models\BusSubType;
use App\Models\Employee;
use App\Models\Unit;
use App\Models\Status as StatusModel;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FuelController extends Controller
{
    /**
     * Helper method to get filtered fuel records
     */
    private function getFilteredFuels(Request $request)
    {
        $query = Fuel::with(['bus.busSubType', 'concernEmployee', 'user', 'unit'])
            ->where('user_id', Auth::id());

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('fuel_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('fuel_date', '<=', $request->to_date);
        }

        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('concern_employee_id', $request->employee_id);
        }

        return $query->orderBy('fuel_date', 'desc')
            ->orderBy('fuel_time', 'desc')
            ->get();
    }

    /**
     * Display a listing of fuel records
     */
    public function index(Request $request)
    {
        $query = Fuel::with(['bus.busSubType', 'concernEmployee', 'user', 'unit'])
            ->where('user_id', Auth::id());

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('fuel_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('fuel_date', '<=', $request->to_date);
        }

        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('concern_employee_id', $request->employee_id);
        }

        $fuels = $query->orderBy('fuel_date', 'desc')
            ->orderBy('fuel_time', 'desc')
            ->paginate(20);

        // Get own buses for filter
        $activeStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->where('user_id', Auth::id())
            ->first();

        $buses = collect();
        if ($activeStatus) {
            $buses = Bus::where('user_id', Auth::id())
                ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
                ->where('status_id', $activeStatus->id)
                ->orderBy('bus_number')
                ->get();
        }

        // Get employees for filter
        $employees = Employee::where('user_id', Auth::id())
            ->orderBy('employee_name')
            ->get();

        return view('content.fuels.index', compact('fuels', 'buses', 'employees'));
    }

    /**
     * Show the form for adding fuel (multi-bus form)
     */
    public function create()
    {
        // Get active status for buses
        $activeStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->where('user_id', Auth::id())
            ->first();

        // Get own buses only
        $buses = collect();
        if ($activeStatus) {
            $buses = Bus::where('user_id', Auth::id())
                ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
                ->where('status_id', $activeStatus->id)
                ->with(['busSubType', 'driver', 'busHelper'])
                ->orderBy('bus_number')
                ->get();
        }

        // Get employees for concern employee dropdown
        $employees = Employee::where('user_id', Auth::id())
            ->orderBy('employee_name')
            ->get();

        // Get units related to Fuel for the current user (only unit_name)
        $units = Unit::where('user_id', Auth::id())
            ->where('related_to', 'Fuel')
            ->select('id', 'unit_name')
            ->orderBy('unit_name')
            ->get();

        return view('content.fuels.create', compact('buses', 'employees', 'units'));
    }

    /**
     * Store a single fuel record (AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'fuel_date' => 'required|date',
            'fuel_time' => 'required|date_format:H:i',
            'concern_employee_id' => 'nullable|exists:employees,id',
            'fuel_amount' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Verify bus belongs to user and is own bus
        $bus = Bus::where('id', $request->bus_id)
            ->where('user_id', Auth::id())
            ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
            ->first();

        if (!$bus) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid bus selected.'
            ], 422);
        }

        try {
            $fuel = Fuel::create([
                'bus_id' => $request->bus_id,
                'fuel_date' => $request->fuel_date,
                'fuel_time' => $request->fuel_time,
                'concern_employee_id' => $request->concern_employee_id ?: null,
                'fuel_amount' => $request->fuel_amount,
                'unit_id' => $request->unit_id,
                'comment' => $request->comment,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fuel record saved successfully.',
                'fuel' => $fuel->load(['bus.busSubType', 'concernEmployee', 'unit'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save fuel record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store multiple fuel records (Save All)
     */
    public function storeAll(Request $request)
    {
        $request->validate([
            'fuel_date' => 'required|date',
            'fuel_time' => 'required|date_format:H:i',
            'concern_employee_id' => 'nullable|exists:employees,id',
            'fuels' => 'required|array|min:1',
            'fuels.*.bus_id' => 'required|exists:buses,id',
            'fuels.*.fuel_amount' => 'required|numeric|min:0',
            'fuels.*.unit_id' => 'required|exists:units,id',
            'fuels.*.comment' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $savedCount = 0;
            $errors = [];

            foreach ($request->fuels as $index => $fuelData) {
                // Skip if bus_id is empty
                if (empty($fuelData['bus_id']) || empty($fuelData['fuel_amount'])) {
                    continue;
                }

                // Verify bus belongs to user and is own bus
                $bus = Bus::where('id', $fuelData['bus_id'])
                    ->where('user_id', Auth::id())
                    ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
                    ->first();

                if (!$bus) {
                    $errors[] = "Row " . ($index + 1) . ": Invalid bus selected.";
                    continue;
                }

                Fuel::create([
                    'bus_id' => $fuelData['bus_id'],
                    'fuel_date' => $request->fuel_date,
                    'fuel_time' => $request->fuel_time,
                    'concern_employee_id' => $request->concern_employee_id ?: null,
                    'fuel_amount' => $fuelData['fuel_amount'],
                    'unit_id' => $fuelData['unit_id'],
                    'comment' => $fuelData['comment'] ?? null,
                    'user_id' => Auth::id(),
                ]);

                $savedCount++;
            }

            if ($savedCount > 0) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => "Successfully saved {$savedCount} fuel record(s).",
                    'saved_count' => $savedCount,
                    'errors' => $errors
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No valid fuel records to save.',
                    'errors' => $errors
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save fuel records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing a fuel record
     */
    public function edit(Fuel $fuel)
    {
        // Verify ownership
        if ($fuel->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        // Get own buses
        $activeStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->where('user_id', Auth::id())
            ->first();

        $buses = collect();
        if ($activeStatus) {
            $buses = Bus::where('user_id', Auth::id())
                ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
                ->where('status_id', $activeStatus->id)
                ->orderBy('bus_number')
                ->get();
        }

        // Get employees
        $employees = Employee::where('user_id', Auth::id())
            ->orderBy('employee_name')
            ->get();

        // Get units related to Fuel for the current user (only unit_name)
        $units = Unit::where('user_id', Auth::id())
            ->where('related_to', 'Fuel')
            ->select('id', 'unit_name')
            ->orderBy('unit_name')
            ->get();

        return view('content.fuels.edit', compact('fuel', 'buses', 'employees', 'units'));
    }

    /**
     * Update a fuel record
     */
    public function update(Request $request, Fuel $fuel)
    {
        // Verify ownership
        if ($fuel->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'fuel_date' => 'required|date',
            'fuel_time' => 'required|date_format:H:i',
            'concern_employee_id' => 'nullable|exists:employees,id',
            'fuel_amount' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Verify bus belongs to user and is own bus
        $bus = Bus::where('id', $request->bus_id)
            ->where('user_id', Auth::id())
            ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
            ->first();

        if (!$bus) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid bus selected.'
                ], 422);
            }
            return back()->withErrors(['bus_id' => 'Invalid bus selected.']);
        }

        try {
            $fuel->update([
                'bus_id' => $request->bus_id,
                'fuel_date' => $request->fuel_date,
                'fuel_time' => $request->fuel_time,
                'concern_employee_id' => $request->concern_employee_id ?: null,
                'fuel_amount' => $request->fuel_amount,
                'unit_id' => $request->unit_id,
                'comment' => $request->comment,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fuel record updated successfully.',
                    'fuel' => $fuel->load(['bus.busSubType', 'concernEmployee', 'unit'])
                ]);
            }

            return redirect()->route('fuels.index')
                ->with('success', 'Fuel record updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update fuel record: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update fuel record.']);
        }
    }

    /**
     * Delete a fuel record
     */
    public function destroy(Request $request, Fuel $fuel)
    {
        // Verify ownership
        if ($fuel->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        try {
            $fuel->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fuel record deleted successfully.'
                ]);
            }

            return redirect()->route('fuels.index')
                ->with('success', 'Fuel record deleted successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete fuel record: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('fuels.index')
                ->with('error', 'Failed to delete fuel record.');
        }
    }

    /**
     * Display print view
     */
    public function print(Request $request)
    {
        $fuels = $this->getFilteredFuels($request);
        $appSettings = AppSetting::first();
        
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        return view('content.fuels.print', compact('fuels', 'appSettings', 'fromDate', 'toDate'));
    }

    /**
     * Generate PDF export
     */
    public function pdf(Request $request)
    {
        $fuels = $this->getFilteredFuels($request);
        $appSettings = AppSetting::first();
        
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        $pdf = Pdf::loadView('content.fuels.pdf', compact('fuels', 'appSettings', 'fromDate', 'toDate'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('fuel-records-' . date('Y-m-d') . '.pdf');
    }
}

