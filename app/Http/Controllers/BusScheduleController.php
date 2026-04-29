<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\BusSchedule;
use App\Models\BusScheduleEntry;
use App\Models\Stoppage;
use App\Models\BusRoute;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\BusType;
use App\Models\BusSubType;
use App\Models\BusHelper;
use App\Models\BusUser;
use App\Models\BusScheduleKeyword;
use App\Models\TripTime;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class BusScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BusSchedule::with([
            'startStoppage', 'endStoppage', 'busRoute', 
            'bus', 'busType', 'busSubType', 'driver', 'busHelper', 'busUser', 'keyword', 'tripTime'
        ]);

        // Apply filters
        if ($request->filled('start_stoppage_id')) {
            $query->where('start_stoppage_id', $request->start_stoppage_id);
        }

        if ($request->filled('end_stoppage_id')) {
            $query->where('end_stoppage_id', $request->end_stoppage_id);
        }

        if ($request->filled('bus_route_id')) {
            $query->where('bus_route_id', $request->bus_route_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        if ($request->filled('bus_user_id')) {
            $query->where('bus_user_id', $request->bus_user_id);
        }

        if ($request->filled('keyword_id')) {
            $query->where('keyword_id', $request->keyword_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

      

        if ($request->filled('trip_time_id')) {
            $query->where('trip_time_id', $request->trip_time_id);
        }

        if ($request->filled('bus_type_id')) {
            $query->where('bus_type_id', $request->bus_type_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('startStoppage', function($q) use ($search) {
                    $q->where('stoppage_name', 'like', "%{$search}%");
                })
                ->orWhereHas('endStoppage', function($q) use ($search) {
                    $q->where('stoppage_name', 'like', "%{$search}%");
                })
                ->orWhereHas('bus', function($q) use ($search) {
                    $q->where('model_name', 'like', "%{$search}%")
                      ->orWhere('registration_number', 'like', "%{$search}%");
                })
                ->orWhereHas('driver', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })
                ->orWhereHas('busHelper', function($q) use ($search) {
                    $q->where('bus_helper_name', 'like', "%{$search}%");
                });
            });
        }

        $schedules = $query->orderBy('created_at', 'desc')->paginate(15);

        // For filter dropdowns
        $startStoppages = Stoppage::orderBy('stoppage_name')->get();
        $endStoppages = Stoppage::orderBy('stoppage_name')->get();
        $busRoutes = BusRoute::orderBy('route_name')->get();
        $buses = Bus::orderBy('model_name')->get();
        $drivers = Driver::orderBy('full_name')->get();
        $busHelpers = BusHelper::orderBy('bus_helper_name')->get();
        $busUsers = BusUser::orderBy('bus_user_name')->get();
        $keywords = BusScheduleKeyword::orderBy('keyword_name')->get();
        $busTypes = BusType::orderBy('bus_type_name')->get();
        $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
        $tripTimes = TripTime::orderBy('time_name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('content.bus-schedule.partials.schedule-table', compact('schedules'))->render(),
                'pagination' => $schedules->links()->render()
            ]);
        }

        return view('content.bus-schedule.index', compact(
            'schedules', 'startStoppages', 'endStoppages', 'busRoutes',
            'buses', 'drivers', 'busHelpers', 'busUsers', 'keywords', 'busTypes', 'busSubTypes', 'tripTimes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $startStoppages = Stoppage::orderBy('stoppage_name')->get();
        $endStoppages = Stoppage::orderBy('stoppage_name')->get();
        $busRoutes = BusRoute::orderBy('route_name')->get();
        $buses = Bus::orderBy('model_name')->get();
        $drivers = Driver::orderBy('full_name')->get();
        $busHelpers = BusHelper::orderBy('bus_helper_name')->get();
        $busUsers = BusUser::orderBy('bus_user_name')->get();
        $keywords = BusScheduleKeyword::orderBy('keyword_name')->get();
        $tripTimes = TripTime::orderBy('time_name')->get();
        $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
        $busTypes = BusType::orderBy('bus_type_name')->get();


        return view('content.bus-schedule.create', compact(
            'startStoppages', 'endStoppages', 'busRoutes',
            'buses', 'drivers', 'busHelpers', 'busUsers', 'keywords', 'tripTimes', 'busSubTypes', 'busTypes'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id',
            'bus_route_id' => 'required|exists:bus_routes,id',
            'trip_time_id' => 'required|exists:trip_times,id',
            'bus_id' => 'required|exists:buses,id',
            'bus_type_id' => 'nullable|exists:bus_types,id',
            'bus_sub_type_id' => 'nullable|exists:bus_sub_types,id',
            'driver_id' => 'required|exists:drivers,id',
            'assistant_id' => 'nullable|exists:assistants,id',
            'bus_user_id' => 'required|exists:bus_users,id',
            'keyword_id' => 'nullable|exists:bus_schedule_keywords,id',
            'status' => 'required|in:active,inactive,cancelled'
        ]);

        // Check for driver assignment conflicts
        // $this->checkDriverConflicts($request->driver_id, $request->trip_time_id, null, $request->bus_id);
        
        // Check for assistant assignment conflicts (if assistant is assigned)
        // if ($request->assistant_id) {
        //     $this->checkAssistantConflicts($request->assistant_id, $request->trip_time_id, null, $request->bus_id);
        // }

        try {
            DB::beginTransaction();

            $schedule = BusSchedule::create([
                'start_stoppage_id' => $request->start_stoppage_id,
                'end_stoppage_id' => $request->end_stoppage_id,
                'bus_route_id' => $request->bus_route_id,
                'trip_time_id' => $request->trip_time_id,
                'bus_id' => $request->bus_id,
                'bus_type_id' => $request->bus_type_id,
                'bus_sub_type_id' => $request->bus_sub_type_id,
                'driver_id' => $request->driver_id,
                'bus_helper_id' => $request->bus_helper_id,
                'bus_user_id' => $request->bus_user_id,
                'keyword_id' => $request->keyword_id,
                'status' => $request->status,
                'user_id' => Auth::id(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus schedule created successfully!',
                    'schedule' => $schedule->load(['startStoppage', 'endStoppage', 'busRoute', 'bus', 'driver', 'assistant', 'busUser'])
                ]);
            }

            return redirect()->route('bus-schedules.index')->with('success', 'Bus schedule created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating bus schedule: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error creating bus schedule: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BusSchedule $busSchedule)
    {
        $busSchedule->load(['startStoppage', 'endStoppage', 'busRoute', 'bus', 'driver', 'assistant', 'busUser', 'keyword']);
        
        if (request()->ajax()) {
            return response()->json([
                'schedule' => $busSchedule
            ]);
        }

        return view('content.bus-schedule.show', compact('busSchedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusSchedule $busSchedule)
    {
        $startStoppages = Stoppage::orderBy('stoppage_name')->get();
        $endStoppages = Stoppage::orderBy('stoppage_name')->get();
        $busRoutes = BusRoute::orderBy('route_name')->get();
        $buses = Bus::orderBy('model_name')->get();
        $drivers = Driver::orderBy('full_name')->get();
        $busHelpers = BusHelper::orderBy('bus_helper_name')->get();
        $busUsers = BusUser::orderBy('bus_user_name')->get();
        $keywords = BusScheduleKeyword::orderBy('keyword_name')->get();
        $tripTimes = TripTime::orderBy('time_name')->get();
        $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
        $busTypes = BusType::orderBy('bus_type_name')->get();

        return view('content.bus-schedule.edit', compact(
            'busSchedule', 'startStoppages', 'endStoppages', 'busRoutes',
            'buses', 'drivers', 'busHelpers', 'busUsers', 'keywords', 'tripTimes', 'busSubTypes', 'busTypes'
        ));
    }

    /**
     * Get trip times for AJAX requests
     */
    public function getTripTimes()
    {
        $tripTimes = TripTime::where('user_id', Auth::id())
            ->orderBy('time_name')
            ->get()
            ->map(function ($tripTime) {
                return [
                    'id' => $tripTime->id,
                    'time_name' => $tripTime->time_name,
                    'time_value' => $tripTime->time_value,
                    'time_period' => $tripTime->time_period,
                    'formatted_time' => \Carbon\Carbon::parse($tripTime->time_value)->format('H:i') . ' ' . $tripTime->time_period,
                    'display_text' => $tripTime->time_name . ' - ' . \Carbon\Carbon::parse($tripTime->time_value)->format('H:i') . ' ' . $tripTime->time_period
                ];
            });

        return response()->json($tripTimes);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusSchedule $busSchedule)
    {
        $request->validate([
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id',
            'bus_route_id' => 'required|exists:bus_routes,id',
            'trip_time_id' => 'required|exists:trip_times,id',
            'bus_id' => 'required|exists:buses,id',
            'bus_type_id' => 'nullable|exists:bus_types,id',
            'bus_sub_type_id' => 'nullable|exists:bus_sub_types,id',
            'driver_id' => 'required|exists:drivers,id',
            'bus_helper_id' => 'nullable|exists:bus_helpers,id',
            'bus_user_id' => 'required|exists:bus_users,id',
            'keyword_id' => 'nullable|exists:bus_schedule_keywords,id',
            'status' => 'required|in:active,inactive,cancelled'
        ]);

        // Check for driver assignment conflicts (exclude current schedule)
        // $this->checkDriverConflicts($request->driver_id, $request->trip_time_id, $busSchedule->id, $request->bus_id);
        
        // Check for assistant assignment conflicts (if assistant is assigned, exclude current schedule)
        // if ($request->assistant_id) {
        //     $this->checkAssistantConflicts($request->assistant_id, $request->trip_time_id, $busSchedule->id, $request->bus_id);
        // }

        try {
            DB::beginTransaction();

            $busSchedule->update([
                'start_stoppage_id' => $request->start_stoppage_id,
                'end_stoppage_id' => $request->end_stoppage_id,
                'bus_route_id' => $request->bus_route_id,
                'trip_time_id' => $request->trip_time_id,
                'bus_id' => $request->bus_id,
                'bus_type_id' => $request->bus_type_id,
                'bus_sub_type_id' => $request->bus_sub_type_id,
                'driver_id' => $request->driver_id,
                'bus_helper_id' => $request->bus_helper_id ?: null,
                'bus_user_id' => $request->bus_user_id,
                'keyword_id' => $request->keyword_id,
                'status' => $request->status,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus schedule updated successfully!',
                    'schedule' => $busSchedule->load(['startStoppage', 'endStoppage', 'busRoute', 'bus', 'driver', 'assistant', 'busUser'])
                ]);
            }

            return redirect()->route('bus-schedules.index')->with('success', 'Bus schedule updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating bus schedule: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating bus schedule: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusSchedule $busSchedule)
    {
        try {
            $busSchedule->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus schedule deleted successfully!'
                ]);
            }

            return redirect()->route('bus-schedules.index')->with('success', 'Bus schedule deleted successfully!');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting bus schedule: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error deleting bus schedule: ' . $e->getMessage());
        }
    }

    /**
     * Check for driver assignment conflicts
     */
    private function checkDriverConflicts($driverId, $tripTimeId, $excludeScheduleId = null, $currentBusId = null)
    {
        // Use raw query to avoid model accessor interference
        $query = DB::table('bus_schedules')
            ->where('driver_id', $driverId)
            ->where('status', 'active')
            ->where('trip_time_id', $tripTimeId)
            ->whereNull('deleted_at'); // Exclude soft deleted records

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        // If we have a current bus ID, only check conflicts with different buses
        if ($currentBusId) {
            $query->where('bus_id', '!=', $currentBusId);
        }

        $conflictingSchedule = $query->first();

        if ($conflictingSchedule) {
            $driver = \App\Models\Driver::find($driverId);
            $bus = \App\Models\Bus::find($conflictingSchedule->bus_id);
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['driver_id' => ["Driver {$driver->full_name} is already assigned to bus {$bus->model_name} ({$bus->registration_number}) at this time."]]
            );
        }
    }

    /**
     * Check for assistant assignment conflicts
     */
    private function checkBusHelperConflicts($busHelperId, $startTime, $excludeScheduleId = null, $currentBusId = null)
    {
        // Use raw query to avoid model accessor interference
        $query = DB::table('bus_schedules')
            ->where('bus_helper_id', $busHelperId)
            ->where('status', 'active');

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        // If we have a current bus ID, only check conflicts with different buses
        if ($currentBusId) {
            $query->where('bus_id', '!=', $currentBusId);
        }

        $conflictingSchedule = $query->first();

        if ($conflictingSchedule) {
            $busHelper = BusHelper::find($busHelperId);
            $bus = Bus::find($conflictingSchedule->bus_id);
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['bus_helper_id' => ["Bus Helper {$busHelper->bus_helper_name} is already assigned to bus {$bus->model_name} ({$bus->registration_number}) at this time."]]
            );
        }
    }

    /**
     * Check driver conflicts via AJAX
     */
    public function checkDriverConflictsAjax(Request $request)
    {
        $driverId = $request->driver_id;
        $busId = $request->bus_id;
        $tripTimeId = $request->trip_time_id;
        $scheduleId = $request->schedule_id;


        // Check if driver is already assigned to a different bus at the same time
        // Use raw query to avoid model accessor interference
        $query = DB::table('bus_schedules')
            ->where('driver_id', $driverId)
            ->where('status', 'active')
            ->where('trip_time_id', $tripTimeId)
            ->where('bus_id', '!=', $busId) // Different bus
            ->whereNull('deleted_at'); // Exclude soft deleted records

        if ($scheduleId) {
            $query->where('id', '!=', $scheduleId);
        }

        $conflictingSchedule = $query->first();


        if ($conflictingSchedule) {
            $driver = Driver::find($driverId);
            $conflictingBus = Bus::find($conflictingSchedule->bus_id);
            return response()->json([
                'has_conflicts' => true,
                'message' => "Driver {$driver->full_name} is already assigned to bus {$conflictingBus->model_name} ({$conflictingBus->registration_number}) at this time."
            ]);
        }

        return response()->json(['has_conflicts' => false]);
    }

    /**
     * Check assistant conflicts via AJAX
     */
    public function checkBusHelperConflictsAjax(Request $request)
    {
        $busHelperId = $request->bus_helper_id;
        $busId = $request->bus_id;
        $tripTimeId = $request->trip_time_id;
        $scheduleId = $request->schedule_id;

        // Check if assistant is already assigned to a different bus at the same time
        // Use raw query to avoid model accessor interference
        $query = DB::table('bus_schedules')
            ->where('bus_helper_id', $busHelperId)
            ->where('status', 'active')
            ->where('trip_time_id', $tripTimeId)
            ->where('bus_id', '!=', $busId) // Different bus
            ->whereNull('deleted_at'); // Exclude soft deleted records

        if ($scheduleId) {
            $query->where('id', '!=', $scheduleId);
        }

        $conflictingSchedule = $query->first();


        if ($conflictingSchedule) {
            $busHelper = BusHelper::find($busHelperId);
            $conflictingBus = Bus::find($conflictingSchedule->bus_id);
            return response()->json([
                'has_conflicts' => true,
                'message' => "Bus Helper {$busHelper->bus_helper_name} is already assigned to bus {$conflictingBus->model_name} ({$conflictingBus->registration_number}) at this time."
            ]);
        }

        return response()->json(['has_conflicts' => false]);
    }

    /**
     * Get schedules for a specific bus user
     */
    public function busUserSchedules(Request $request, $busUserId)
    {
        $query = BusSchedule::with([
            'startStoppage', 'endStoppage', 'busRoute', 
            'bus', 'driver', 'assistant', 'busUser'
        ])->where('bus_user_id', $busUserId);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereHas('tripTime', function($q) use ($request) {
                $q->whereTime('time_value', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('tripTime', function($q) use ($request) {
                $q->whereTime('time_value', '<=', $request->date_to);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('created_at', 'desc')->paginate(15);
        $busUser = BusUser::findOrFail($busUserId);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('content.bus-schedule.partials.bus-user-schedule-table', compact('schedules'))->render(),
                'pagination' => $schedules->links()->render()
            ]);
        }

        return view('content.bus-schedule.bus-user-schedules', compact('schedules', 'busUser'));
    }

    /**
     * Export schedules to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = BusSchedule::with([
            'startStoppage', 'endStoppage', 'busRoute', 
            'bus', 'driver', 'assistant', 'busUser'
        ]);

        // Apply same filters as index
        if ($request->filled('start_stoppage_id')) {
            $query->where('start_stoppage_id', $request->start_stoppage_id);
        }

        if ($request->filled('end_stoppage_id')) {
            $query->where('end_stoppage_id', $request->end_stoppage_id);
        }

        if ($request->filled('bus_route_id')) {
            $query->where('bus_route_id', $request->bus_route_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        if ($request->filled('bus_user_id')) {
            $query->where('bus_user_id', $request->bus_user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereHas('tripTime', function($q) use ($request) {
                $q->whereTime('time_value', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('tripTime', function($q) use ($request) {
                $q->whereTime('time_value', '<=', $request->date_to);
            });
        }

        $schedules = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('content.bus-schedule.pdf.export', compact('schedules'));
        return $pdf->download('bus-schedules-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Print schedules
     */
    public function print(Request $request)
    {
        $query = BusSchedule::with([
            'startStoppage', 'endStoppage', 'busRoute', 
            'bus', 'driver', 'assistant', 'busUser'
        ]);

        // Apply same filters as index
        if ($request->filled('start_stoppage_id')) {
            $query->where('start_stoppage_id', $request->start_stoppage_id);
        }

        if ($request->filled('end_stoppage_id')) {
            $query->where('end_stoppage_id', $request->end_stoppage_id);
        }

        if ($request->filled('bus_route_id')) {
            $query->where('bus_route_id', $request->bus_route_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        if ($request->filled('bus_user_id')) {
            $query->where('bus_user_id', $request->bus_user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereHas('tripTime', function($q) use ($request) {
                $q->whereTime('time_value', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('tripTime', function($q) use ($request) {
                $q->whereTime('time_value', '<=', $request->date_to);
            });
        }

        $schedules = $query->orderBy('created_at', 'desc')->get();

        return view('content.bus-schedule.print', compact('schedules'));
    }

    /**
     * Show the form for creating bus schedule
     */
    public function createSchedule()
    {
        $stoppages = Stoppage::orderBy('stoppage_name')->get();
        $busRoutes = BusRoute::orderBy('route_name')->get();
        $busUsers = BusUser::orderBy('bus_user_name')->get();
        
        // Get statuses related to bus-schedule
        $statuses = Status::where('related_to', 'bus-schedule')
            ->where('user_id', Auth::id())
            ->orderBy('status_name')
            ->get();
        
        // Get or create the keywords "From University" and "Toward University"
        $fromUniversityKeyword = BusScheduleKeyword::firstOrCreate(
            ['keyword_name' => 'From University', 'user_id' => Auth::id()],
            ['keyword_name' => 'From University', 'user_id' => Auth::id()]
        );
        
        $towardUniversityKeyword = BusScheduleKeyword::firstOrCreate(
            ['keyword_name' => 'Toward University', 'user_id' => Auth::id()],
            ['keyword_name' => 'Toward University', 'user_id' => Auth::id()]
        );

        $keywords = [$fromUniversityKeyword, $towardUniversityKeyword];

        return view('content.bus-schedule.bus-schedule', compact(
            'stoppages', 'busRoutes', 'busUsers', 'keywords', 'statuses'
        ));
    }

    /**
     * Store bus schedule
     */
    public function storeSchedule(Request $request)
    {
        try {
            $request->validate([
                'effective_from' => 'required|date',
                'keyword_id' => 'required|exists:bus_schedule_keywords,id',
                'status' => 'required|exists:statuses,id',
                'bus_user_id' => 'required|exists:bus_users,id',
                'remarks' => 'nullable|string|max:1000',
                'description' => 'nullable|string|max:1000',
                'schedules' => 'required|array|min:1',
                'schedules.*.start_time' => 'required|string',
                'schedules.*.starting_point_id' => 'required|exists:stoppages,id',
                'schedules.*.bus_route_id' => 'required|exists:bus_routes,id',
                'schedules.*.description' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            DB::beginTransaction();

            $effectiveFrom = $request->effective_from;
            $keywordId = $request->keyword_id;
            $statusId = $request->status;
            $busUserId = $request->bus_user_id;
            $description = $request->remarks ?? $request->description ?? null;

            // Create one parent bus schedule record
            $busSchedule = BusSchedule::create([
                'bus_schedule_keyword_id' => $keywordId,
                'status_id' => $statusId,
                'bus_user_id' => $busUserId,
                'effective_from' => $effectiveFrom,
                'description' => $description,
                'user_id' => Auth::id(),
            ]);

            // Create multiple schedule entries for this bus schedule
            foreach ($request->schedules as $index => $scheduleData) {
                // Parse start time
                $startTime = $scheduleData['start_time'] ?? null;
                
                if (!$startTime) {
                    continue; // Skip if start_time is missing
                }

                // Create schedule entry
                BusScheduleEntry::create([
                    'bus_schedule_id' => $busSchedule->id,
                    'start_time' => $startTime,
                    'starting_point_id' => $scheduleData['starting_point_id'],
                    'bus_route_id' => $scheduleData['bus_route_id'],
                    'description' => $scheduleData['description'] ?? null,
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus schedule created successfully!',
                    'redirect_url' => route('bus-schedules.schedule-index')
                ]);
            }

            return redirect()->route('bus-schedules.schedule-index')
                ->with('success', 'Bus schedule created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating bus schedule: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error creating bus schedule: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display listing of bus schedules
     */
    public function scheduleIndex(Request $request)
    {
        $query = BusSchedule::with(['keyword', 'status', 'busUser', 'entries.startingPoint', 'entries.busRoute', 'user'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->filled('keyword_id')) {
            $query->where('bus_schedule_keyword_id', $request->keyword_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('bus_user_id')) {
            $query->where('bus_user_id', $request->bus_user_id);
        }

        if ($request->filled('effective_from')) {
            $query->where('effective_from', $request->effective_from);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('keyword', function($q) use ($search) {
                    $q->where('keyword_name', 'like', "%{$search}%");
                })
                ->orWhereHas('busUser', function($q) use ($search) {
                    $q->where('bus_user_name', 'like', "%{$search}%");
                });
            });
        }

        $schedules = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter options
        $keywords = BusScheduleKeyword::where('user_id', Auth::id())->orderBy('keyword_name')->get();
        $statuses = Status::where('related_to', 'bus-schedule')
            ->where('user_id', Auth::id())
            ->orderBy('status_name')
            ->get();
        $busUsers = BusUser::orderBy('bus_user_name')->get();

        if ($request->ajax()) {
            $html = view('content.bus-schedule.partials.schedule-table', compact('schedules'))->render();
            $pagination = $schedules->hasPages() ? $schedules->links()->render() : '';
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination
            ]);
        }

        return view('content.bus-schedule.bus-schedule-index', compact(
            'schedules', 'keywords', 'statuses', 'busUsers'
        ));
    }

    /**
     * Show the form for editing bus schedule
     */
    public function editSchedule($id)
    {
        $busSchedule = BusSchedule::with(['entries'])->findOrFail($id);
        
        // Check if user owns this schedule
        if ($busSchedule->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $stoppages = Stoppage::orderBy('stoppage_name')->get();
        $busRoutes = BusRoute::orderBy('route_name')->get();
        $busUsers = BusUser::orderBy('bus_user_name')->get();
        
        // Get statuses related to bus-schedule
        $statuses = Status::where('related_to', 'bus-schedule')
            ->where('user_id', Auth::id())
            ->orderBy('status_name')
            ->get();
        
        // Get or create the keywords "From University" and "Toward University"
        $fromUniversityKeyword = BusScheduleKeyword::firstOrCreate(
            ['keyword_name' => 'From University', 'user_id' => Auth::id()],
            ['keyword_name' => 'From University', 'user_id' => Auth::id()]
        );
        
        $towardUniversityKeyword = BusScheduleKeyword::firstOrCreate(
            ['keyword_name' => 'Toward University', 'user_id' => Auth::id()],
            ['keyword_name' => 'Toward University', 'user_id' => Auth::id()]
        );

        $keywords = [$fromUniversityKeyword, $towardUniversityKeyword];

        return view('content.bus-schedule.bus-schedule-edit', compact(
            'busSchedule', 'stoppages', 'busRoutes', 'busUsers', 'keywords', 'statuses'
        ));
    }

    /**
     * Update bus schedule
     */
    public function updateSchedule(Request $request, $id)
    {
        try {
            $request->validate([
                'effective_from' => 'required|date',
                'keyword_id' => 'required|exists:bus_schedule_keywords,id',
                'status' => 'required|exists:statuses,id',
                'bus_user_id' => 'required|exists:bus_users,id',
                'remarks' => 'nullable|string|max:1000',
                'description' => 'nullable|string|max:1000',
                'schedules' => 'required|array|min:1',
                'schedules.*.start_time' => 'required|string',
                'schedules.*.starting_point_id' => 'required|exists:stoppages,id',
                'schedules.*.bus_route_id' => 'required|exists:bus_routes,id',
                'schedules.*.description' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            DB::beginTransaction();

            $busSchedule = BusSchedule::findOrFail($id);
            
            // Check if user owns this schedule
            if ($busSchedule->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access');
            }

            $effectiveFrom = $request->effective_from;
            $keywordId = $request->keyword_id;
            $statusId = $request->status;
            $busUserId = $request->bus_user_id;
            $description = $request->remarks ?? $request->description ?? null;

            // Update parent bus schedule record
            $busSchedule->update([
                'bus_schedule_keyword_id' => $keywordId,
                'status_id' => $statusId,
                'bus_user_id' => $busUserId,
                'effective_from' => $effectiveFrom,
                'description' => $description,
            ]);

            // Delete existing entries
            $busSchedule->entries()->delete();

            // Create new schedule entries
            foreach ($request->schedules as $index => $scheduleData) {
                $startTime = $scheduleData['start_time'] ?? null;
                
                if (!$startTime) {
                    continue; // Skip if start_time is missing
                }

                BusScheduleEntry::create([
                    'bus_schedule_id' => $busSchedule->id,
                    'start_time' => $startTime,
                    'starting_point_id' => $scheduleData['starting_point_id'],
                    'bus_route_id' => $scheduleData['bus_route_id'],
                    'description' => $scheduleData['description'] ?? null,
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus schedule updated successfully!',
                    'redirect_url' => route('bus-schedules.schedule-index')
                ]);
            }

            return redirect()->route('bus-schedules.schedule-index')
                ->with('success', 'Bus schedule updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating bus schedule: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating bus schedule: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * View bus schedule
     */
    public function viewSchedule($id)
    {
        $busSchedule = BusSchedule::with(['keyword', 'status', 'busUser', 'entries.startingPoint', 'entries.busRoute', 'user'])
            ->findOrFail($id);
        
        // Check if user owns this schedule
        if ($busSchedule->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'schedule' => $busSchedule
            ]);
        }

        return view('content.bus-schedule.bus-schedule-view', compact('busSchedule'));
    }

    /**
     * Print bus schedule
     */
    public function printSchedule($id)
    {
        $busSchedule = BusSchedule::with(['keyword', 'status', 'busUser', 'entries.startingPoint', 'entries.busRoute', 'user'])
            ->findOrFail($id);
        
        // Check if user owns this schedule
        if ($busSchedule->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        return view('content.bus-schedule.print-schedule', compact('busSchedule'));
    }

    /**
     * Export bus schedule to PDF
     */
    public function exportSchedulePdf($id)
    {
        $busSchedule = BusSchedule::with(['keyword', 'status', 'busUser', 'entries.startingPoint', 'entries.busRoute', 'user'])
            ->findOrFail($id);
        
        // Check if user owns this schedule
        if ($busSchedule->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $pdf = Pdf::loadView('content.bus-schedule.pdf.schedule', compact('busSchedule'));
        $filename = 'bus-schedule-' . $busSchedule->id . '-' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Delete bus schedule
     */
    public function destroySchedule($id)
    {
        try {
            $busSchedule = BusSchedule::findOrFail($id);
            
            // Check if user owns this schedule
            if ($busSchedule->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access');
            }

            // Delete entries first (cascade should handle this, but being explicit)
            $busSchedule->entries()->delete();
            
            // Delete the schedule
            $busSchedule->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus schedule deleted successfully!'
                ]);
            }

            return redirect()->route('bus-schedules.schedule-index')
                ->with('success', 'Bus schedule deleted successfully!');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting bus schedule: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error deleting bus schedule: ' . $e->getMessage());
        }
    }
}
