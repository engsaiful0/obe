<?php

namespace App\Http\Controllers;

use App\Models\Punishment;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\User;
use App\Models\Employee;
use App\Models\BusHelper;
use App\Models\Status as StatusModel;
use App\Models\BusType;
use App\Models\BusSubType;
use App\Models\PunishmentType;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PunishmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Punishment::with(['bus', 'driver', 'busHelper', 'user', 'punishmentType', 'violationType', 'witnessEmployee']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('bus', function ($busQuery) use ($search) {
                      $busQuery->where('registration_number', 'like', "%{$search}%")
                                  ->orWhere('model_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('driver', function ($driverQuery) use ($search) {
                      $driverQuery->where('full_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('punishmentType', function ($typeQuery) use ($search) {
                      $typeQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('violationType', function ($typeQuery) use ($search) {
                      $typeQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by punishment type
        if ($request->filled('punishment_type_id')) {
            $query->where('punishment_type_id', $request->punishment_type_id);
        }

        // Filter by violation type
        if ($request->filled('violation_type_id')) {
            $query->where('violation_type_id', $request->violation_type_id);
        }

        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by bus type
        if ($request->filled('bus_type_id')) {
            $query->whereHas('bus', function($q) use ($request) {
                $q->where('bus_type_id', $request->bus_type_id);
            });
        }

        // Filter by bus sub type
        if ($request->filled('bus_sub_type_id')) {
            $query->whereHas('bus', function($q) use ($request) {
                $q->where('bus_sub_type_id', $request->bus_sub_type_id);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('punishment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('punishment_date', '<=', $request->date_to);
        }

        $punishments = $query->orderBy('punishment_date', 'desc')->paginate(15);

        // Get filter options
        $buses = Bus::with(['busType', 'busSubType'])->get();
        $punishmentTypes = PunishmentType::all();
        $violationTypes = ViolationType::all();
        $busTypes = BusType::all();
        $busSubTypes = BusSubType::all();
        $drivers = Driver::all();
        $users = User::all();
        $witness_employees = Employee::all();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.punishments.partials.table', compact('punishments'))->render(),
                'pagination' => $punishments->links()->toHtml()
            ]);
        }

        return view('content.punishments.index', compact(
            'punishments',
            'buses',
            'busTypes',
            'busSubTypes',
            'drivers',
            'users',
            'punishmentTypes',
            'violationTypes',
            'witness_employees'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buses = Bus::all();
        $drivers = Driver::all();
        $busHelpers = BusHelper::all();
        $users = User::all();
        $busSubTypes = BusSubType::all();
        $punishmentTypes = PunishmentType::all();
        $violationTypes = ViolationType::all();
        $witness_employees = Employee::all();

        return view('content.punishments.create', compact(
            'buses',
            'drivers',
            'busHelpers',
            'users',
            'busSubTypes',
            'punishmentTypes',
            'violationTypes',
            'witness_employees'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Punishment store method called', [
                'request_data' => $request->all(),
                'is_ajax' => $request->ajax(),
                'expects_json' => $request->expectsJson()
            ]);
            
            $validated = $request->validate([
            'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
            'bus_id' => 'required|exists:buses,id',
            'punishment_type_id' => 'required|exists:punishment_types,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'description' => 'required|string|min:10',
            'punishment_date' => 'required|date',
            'fine_amount' => 'nullable|numeric|min:0',
            'suspension_days' => 'nullable|integer|min:1',
            'status' => 'nullable|in:active,completed,cancelled',
            'remarks' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'witness_employee_id' => 'nullable|exists:employees,id',
        ]);

        // Handle file upload
        if ($request->hasFile('document')) {
            $validated['document_path'] = $request->file('document')->store('punishments/documents', 'public');
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'active';

        $punishment = Punishment::create($validated);
        
            Log::info('Punishment created successfully', [
            'punishment_id' => $punishment->id,
            'expects_json' => $request->expectsJson()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punishment record created successfully.',
                'data' => $punishment
            ], 201);
        }

            return redirect()->route('punishments.index')
                ->with('success', 'Punishment record created successfully.');
        } catch (\Exception $e) {
            Log::error('Punishment creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the punishment.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'An error occurred while creating the punishment.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Punishment $punishment)
    {
        $punishment->load([
            'bus.brand', 
            'bus.busType', 
            'bus.supplier',
            'bus.assignedBusHelpers',
            'driver', 
            'user', 
            'punishmentType', 
            'violationType', 
            'witnessEmployee'
        ]);

        return view('content.punishments.show', compact('punishment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Punishment $punishment)
    {
        $buses = Bus::all();
        $drivers = Driver::all();
        $busHelpers = BusHelper::all();
        $users = User::all();
        $busSubTypes = BusSubType::all();
        $punishmentTypes = PunishmentType::all();
        $violationTypes = ViolationType::all();
        $witness_employees = Employee::all();

        return view('content.punishments.edit', compact(
            'punishment',
            'buses',
            'drivers',
            'busHelpers',
            'users',
            'busSubTypes',
            'punishmentTypes',
            'violationTypes',
            'witness_employees'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Punishment $punishment)
    {
        $validated = $request->validate([
            'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
            'bus_id' => 'required|exists:buses,id',
            'punishment_type_id' => 'required|exists:punishment_types,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'description' => 'required|string|min:10',
            'punishment_date' => 'required|date',
            'fine_amount' => 'nullable|numeric|min:0',
            'suspension_days' => 'nullable|integer|min:1',
            'status' => 'nullable|in:active,completed,cancelled',
            'remarks' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'witness_employee_id' => 'nullable|exists:employees,id',
        ]);

        // Handle file upload
        if ($request->hasFile('document')) {
            // Delete old document if exists
            if ($punishment->document_path) {
                Storage::disk('public')->delete($punishment->document_path);
            }
            $validated['document_path'] = $request->file('document')->store('punishments/documents', 'public');
        }

        $punishment->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punishment record updated successfully.',
                'data' => $punishment
            ]);
        }

        return redirect()->route('punishments.index')
            ->with('success', 'Punishment record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Punishment $punishment)
    {
        // Delete associated document if exists
        if ($punishment->document_path) {
            Storage::disk('public')->delete($punishment->document_path);
        }

        $punishment->delete();

        return redirect()->route('punishments.index')
            ->with('success', 'Punishment record deleted successfully.');
    }

    /**
     * Get buses based on bus type and sub-type for AJAX
     */
    public function getBusesByType(Request $request)
    {
        $busTypeId = $request->bus_type_id;
        $busSubTypeId = $request->bus_sub_type_id;
        
        $query = Bus::with(['busType', 'busSubType', 'driver']);
        
        if ($busTypeId) {
            $query->where('bus_type_id', $busTypeId);
        }
        
        if ($busSubTypeId) {
            $query->where('bus_sub_type_id', $busSubTypeId);
        }
        
        $buses = $query->get();
        
        return response()->json([
            'success' => true,
            'buses' => $buses
        ]);
    }

    /**
     * Get buses by sub-type for AJAX
     */
    public function getBusesBySubType(Request $request)
    {
        try {
            $busSubTypeId = $request->bus_sub_type_id;
            
            if (!$busSubTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bus sub type ID is required',
                    'buses' => []
                ], 400);
            }
            $busActiveStatus = StatusModel::where('related_to', 'bus')
                ->where('status_name', 'like', '%active%')
                ->first();

            $buses = Bus::with('busSubType')
                ->where('bus_sub_type_id', $busSubTypeId)
                ->where('status_id', $busActiveStatus->id)
                ->get();

            return response()->json([
                'success' => true,
                'buses' => $buses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading buses: ' . $e->getMessage(),
                'buses' => []
            ], 500);
        }
    }

    /**
     * Get drivers for AJAX
     */
    public function getDrivers(Request $request)
    {
        $drivers = Driver::all();
        
        return response()->json([
            'success' => true,
            'drivers' => $drivers
        ]);
    }
}
