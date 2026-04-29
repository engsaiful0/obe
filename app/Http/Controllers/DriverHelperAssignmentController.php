<?php

namespace App\Http\Controllers;

use App\Models\DriverHelperAssignment;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\BusHelper;
use App\Models\Status as StatusModel;
use App\Models\BusSubType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DriverHelperAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
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

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bus', function ($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('bus_number', 'like', "%{$search}%");
            })
            ->orWhereHas('driver', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('driver_unique_id', 'like', "%{$search}%");
            })
            ->orWhereHas('busHelper', function ($q) use ($search) {
                $q->where('bus_helper_name', 'like', "%{$search}%")
                  ->orWhere('bus_helper_id', 'like', "%{$search}%");
            });
        }

        $assignments = $query->orderBy('assignment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get data for filters
        $buses = Bus::where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
            ->with('busType', 'busSubType')
            ->get();
        $drivers = Driver::all();
        $busHelpers = BusHelper::all();
        $statuses = StatusModel::where('related_to', 'driver-helper-assignment')->get();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.driver-helper-assignments.partials.table', compact('assignments'))->render(),
                'pagination' => $assignments->links()->toHtml()
            ]);
        }

        return view('content.driver-helper-assignments.index', compact(
            'assignments',
            'buses',
            'drivers',
            'busHelpers',
            'statuses'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buses = Bus::where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
            ->with('busType', 'busSubType')
            ->get();
        $drivers = Driver::all();
        $busHelpers = BusHelper::all();
        $statuses = StatusModel::where('related_to', 'driver-helper-assignment')->get();

        return view('content.driver-helper-assignments.create', compact(
            'buses',
            'drivers',
            'busHelpers',
            'statuses'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_id' => [
                'required',
                'exists:buses,id',
                Rule::unique('driver_helper_assignments', 'bus_id')
            ],
            'driver_id' => 'required|exists:drivers,id',
            'bus_helper_id' => 'required|exists:bus_helpers,id',
            'status_id' => 'required|exists:statuses,id',
            'assignment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify bus is an own bus
        $bus = Bus::findOrFail($validated['bus_id']);
        if ($bus->bus_sub_type_id != BusSubType::OWN_BUS_SUB_TYPE_ID) {
            return response()->json([
                'success' => false,
                'message' => 'Only own buses can have driver and helper assignments.'
            ], 422);
        }

        // Verify status is for driver-helper-assignment
        $status = StatusModel::findOrFail($validated['status_id']);
        if ($status->related_to != 'driver-helper-assignment') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status selected. Please select a status related to driver-helper-assignment.'
            ], 422);
        }

        $validated['user_id'] = Auth::id();

        $assignment = DriverHelperAssignment::create($validated);

        // Update the bus with driver and helper IDs
        $bus->update([
            'driver_id' => $validated['driver_id'],
            'bus_helper_id' => $validated['bus_helper_id']
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Driver and Helper assigned successfully.',
                'data' => $assignment->load(['bus', 'driver', 'busHelper', 'status'])
            ]);
        }

        return redirect()->route('driver-helper-assignments.index')
            ->with('success', 'Driver and Helper assigned successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DriverHelperAssignment $driverHelperAssignment)
    {
        $driverHelperAssignment->load(['bus.busType', 'bus.busSubType', 'driver', 'busHelper', 'status', 'user']);
        return view('content.driver-helper-assignments.show', compact('driverHelperAssignment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DriverHelperAssignment $driverHelperAssignment)
    {
        $buses = Bus::where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
            ->with('busType', 'busSubType')
            ->get();
        $drivers = Driver::all();
        $busHelpers = BusHelper::all();
        $statuses = StatusModel::where('related_to', 'driver-helper-assignment')->get();

        return view('content.driver-helper-assignments.edit', compact(
            'driverHelperAssignment',
            'buses',
            'drivers',
            'busHelpers',
            'statuses'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DriverHelperAssignment $driverHelperAssignment)
    {
        $validated = $request->validate([
            'bus_id' => [
                'required',
                'exists:buses,id',
                Rule::unique('driver_helper_assignments', 'bus_id')->ignore($driverHelperAssignment->id)
            ],
            'driver_id' => 'required|exists:drivers,id',
            'bus_helper_id' => 'required|exists:bus_helpers,id',
            'status_id' => 'required|exists:statuses,id',
            'assignment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify bus is an own bus
        $bus = Bus::findOrFail($validated['bus_id']);
        if ($bus->bus_sub_type_id != BusSubType::OWN_BUS_SUB_TYPE_ID) {
            return response()->json([
                'success' => false,
                'message' => 'Only own buses can have driver and helper assignments.'
            ], 422);
        }

        // Verify status is for driver-helper-assignment
        $status = StatusModel::findOrFail($validated['status_id']);
        if ($status->related_to != 'driver-helper-assignment') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status selected. Please select a status related to driver-helper-assignment.'
            ], 422);
        }

        // Get the old bus ID before updating
        $oldBusId = $driverHelperAssignment->bus_id;

        $driverHelperAssignment->update($validated);

        // If bus changed, clear driver and helper from old bus
        if ($oldBusId != $validated['bus_id']) {
            $oldBus = Bus::find($oldBusId);
            if ($oldBus) {
                $oldBus->update([
                    'driver_id' => null,
                    'bus_helper_id' => null
                ]);
            }
        }

        // Update the new bus with driver and helper IDs
        $bus->update([
            'driver_id' => $validated['driver_id'],
            'bus_helper_id' => $validated['bus_helper_id']
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully.',
                'data' => $driverHelperAssignment->load(['bus', 'driver', 'busHelper', 'status'])
            ]);
        }

        return redirect()->route('driver-helper-assignments.index')
            ->with('success', 'Assignment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, DriverHelperAssignment $driverHelperAssignment)
    {
        try {
            // Get the bus before deleting the assignment
            $bus = $driverHelperAssignment->bus;

            // Delete the assignment
            $driverHelperAssignment->delete();

            // Clear driver and helper from the bus
            if ($bus) {
                $bus->update([
                    'driver_id' => null,
                    'bus_helper_id' => null
                ]);
            }

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment deleted successfully.'
                ]);
            }

            return redirect()->route('driver-helper-assignments.index')
                ->with('success', 'Assignment deleted successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting assignment: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('driver-helper-assignments.index')
                ->with('error', 'Error deleting assignment: ' . $e->getMessage());
        }
    }
}
