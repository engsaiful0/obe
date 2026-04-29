<?php

namespace App\Http\Controllers;

use App\Models\BusTrip;
use App\Models\Bus;
use App\Models\Stoppage;
use App\Models\BusSubType;
use App\Models\BusType;
use App\Models\Driver;
use App\Models\BusHelper;
use App\Models\Status as StatusModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BusTripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BusTrip::with(['bus.busSubType', 'startStoppage', 'endStoppage', 'user', 'driver', 'alternateDriver', 'busHelper', 'alternateBusHelper']);

        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by bus type
        if ($request->filled('bus_type_id')) {
            $query->whereHas('bus', function ($q) use ($request) {
                $q->where('bus_type_id', $request->bus_type_id);
            });
        }

        // Filter by bus sub type
        if ($request->filled('bus_sub_type_id')) {
            $query->whereHas('bus', function ($q) use ($request) {
                $q->where('bus_sub_type_id', $request->bus_sub_type_id);
            });
        }

        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by bus helper
        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        // Filter by trip type
        if ($request->filled('trip_type')) {
            $query->where('trip_type', $request->trip_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        $busTrips = $query->orderBy('trip_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $buses = Bus::with('busSubType')->get();
        $drivers = Driver::all();

        $statusOptions = StatusModel::where('related_to', 'bus-trip')->get();
        $busHelpers = BusHelper::all();

        $busSubTypes = BusSubType::all();
        $busTypes = BusType::all();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.bus-trips.partials.table', compact('busTrips'))->render(),
                'pagination' => $busTrips->links()->toHtml()
            ]);
        }

        return view('content.bus-trips.index', compact('busTrips', 'buses', 'drivers', 'busHelpers', 'busSubTypes', 'busTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buses = Bus::with(['busSubType', 'driver', 'busHelper'])->get();
        $stoppages = Stoppage::all();


        $driverStatusOptions = StatusModel::where('related_to', 'driver')
            ->where('status_name', 'like', '%active%')
            ->first();
        $drivers = Driver::where('status_id', $driverStatusOptions->id)->get();

        $helperStatusOptions = StatusModel::where('related_to', 'bus-helper')->where('status_name', 'like', '%active%')
            ->first();
        $busHelpers = BusHelper::where('status_id', $helperStatusOptions->id)->get();

        $busSubTypes = BusSubType::all();

        // Calculate trip numbers for each bus for today's date
        $today = Carbon::today()->format('Y-m-d');
        $busTripNumbers = [];
        foreach ($buses as $bus) {
            $busTripNumbers[$bus->id] = $this->getNextTripNumber($today, $bus->id);
        }

        return view('content.bus-trips.create', compact('buses', 'stoppages', 'drivers', 'busHelpers', 'busSubTypes', 'busTripNumbers', 'today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Convert empty strings to null for nullable fields
        $request->merge([
            'driver_id' => $request->driver_id === '' ? null : $request->driver_id,
            'alternate_driver_id' => $request->alternate_driver_id === '' ? null : $request->alternate_driver_id,
            'bus_helper_id' => $request->bus_helper_id === '' ? null : $request->bus_helper_id,
            'alternate_bus_helper_id' => $request->alternate_bus_helper_id === '' ? null : $request->alternate_bus_helper_id,
            'passengers' => $request->passengers === '' ? null : $request->passengers,
            'remarks' => $request->remarks === '' ? null : $request->remarks,
            'total_distance' => $request->total_distance === '' ? null : $request->total_distance,
            'in_time' => $request->in_time === '' ? null : $request->in_time,
            'out_time' => $request->out_time === '' ? null : $request->out_time,
        ]);

        // Debug: Log request data
        Log::info('BusTrip Store Request:', $request->all());

        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'alternate_driver_id' => 'nullable|exists:drivers,id',
            'bus_helper_id' => 'nullable|exists:bus_helpers,id',
            'alternate_bus_helper_id' => 'nullable|exists:bus_helpers,id',
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id',
            'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
            'trip_type' => 'required|in:in,out',
            'trip_date' => 'required|date',
            'in_time' => 'nullable|date_format:H:i:s',
            'out_time' => 'nullable|date_format:H:i:s',
            'total_distance' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $bus = Bus::with(['driver', 'busHelper'])->findOrFail($request->bus_id);

        $finalDriverId = $request->driver_id
            ?: $request->alternate_driver_id
            ?: $bus->driver_id;

        if (!$finalDriverId) {
            $errorMessage = 'Either driver or alternate driver must be provided.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['driver_id' => [$errorMessage]]
                ], 422);
            }
            return back()->withErrors(['driver_id' => $errorMessage]);
        }

        $finalBusHelperId = $request->bus_helper_id
            ?: $request->alternate_bus_helper_id
            ?: $bus->bus_helper_id;

        // Check for duplicate trip type on same date for same bus
        $existingTrip = BusTrip::where('bus_id', $request->bus_id)
            ->where('trip_date', $request->trip_date)
            ->where('start_stoppage_id', $request->start_stoppage_id)
            ->where('end_stoppage_id', $request->end_stoppage_id)
            ->where('trip_type', $request->trip_type)
            ->first();

        if ($existingTrip) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A ' . $request->trip_type . ' trip already exists for this bus on this date.',
                    'errors' => ['trip_type' => ['A ' . $request->trip_type . ' trip already exists for this bus on this date.']]
                ], 422);
            }
            return back()->withErrors(['trip_type' => 'A ' . $request->trip_type . ' trip already exists for this bus on this date.']);
        }

        try {
            // Load the bus to get default driver and helper
            $bus = Bus::find($request->bus_id);

            // Determine driver_id and alternate_driver_id:
            // If alternate_driver_id is NOT selected → use bus's default driver_id, set alternate_driver_id to null
            // If alternate_driver_id IS selected → set driver_id to null, use selected alternate_driver_id
            $finalDriverId = null;
            $finalAlternateDriverId = null;
            if ($request->alternate_driver_id) {
                // Alternate driver is selected, so driver_id is null and alternate_driver_id is the selected value
                $finalAlternateDriverId = $request->alternate_driver_id;
            } elseif ($bus && $bus->driver_id) {
                // No alternate driver selected, use bus's default driver
                $finalDriverId = $bus->driver_id;
            }

            // Determine bus_helper_id and alternate_bus_helper_id:
            // If alternate_bus_helper_id is NOT selected → use bus's default bus_helper_id, set alternate_bus_helper_id to null
            // If alternate_bus_helper_id IS selected → set bus_helper_id to null, use selected alternate_bus_helper_id
            $finalBusHelperId = null;
            $finalAlternateBusHelperId = null;
            if ($request->alternate_bus_helper_id) {
                // Alternate helper is selected, so bus_helper_id is null and alternate_bus_helper_id is the selected value
                $finalAlternateBusHelperId = $request->alternate_bus_helper_id;
            } elseif ($bus && $bus->bus_helper_id) {
                // No alternate helper selected, use bus's default helper
                $finalBusHelperId = $bus->bus_helper_id;
            }

            $busTrip = BusTrip::create([
                'bus_id' => $request->bus_id,
                'driver_id' => $finalDriverId,
                'alternate_driver_id' => $finalAlternateDriverId,
                'bus_helper_id' => $finalBusHelperId,
                'alternate_bus_helper_id' => $finalAlternateBusHelperId,
                'start_stoppage_id' => $request->start_stoppage_id,
                'end_stoppage_id' => $request->end_stoppage_id,
                'bus_sub_type_id' => $request->bus_sub_type_id,
                'trip_type' => $request->trip_type,
                'trip_number' => $this->getNextTripNumber($request->trip_date, $request->bus_id),
                'trip_date' => $request->trip_date,
                'passengers' => $request->passengers,
                'in_time' => $request->in_time,
                'out_time' => $request->out_time,
                'total_distance' => $request->total_distance,
                'remarks' => $request->remarks,
                'user_id' => Auth::id(),
            ]);

            Log::info('BusTrip created successfully:', $busTrip->toArray());
        } catch (\Exception $e) {
            Log::error('BusTrip creation failed:', ['error' => $e->getMessage()]);

            // Handle specific database constraint violations
            $errorMessage = 'Failed to create bus trip.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $errorMessage = 'A ' . $request->trip_type . ' trip already exists for this bus on this date.';
            } elseif (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                $errorMessage = 'This trip already exists for the selected bus and date.';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return back()->withErrors(['trip_type' => $errorMessage]);
        }

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus trip recorded successfully.',
                'redirect_url' => route('bus-trips.index')
            ]);
        }

        return redirect()->route('bus-trips.index')
            ->with('success', 'Bus trip recorded successfully.');
    }

    /**
     * Store a newly created Own Bus trip.
     */
    public function storeOwnBus(Request $request)
    {
        return $this->storeTrip($request, BusSubType::OWN_BUS_SUB_TYPE_ID, true);
    }

    /**
     * Store a newly created BRTC Bus trip.
     */
    public function storeBRTCBus(Request $request)
    {
        return $this->storeTrip($request, BusSubType::BRTC_BUS_SUB_TYPE_ID, false, true);
    }

    /**
     * Store a newly created Hired Bus trip.
     */
    public function storeHiredBus(Request $request)
    {
        return $this->storeTrip($request, BusSubType::HIRED_BUS_SUB_TYPE_ID, false);
    }

    /**
     * Common method to store bus trips with specific validation rules.
     */
    private function storeTrip(Request $request, $expectedSubTypeId, $allowAlternateDriver = false, $requireDistance = false)
    {
        // Convert empty strings to null for nullable fields
        $request->merge([
            'driver_id' => $request->driver_id === '' ? null : $request->driver_id,
            'alternate_driver_id' => $request->alternate_driver_id === '' ? null : $request->alternate_driver_id,
            'bus_helper_id' => $request->bus_helper_id === '' ? null : $request->bus_helper_id,
            'alternate_bus_helper_id' => $request->alternate_bus_helper_id === '' ? null : $request->alternate_bus_helper_id,
            'passengers' => ($request->passengers === '' || $request->passengers === null) ? null : (int)$request->passengers,
            'remarks' => $request->remarks === '' ? null : $request->remarks,
            'total_distance' => $request->total_distance === '' ? null : $request->total_distance,
            'in_time' => $request->in_time === '' ? null : $request->in_time,
            'out_time' => $request->out_time === '' ? null : $request->out_time,
        ]);

        // Debug: Log request data
        Log::info('BusTrip Store Request:', $request->all());

        // Validate bus_sub_type_id matches expected type
        if ($request->bus_sub_type_id != $expectedSubTypeId) {
            $errorMessage = 'Invalid bus sub type for this operation.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['bus_sub_type_id' => [$errorMessage]]
                ], 422);
            }
            return back()->withErrors(['bus_sub_type_id' => $errorMessage]);
        }

        // Build validation rules
        $rules = [
            'bus_id' => 'required|exists:buses,id',
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id',
            'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
            'trip_type' => 'required|in:in,out',
            'trip_date' => 'required|date',
            'in_time' => 'nullable|date_format:H:i:s',
            'out_time' => 'nullable|date_format:H:i:s',
            'remarks' => 'nullable|string|max:1000',
        ];

        // Own Bus allows alternate driver, BRTC and Hired Bus don't require driver
        if ($allowAlternateDriver) {
            $rules['driver_id'] = 'nullable|exists:drivers,id';
            $rules['alternate_driver_id'] = 'nullable|exists:drivers,id';
            $rules['alternate_bus_helper_id'] = 'nullable|exists:bus_helpers,id';
        } else {
            // BRTC and Hired Bus: driver_id is optional
            $rules['driver_id'] = 'nullable|exists:drivers,id';
        }

        $rules['bus_helper_id'] = 'nullable|exists:bus_helpers,id';
        $rules['passengers'] = 'nullable|integer|min:0';

        // BRTC Bus requires distance
        if ($requireDistance) {
            $rules['total_distance'] = 'required|numeric|min:0';
        } else {
            $rules['total_distance'] = 'nullable|numeric|min:0';
        }

        try {
            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('BusTrip Validation Failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        // Ensure at least one driver is provided (only for Own Bus: either driver_id or alternate_driver_id)
        if ($allowAlternateDriver) {
            if (!$request->driver_id && !$request->alternate_driver_id) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Either driver or alternate driver must be provided.',
                        'errors' => ['driver_id' => ['Either driver or alternate driver must be provided.']]
                    ], 422);
                }
                return back()->withErrors(['driver_id' => 'Either driver or alternate driver must be provided.']);
            }
        }
        // BRTC and Hired Bus: driver is optional, no validation needed

        // Check for duplicate trip type on same date for same bus
        $existingTrip = BusTrip::where('bus_id', $request->bus_id)
            ->where('trip_date', $request->trip_date)
            ->where('start_stoppage_id', $request->start_stoppage_id)
            ->where('end_stoppage_id', $request->end_stoppage_id)
            ->where('trip_type', $request->trip_type)
            ->first();

        if ($existingTrip) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A ' . $request->trip_type . ' trip already exists for this bus on this date.',
                    'errors' => ['trip_type' => ['A ' . $request->trip_type . ' trip already exists for this bus on this date.']]
                ], 422);
            }
            return back()->withErrors(['trip_type' => 'A ' . $request->trip_type . ' trip already exists for this bus on this date.']);
        }

        try {
            // Load the bus to get default driver and helper
            $bus = Bus::find($request->bus_id);

            // Determine driver_id and alternate_driver_id:
            // If alternate_driver_id is NOT selected → use bus's default driver_id, set alternate_driver_id to null
            // If alternate_driver_id IS selected → set driver_id to null, use selected alternate_driver_id
            $finalDriverId = null;
            $finalAlternateDriverId = null;
            if ($request->alternate_driver_id) {
                // Alternate driver is selected, so driver_id is null and alternate_driver_id is the selected value
                $finalAlternateDriverId = $request->alternate_driver_id;
            } elseif ($bus && $bus->driver_id) {
                // No alternate driver selected, use bus's default driver
                $finalDriverId = $bus->driver_id;
            }

            // Determine bus_helper_id and alternate_bus_helper_id:
            // If alternate_bus_helper_id is NOT selected → use bus's default bus_helper_id, set alternate_bus_helper_id to null
            // If alternate_bus_helper_id IS selected → set bus_helper_id to null, use selected alternate_bus_helper_id
            $finalBusHelperId = null;
            $finalAlternateBusHelperId = null;
            if ($request->alternate_bus_helper_id) {
                // Alternate helper is selected, so bus_helper_id is null and alternate_bus_helper_id is the selected value
                $finalAlternateBusHelperId = $request->alternate_bus_helper_id;
            } elseif ($bus && $bus->bus_helper_id) {
                // No alternate helper selected, use bus's default helper
                $finalBusHelperId = $bus->bus_helper_id;
            }

            $busTrip = BusTrip::create([
                'bus_id' => $request->bus_id,
                'driver_id' => $finalDriverId,
                'alternate_driver_id' => $finalAlternateDriverId,
                'bus_helper_id' => $finalBusHelperId,
                'alternate_bus_helper_id' => $finalAlternateBusHelperId,
                'start_stoppage_id' => $request->start_stoppage_id,
                'end_stoppage_id' => $request->end_stoppage_id,
                'bus_sub_type_id' => $request->bus_sub_type_id,
                'trip_type' => $request->trip_type,
                'trip_number' => $this->getNextTripNumber($request->trip_date, $request->bus_id),
                'trip_date' => $request->trip_date,
                'passengers' => $request->passengers,
                'in_time' => $request->in_time,
                'out_time' => $request->out_time,
                'total_distance' => $request->total_distance,
                'remarks' => $request->remarks,
                'user_id' => Auth::id(),
            ]);

            Log::info('BusTrip created successfully:', $busTrip->toArray());
        } catch (\Exception $e) {
            Log::error('BusTrip creation failed:', ['error' => $e->getMessage()]);

            // Handle specific database constraint violations
            $errorMessage = 'Failed to create bus trip.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $errorMessage = 'A ' . $request->trip_type . ' trip already exists for this bus on this date.';
            } elseif (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                $errorMessage = 'This trip already exists for the selected bus and date.';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return back()->withErrors(['trip_type' => $errorMessage]);
        }

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus trip recorded successfully.',
                'redirect_url' => route('bus-trips.index')
            ]);
        }

        return redirect()->route('bus-trips.index')
            ->with('success', 'Bus trip recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusTrip $busTrip)
    {
        $busTrip->load(['bus.busSubType', 'startStoppage', 'endStoppage', 'driver', 'alternateDriver', 'busHelper', 'alternateBusHelper', 'user']);

        return view('content.bus-trips.show', compact('busTrip'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusTrip $busTrip)
    {
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();

        $buses = Bus::with(['busSubType', 'driver', 'busHelper'])
            ->when($busActiveStatus, fn ($query) => $query->where('status_id', $busActiveStatus->id))
            ->get();

        $stoppages = Stoppage::all();

        $driverStatusOptions = StatusModel::where('related_to', 'driver')
            ->where('status_name', 'like', '%active%')
            ->first();

        $drivers = Driver::when(
                $driverStatusOptions,
                fn ($query) => $query->where('status_id', $driverStatusOptions->id)
            )
            ->orWhere('id', $busTrip->driver_id)
            ->get();

        $helperStatusOptions = StatusModel::where('related_to', 'bus-helper')
            ->where('status_name', 'like', '%active%')
            ->first();

        $busHelpers = BusHelper::when(
                $helperStatusOptions,
                fn ($query) => $query->where('status_id', $helperStatusOptions->id)
            )
            ->orWhere('id', $busTrip->bus_helper_id)
            ->get();

        $busSubTypes = BusSubType::all();

        $busOptions = $buses->map(function ($bus) {
            return [
                'id' => $bus->id,
                'model_name' => $bus->model_name,
                'registration_number' => $bus->registration_number,
            ];
        });

        return view('content.bus-trips.edit', compact(
            'busTrip',
            'buses',
            'stoppages',
            'drivers',
            'busHelpers',
            'busSubTypes',
            'busOptions'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusTrip $busTrip)
    {
        // Convert empty strings to null for nullable fields
        $request->merge([
            'driver_id' => $request->driver_id === '' ? null : $request->driver_id,
            'alternate_driver_id' => $request->alternate_driver_id === '' ? null : $request->alternate_driver_id,
            'bus_helper_id' => $request->bus_helper_id === '' ? null : $request->bus_helper_id,
            'alternate_bus_helper_id' => $request->alternate_bus_helper_id === '' ? null : $request->alternate_bus_helper_id,
            'passengers' => $request->passengers === '' ? null : $request->passengers,
            'in_time' => $request->in_time === '' ? null : $request->in_time,
            'out_time' => $request->out_time === '' ? null : $request->out_time,
            'total_distance' => $request->total_distance === '' ? null : $request->total_distance,
            'remarks' => $request->remarks === '' ? null : $request->remarks,
        ]);

        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'alternate_driver_id' => 'nullable|exists:drivers,id',
            'bus_helper_id' => 'nullable|exists:bus_helpers,id',
            'alternate_bus_helper_id' => 'nullable|exists:bus_helpers,id',
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id',
            'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
            'trip_type' => 'required|in:in,out',
            'trip_date' => 'required|date',
            'passengers' => 'nullable|integer|min:0',
            'in_time' => 'nullable|date_format:H:i',
            'out_time' => 'nullable|date_format:H:i',
            'total_distance' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $bus = Bus::with(['driver', 'busHelper'])->findOrFail($request->bus_id);

        // Determine driver_id and alternate_driver_id:
        // If alternate_driver_id is NOT selected → use bus's default driver_id, set alternate_driver_id to null
        // If alternate_driver_id IS selected → set driver_id to null, use selected alternate_driver_id
        $finalDriverId = null;
        $finalAlternateDriverId = null;
        if ($request->alternate_driver_id) {
            // Alternate driver is selected, so driver_id is null and alternate_driver_id is the selected value
            $finalAlternateDriverId = $request->alternate_driver_id;
        } elseif ($bus && $bus->driver_id) {
            // No alternate driver selected, use bus's default driver
            $finalDriverId = $bus->driver_id;
        }

        // Check if we have at least one driver (only for Own Bus trips)
        // For BRTC and Hired Bus, driver is optional
        $busSubType = BusSubType::find($request->bus_sub_type_id);
        $isOwnBus = $busSubType && $busSubType->id == BusSubType::OWN_BUS_SUB_TYPE_ID;
        
        if ($isOwnBus && !$finalDriverId && !$finalAlternateDriverId) {
            $errorMessage = 'Either driver or alternate driver must be provided.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['driver_id' => [$errorMessage]]
                ], 422);
            }
            return back()->withErrors(['driver_id' => $errorMessage]);
        }

        // Determine bus_helper_id and alternate_bus_helper_id:
        // If alternate_bus_helper_id is NOT selected → use bus's default bus_helper_id, set alternate_bus_helper_id to null
        // If alternate_bus_helper_id IS selected → set bus_helper_id to null, use selected alternate_bus_helper_id
        $finalBusHelperId = null;
        $finalAlternateBusHelperId = null;
        if ($request->alternate_bus_helper_id) {
            // Alternate helper is selected, so bus_helper_id is null and alternate_bus_helper_id is the selected value
            $finalAlternateBusHelperId = $request->alternate_bus_helper_id;
        } elseif ($bus && $bus->bus_helper_id) {
            // No alternate helper selected, use bus's default helper
            $finalBusHelperId = $bus->bus_helper_id;
        }

        // Check for duplicate trip type on same date for same bus (excluding current record)
        $existingTrip = BusTrip::where('bus_id', $request->bus_id)
            ->where('trip_date', $request->trip_date)
            ->where('trip_type', $request->trip_type)
            ->where('id', '!=', $busTrip->id)
            ->first();

        if ($existingTrip) {
            $errorMessage = 'A ' . $request->trip_type . ' trip already exists for this bus on this date.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['trip_type' => [$errorMessage]]
                ], 422);
            }
            return back()->withErrors(['trip_type' => $errorMessage]);
        }

        $newTripNumber = $busTrip->trip_number;
        $tripDateChanged = $busTrip->trip_date->format('Y-m-d') !== $request->trip_date;
        $busChanged = $busTrip->bus_id !== (int) $request->bus_id;

        if ($tripDateChanged || $busChanged || !$newTripNumber) {
            $newTripNumber = $this->getNextTripNumber($request->trip_date, $request->bus_id, $busTrip->id);
        }

        $busTrip->update([
            'bus_id' => $request->bus_id,
            'driver_id' => $finalDriverId,
            'alternate_driver_id' => $finalAlternateDriverId,
            'bus_helper_id' => $finalBusHelperId,
            'alternate_bus_helper_id' => $finalAlternateBusHelperId,
            'start_stoppage_id' => $request->start_stoppage_id,
            'end_stoppage_id' => $request->end_stoppage_id,
            'bus_sub_type_id' => $request->bus_sub_type_id,
            'trip_type' => $request->trip_type,
            'trip_date' => $request->trip_date,
            'trip_number' => $newTripNumber,
            'passengers' => $request->passengers,
            'in_time' => $request->in_time,
            'out_time' => $request->out_time,
            'total_distance' => $request->total_distance,
            'remarks' => $request->remarks,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus trip updated successfully.',
                'redirect_url' => route('bus-trips.index')
            ]);
        }

        return redirect()->route('bus-trips.index')
            ->with('success', 'Bus trip updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, BusTrip $busTrip)
    {
        try {
            $busTrip->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus trip deleted successfully.'
                ]);
            }

            return redirect()->route('bus-trips.index')
                ->with('success', 'Bus trip deleted successfully.');
        } catch (\Exception $e) {
            Log::error('BusTrip deletion failed:', ['error' => $e->getMessage()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete bus trip. Please try again.'
                ], 500);
            }

            return redirect()->route('bus-trips.index')
                ->with('error', 'Failed to delete bus trip. Please try again.');
        }
    }

    /**
     * Add all bus trips for a specific date
     */
    public function addAllBusTrip(Request $request)
    {



        $helperStatusOptions = StatusModel::where('related_to', 'bus-helper')->where('status_name', 'like', '%active%')
            ->first();

        $busActiveStatus = StatusModel::where('related_to', 'bus')->where('status_name', 'like', '%active%')
            ->first();

        $busHelpers = BusHelper::where('status_id', $helperStatusOptions->id)->get();
        $buses = Bus::where('status_id', $busActiveStatus->id)->with(['busSubType', 'driver', 'busHelper'])->get();
        $stoppages = Stoppage::all();

        $driverStatusOptions = StatusModel::where('related_to', 'driver')
            ->where('status_name', 'like', '%active%')
            ->first();
        $drivers = Driver::where('status_id', $driverStatusOptions->id)->get();

        // Calculate trip numbers for each bus for today's date
        $today = Carbon::today()->format('Y-m-d');
        $busTripNumbers = [];
        foreach ($buses as $bus) {
            $busTripNumbers[$bus->id] = $this->getNextTripNumber($today, $bus->id);
        }

        return view('content.bus-trips.add-all', compact('buses', 'stoppages', 'drivers', 'busHelpers', 'busTripNumbers', 'today'));
    }

    /**
     * View bus trips
     */
    public function viewBusTrip(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Get trip numbers for buses for a specific date (AJAX)
     */
    public function getTripNumbersForDate(Request $request)
    {
        $tripDate = $request->input('trip_date');
        
        if (!$tripDate) {
            return response()->json([
                'success' => false,
                'message' => 'Trip date is required'
            ], 400);
        }

        $busIds = $request->input('bus_ids', []);
        
        // If no bus IDs provided, get all active buses
        if (empty($busIds)) {
            $busActiveStatus = StatusModel::where('related_to', 'bus')
                ->where('status_name', 'like', '%active%')
                ->first();
            $buses = Bus::where('status_id', $busActiveStatus->id)->pluck('id');
            $busIds = $buses->toArray();
        }

        $tripNumbers = [];
        foreach ($busIds as $busId) {
            $tripNumbers[$busId] = $this->getNextTripNumber($tripDate, $busId);
        }

        return response()->json([
            'success' => true,
            'trip_numbers' => $tripNumbers
        ]);
    }

    /**
     * Get bus subtype for AJAX
     */
    public function getBusSubType(Request $request)
    {
        $busId = $request->bus_id;
        $bus = Bus::with('busSubType')->find($busId);

        if ($bus && $bus->busSubType) {
            return response()->json([
                'success' => true,
                'sub_type' => $bus->busSubType
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Bus or subtype not found'
        ]);
    }

    /**
     * Get buses based on bus type and sub-type for AJAX
     */
    public function getBusesByType(Request $request)
    {
        $busTypeId = $request->bus_type_id;
        $busSubTypeId = $request->bus_sub_type_id;

        $query = Bus::with(['busType', 'busSubType', 'driver', 'assistant']);

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
    public function getBusesNamesBySubType(Request $request)
    {
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();

        try {
            if ($request->sub_type_id == 'all' || empty($request->sub_type_id)) {
                $buses = Bus::with('busSubType')
                    ->where('status_id', $busActiveStatus->id)
                    ->get();
            } else {
                $buses = Bus::with('busSubType')
                    ->where('bus_sub_type_id', $request->sub_type_id)
                    ->where('status_id', $busActiveStatus->id)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'buses' => $buses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error loading buses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate trip data
     */
    public function validateTrip(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'trip_date' => 'required|date',
            'trip_type' => 'required|in:in,out',
        ]);

        // Check for duplicate trip type on same date for same bus
        $existingTrip = BusTrip::where('bus_id', $request->bus_id)
            ->where('trip_date', $request->trip_date)
            ->where('trip_type', $request->trip_type)
            ->first();

        if ($existingTrip) {
            return response()->json([
                'valid' => false,
                'message' => 'A ' . $request->trip_type . ' trip already exists for this bus on this date.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Trip data is valid.'
        ]);
    }

    /**
     * Monthly billing for bus trips
     */
    public function monthlyBilling(Request $request)
    {
        $buses = Bus::with('busSubType')->get();

        // Get current month and year if not specified
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('n'));

        $billingData = [];

        foreach ($buses as $bus) {
            $monthlyBill = BusTrip::getMonthlyBill($bus->id, $year, $month);
            $billingData[] = [
                'bus' => $bus,
                'bill' => $monthlyBill
            ];
        }

        return view('content.bus-trips.monthly-billing', compact('billingData', 'buses', 'year', 'month'));
    }

    /**
     * Display trip report with drivers as rows and dates as columns
     */
    public function tripReport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Default to current month if no dates provided
        if (!$fromDate) {
            $fromDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$toDate) {
            $toDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // Convert to Carbon instances
        $startDate = Carbon::parse($fromDate);
        $endDate = Carbon::parse($toDate);

        // Get all active drivers for the user
        $drivers = Driver::where('user_id', Auth::id())
            ->where('status', Driver::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get();

        // Get all trips in the date range
        $trips = BusTrip::with('driver')
            ->where('user_id', Auth::id())
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->whereNotNull('driver_id')
            ->get();

        // Generate date range
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Build pivot data: driver_id => [date => count]
        $pivotData = [];
        foreach ($drivers as $driver) {
            $pivotData[$driver->id] = [];
            foreach ($dates as $date) {
                $pivotData[$driver->id][$date] = 0;
            }
        }

        // Count trips per driver per date
        foreach ($trips as $trip) {
            $dateKey = $trip->trip_date->format('Y-m-d');
            if (isset($pivotData[$trip->driver_id][$dateKey])) {
                $pivotData[$trip->driver_id][$dateKey]++;
            }
        }

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.report.trip-report-table', compact('drivers', 'dates', 'pivotData', 'fromDate', 'toDate'))->render()
            ]);
        }

        return view('content.report.trip-report', compact('drivers', 'dates', 'pivotData', 'fromDate', 'toDate'));
    }

    /**
     * Print trip report
     */
    public function printTripReport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Default to current month if no dates provided
        if (!$fromDate) {
            $fromDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$toDate) {
            $toDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // Convert to Carbon instances
        $startDate = Carbon::parse($fromDate);
        $endDate = Carbon::parse($toDate);

        // Get all active drivers for the user
        $drivers = Driver::where('user_id', Auth::id())
            ->where('status', Driver::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get();

        // Get all trips in the date range
        $trips = BusTrip::with('driver')
            ->where('user_id', Auth::id())
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->whereNotNull('driver_id')
            ->get();

        // Generate date range
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Build pivot data: driver_id => [date => count]
        $pivotData = [];
        foreach ($drivers as $driver) {
            $pivotData[$driver->id] = [];
            foreach ($dates as $date) {
                $pivotData[$driver->id][$date] = 0;
            }
        }

        // Count trips per driver per date
        foreach ($trips as $trip) {
            $dateKey = $trip->trip_date->format('Y-m-d');
            if (isset($pivotData[$trip->driver_id][$dateKey])) {
                $pivotData[$trip->driver_id][$dateKey]++;
            }
        }

        return view('content.report.trip-report-print-list', compact('drivers', 'dates', 'pivotData', 'fromDate', 'toDate'));
    }

    /**
     * Export trip report to PDF
     */
    public function exportTripReportPdf(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Default to current month if no dates provided
        if (!$fromDate) {
            $fromDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$toDate) {
            $toDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // Convert to Carbon instances
        $startDate = Carbon::parse($fromDate);
        $endDate = Carbon::parse($toDate);

        // Get all active drivers for the user
        $drivers = Driver::where('user_id', Auth::id())
            ->where('status', Driver::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get();

        // Get all trips in the date range
        $trips = BusTrip::with('driver')
            ->where('user_id', Auth::id())
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->whereNotNull('driver_id')
            ->get();

        // Generate date range
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Build pivot data: driver_id => [date => count]
        $pivotData = [];
        foreach ($drivers as $driver) {
            $pivotData[$driver->id] = [];
            foreach ($dates as $date) {
                $pivotData[$driver->id][$date] = 0;
            }
        }

        // Count trips per driver per date
        foreach ($trips as $trip) {
            $dateKey = $trip->trip_date->format('Y-m-d');
            if (isset($pivotData[$trip->driver_id][$dateKey])) {
                $pivotData[$trip->driver_id][$dateKey]++;
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('content.report.trip-report-pdf', compact('drivers', 'dates', 'pivotData', 'fromDate', 'toDate'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('trip-report-' . $fromDate . '-to-' . $toDate . '.pdf');
    }

    /**
     * Determine the next trip number for a given date and bus.
     */
    private function getNextTripNumber($tripDate, $busId, $excludeTripId = null)
    {
        $query = BusTrip::whereDate('trip_date', $tripDate)
            ->where('bus_id', $busId);

        if ($excludeTripId) {
            $query->where('id', '!=', $excludeTripId);
        }

        $maxTripNumber = $query->max('trip_number');

        return $maxTripNumber ? $maxTripNumber + 1 : 1;
    }
}
