<?php

namespace App\Http\Controllers;

use App\Models\Lubricant;
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

class LubricantController extends Controller
{
    /**
     * Helper method to get filtered lubricant records
     */
    private function getFilteredLubricants(Request $request)
    {
        $query = Lubricant::with(['bus.busSubType', 'concernEmployee', 'user', 'unit'])
            ->where('user_id', Auth::id());

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('lubricant_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('lubricant_date', '<=', $request->to_date);
        }

        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('concern_employee_id', $request->employee_id);
        }

        return $query->orderBy('lubricant_date', 'desc')
            ->orderBy('lubricant_time', 'desc')
            ->get();
    }

    /**
     * Display a listing of lubricant records
     */
    public function index(Request $request)
    {
        $query = Lubricant::with(['bus.busSubType', 'concernEmployee', 'user', 'unit'])
            ->where('user_id', Auth::id());

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('lubricant_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('lubricant_date', '<=', $request->to_date);
        }

        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('concern_employee_id', $request->employee_id);
        }

        $lubricants = $query->orderBy('lubricant_date', 'desc')
            ->orderBy('lubricant_time', 'desc')
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

        return view('content.lubricants.index', compact('lubricants', 'buses', 'employees'));
    }

    /**
     * Show the form for adding lubricant (multi-bus form)
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

        // Get units related to Lubricant for the current user (only unit_name)
        $units = Unit::where('user_id', Auth::id())
            ->where('related_to', 'Lubricant')
            ->select('id', 'unit_name')
            ->orderBy('unit_name')
            ->get();

        return view('content.lubricants.create', compact('buses', 'employees', 'units'));
    }

    /**
     * Store a single lubricant record (AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'lubricant_date' => 'required|date',
            'lubricant_time' => 'required|date_format:H:i',
            'concern_employee_id' => 'nullable|exists:employees,id',
            'lubricant_amount' => 'required|numeric|min:0',
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
            $lubricant = Lubricant::create([
                'bus_id' => $request->bus_id,
                'lubricant_date' => $request->lubricant_date,
                'lubricant_time' => $request->lubricant_time,
                'concern_employee_id' => $request->concern_employee_id ?: null,
                'lubricant_amount' => $request->lubricant_amount,
                'unit_id' => $request->unit_id,
                'comment' => $request->comment,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lubricant record saved successfully.',
                'lubricant' => $lubricant->load(['bus.busSubType', 'concernEmployee', 'unit'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save lubricant record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store multiple lubricant records (Save All)
     */
    public function storeAll(Request $request)
    {
        $request->validate([
            'lubricant_date' => 'required|date',
            'lubricant_time' => 'required|date_format:H:i',
            'concern_employee_id' => 'nullable|exists:employees,id',
            'lubricants' => 'required|array|min:1',
            'lubricants.*.bus_id' => 'required|exists:buses,id',
            'lubricants.*.lubricant_amount' => 'required|numeric|min:0',
            'lubricants.*.unit_id' => 'required|exists:units,id',
            'lubricants.*.comment' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $savedCount = 0;
            $errors = [];

            foreach ($request->lubricants as $index => $lubricantData) {
                // Skip if bus_id is empty
                if (empty($lubricantData['bus_id']) || empty($lubricantData['lubricant_amount'])) {
                    continue;
                }

                // Verify bus belongs to user and is own bus
                $bus = Bus::where('id', $lubricantData['bus_id'])
                    ->where('user_id', Auth::id())
                    ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
                    ->first();

                if (!$bus) {
                    $errors[] = "Row " . ($index + 1) . ": Invalid bus selected.";
                    continue;
                }

                Lubricant::create([
                    'bus_id' => $lubricantData['bus_id'],
                    'lubricant_date' => $request->lubricant_date,
                    'lubricant_time' => $request->lubricant_time,
                    'concern_employee_id' => $request->concern_employee_id ?: null,
                    'lubricant_amount' => $lubricantData['lubricant_amount'],
                    'unit_id' => $lubricantData['unit_id'],
                    'comment' => $lubricantData['comment'] ?? null,
                    'user_id' => Auth::id(),
                ]);

                $savedCount++;
            }

            if ($savedCount > 0) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => "Successfully saved {$savedCount} lubricant record(s).",
                    'saved_count' => $savedCount,
                    'errors' => $errors
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No valid lubricant records to save.',
                    'errors' => $errors
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save lubricant records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing a lubricant record
     */
    public function edit(Lubricant $lubricant)
    {
        // Verify ownership
        if ($lubricant->user_id !== Auth::id()) {
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

        // Get units related to Lubricant for the current user (only unit_name)
        $units = Unit::where('user_id', Auth::id())
            ->where('related_to', 'Lubricant')
            ->select('id', 'unit_name')
            ->orderBy('unit_name')
            ->get();

        return view('content.lubricants.edit', compact('lubricant', 'buses', 'employees', 'units'));
    }

    /**
     * Update a lubricant record
     */
    public function update(Request $request, Lubricant $lubricant)
    {
        // Verify ownership
        if ($lubricant->user_id !== Auth::id()) {
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
            'lubricant_date' => 'required|date',
            'lubricant_time' => 'required|date_format:H:i',
            'concern_employee_id' => 'nullable|exists:employees,id',
            'lubricant_amount' => 'required|numeric|min:0',
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
            $lubricant->update([
                'bus_id' => $request->bus_id,
                'lubricant_date' => $request->lubricant_date,
                'lubricant_time' => $request->lubricant_time,
                'concern_employee_id' => $request->concern_employee_id ?: null,
                'lubricant_amount' => $request->lubricant_amount,
                'unit_id' => $request->unit_id,
                'comment' => $request->comment,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lubricant record updated successfully.',
                    'lubricant' => $lubricant->load(['bus.busSubType', 'concernEmployee', 'unit'])
                ]);
            }

            return redirect()->route('lubricants.index')
                ->with('success', 'Lubricant record updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update lubricant record: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update lubricant record.']);
        }
    }

    /**
     * Delete a lubricant record
     */
    public function destroy(Request $request, Lubricant $lubricant)
    {
        // Verify ownership
        if ($lubricant->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        try {
            $lubricant->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lubricant record deleted successfully.'
                ]);
            }

            return redirect()->route('lubricants.index')
                ->with('success', 'Lubricant record deleted successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete lubricant record: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('lubricants.index')
                ->with('error', 'Failed to delete lubricant record.');
        }
    }

    /**
     * Display print view
     */
    public function print(Request $request)
    {
        $lubricants = $this->getFilteredLubricants($request);
        $appSettings = AppSetting::first();
        
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        return view('content.lubricants.print', compact('lubricants', 'appSettings', 'fromDate', 'toDate'));
    }

    /**
     * Generate PDF export
     */
    public function pdf(Request $request)
    {
        $lubricants = $this->getFilteredLubricants($request);
        $appSettings = AppSetting::first();
        
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        $pdf = Pdf::loadView('content.lubricants.pdf', compact('lubricants', 'appSettings', 'fromDate', 'toDate'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('lubricant-records-' . date('Y-m-d') . '.pdf');
    }
}
