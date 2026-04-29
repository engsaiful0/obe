<?php

namespace App\Http\Controllers;

use App\Models\DailyBusList;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\BusHelper;
use App\Models\Status as StatusModel;
use App\Models\Stoppage;
use App\Models\BusType;
use App\Models\BusSubType;
use App\Models\TripTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

use App\Exports\AllBusesListExport;



class DailyBusListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('content.daily-bus-lists.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();
        $buses = Bus::where('status_id', $busActiveStatus->id)->get();

        $stoppages = Stoppage::orderBy('stoppage_name')->get();
        $busTypes = BusType::orderBy('bus_type_name')->get();
        $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
        $tripTimes = TripTime::orderBy('time_name')->get();
        return view('content.daily-bus-lists.create', compact('buses', 'stoppages', 'busTypes', 'busSubTypes', 'tripTimes'));
    }
    public function create_all_buses_list(Request $request)
    {
        try {
            $buses = collect();
            $stoppages = Stoppage::orderBy('stoppage_name')->get();
            $busTypes = BusType::orderBy('bus_type_name')->get();
            $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
            $tripTimes = TripTime::orderBy('time_name')->get();

            // If sub_type_id is provided, load buses for that sub type
            if ($request->has('sub_type_id') && $request->sub_type_id) {
                $buses = Bus::where('bus_sub_type_id', $request->sub_type_id)
                    ->where('status', Bus::STATUS_ACTIVE)
                    ->get();
            }

            return view('content.daily-bus-lists.create_all_buses_list', compact(
                'buses',
                'stoppages',
                'busSubTypes',
                'tripTimes'
            ));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Step 1: Validate the incoming data
        $request->validate([
            'list_date' => ['required', 'date'],
            'bus_id' => ['required', 'exists:buses,id'],
            'start_stoppage_id' => ['required', 'exists:stoppages,id'],
            'end_stoppage_id' => ['required', 'exists:stoppages,id'],
            'trip_time_id' => ['required', 'exists:trip_times,id'],
            'bus_sub_type_id' => ['nullable', 'exists:bus_sub_types,id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'bus_id.required' => 'Bus selection is required.',
            'start_stoppage_id.required' => 'Start stoppage must be selected.',
            'end_stoppage_id.required' => 'End stoppage must be selected.',
            'trip_time_id.required' => 'Trip time must be selected.',
        ]);

        DB::beginTransaction();
        try {
            // Step 2: Prepare clean data array
            $data = [
                'list_date' => $request->list_date,
                'bus_id' => $request->bus_id,
                'start_stoppage_id' => $request->start_stoppage_id,
                'end_stoppage_id' => $request->end_stoppage_id,
                'trip_time_id' => $request->trip_time_id,
                'bus_sub_type_id' => $request->bus_sub_type_id ?? null,
                'remarks' => $request->remarks ?? null,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Step 3: Create record
            $dailyBusList = DailyBusList::create($data);

            // Step 4: Commit transaction
            DB::commit();

            // Step 5: Prepare success response
            $response = [
                'success' => true,
                'message' => 'Daily bus list created successfully.',
                'data' => $dailyBusList->load([
                    'bus:id,model_name,registration_number',
                    'startStoppage:id,stoppage_name',
                    'endStoppage:id,stoppage_name',
                    'busSubType:id,sub_type_name'
                ]),
            ];

            // Step 6: Return JSON for AJAX or redirect for normal request
            if ($request->ajax()) {
                return response()->json($response, 201);
            }

            return redirect()
                ->route('daily-bus-lists.index')
                ->with('success', $response['message']);
        } catch (\Throwable $e) {
            DB::rollBack();

            // Log the exception for debugging
            Log::error('Error creating daily bus list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving the record. Please try again later.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }


    /**
     * Store multiple bus list entries
     */
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'list_date' => 'required|date',
            'buses' => 'required|array|min:1',
            'buses.*.bus_id' => 'required|exists:buses,id',
            'buses.*.start_stoppage_id' => 'required|exists:stoppages,id',
            'buses.*.end_stoppage_id' => 'required|exists:stoppages,id',
            'buses.*.trip_time_id' => 'required|exists:trip_times,id',
            'buses.*.bus_sub_type_id' => 'nullable|exists:bus_sub_types,id',
            'buses.*.remarks' => 'nullable|string|max:1000',
        ]);

        $createdEntries = [];
        $userId = Auth::id();

        DB::beginTransaction();
        try {
            foreach ($request->buses as $busData) {
                $data = $busData;
                $data['list_date'] = $request->list_date;
                $data['user_id'] = $userId;

                $dailyBusList = DailyBusList::create($data);
                $createdEntries[] = $dailyBusList->load(['bus', 'startStoppage', 'endStoppage', 'busSubType']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdEntries) . ' bus list entries created successfully.',
                'data' => $createdEntries
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating bus list entries: ' . $e->getMessage()
            ], 500);
        }
    }
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
    public function getBusesBySubType(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            if ($request->sub_type_id == 'all' || empty($request->sub_type_id)) {
                $buses = Bus::with('busSubType')
                    ->where('status', Bus::STATUS_ACTIVE)
                    ->get();
            } else {
                $buses = Bus::with('busSubType')
                    ->where('bus_sub_type_id', $request->sub_type_id)
                    ->where('status', Bus::STATUS_ACTIVE)
                    ->get();
            }

            // Get stoppages for the dropdowns
            $stoppages = Stoppage::where('user_id', Auth::id())->get();

            // Return HTML response
            $html = view('content.daily-bus-lists.partials.bus-rows', compact('buses', 'stoppages'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error loading buses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyBusList $dailyBusList)
    {
        $dailyBusList->load(['bus', 'startStoppage', 'endStoppage', 'busSubType']);
        return view('content.daily-bus-lists.show', compact('dailyBusList'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyBusList $dailyBusList)
    {
        $dailyBusList->load(['bus', 'startStoppage', 'endStoppage', 'busSubType', 'tripTime']);
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();
        $buses = Bus::where('status_id', $busActiveStatus->id)->get();

        $stoppages = Stoppage::orderBy('stoppage_name')->get();
        $busTypes = BusType::orderBy('bus_type_name')->get();
        $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
        $tripTimes = TripTime::orderBy('time_name')->get();

        $busSubTypes = BusSubType::all();
        return view('content.daily-bus-lists.edit', compact('dailyBusList', 'buses', 'stoppages', 'busTypes', 'busSubTypes', 'tripTimes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyBusList $dailyBusList)
    {
        $request->validate([
            'list_date' => 'required|date',
            'bus_id' => 'required|exists:buses,id',
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id',
            'trip_time_id' => 'required|exists:trip_times,id',
            'bus_sub_type_id' => 'nullable|exists:bus_sub_types,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $dailyBusList->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Daily bus list updated successfully.',
                'data' => $dailyBusList->load(['bus', 'startStoppage', 'endStoppage', 'busSubType', 'tripTime'])
            ]);
        }

        return redirect()->route('daily-bus-lists.index')
            ->with('success', 'Daily bus list updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyBusList $dailyBusList)
    {
        $dailyBusList->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Daily bus list deleted successfully.'
            ]);
        }

        return redirect()->route('daily-bus-lists.index')
            ->with('success', 'Daily bus list deleted successfully.');
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        $query = DailyBusList::with([
            'bus',
            'startStoppage',
            'endStoppage',
            'busSubType',
            'tripTime'
        ])->forUser(Auth::id());

        // Apply filters
        if ($request->filled('date')) {
            $query->forDate($request->date);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }



        if ($request->filled('bus_id')) {
            $query->forBus($request->bus_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('bus', function ($busQuery) use ($search) {
                    $busQuery->where('model_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('startStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('endStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    });
            });
        }

        $dailyBusLists = $query->orderBy('list_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $dailyBusLists
        ]);
    }

    /**
     * Get filter options for dropdowns
     */
    public function getFilterOptions()
    {
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();
        $buses = Bus::where('status_id', $busActiveStatus->id)
            ->where('status_id', $busActiveStatus->id)
            ->get(['id', 'bus_number']);


        $stoppages = Stoppage::where('user_id', Auth::id())
            ->get(['id', 'stoppage_name']);

        $busSubTypes = BusSubType::where('user_id', Auth::id())
            ->get(['id', 'sub_type_name']);

        return response()->json([
            'buses' => $buses,
            'stoppages' => $stoppages,
            'bus_sub_types' => $busSubTypes,
        ]);
    }

    /**
     * Get last saved data for a specific date
     */
    public function getLastSavedData(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $lastSavedData = DailyBusList::with([
            'bus',
            'startStoppage',
            'endStoppage',
            'busSubType'
        ])
            ->forUser(Auth::id())
            ->forDate($request->date)
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'data' => $lastSavedData
        ]);
    }

    /**
     * Check if bus data exists for a specific date
     */
    public function checkBusData(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $exists = DailyBusList::forUser(Auth::id())
            ->forDate($request->date)
            ->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    /**
     * Display all buses list with filtering
     */
    public function allBusesList(Request $request)
    {
        $query = DailyBusList::with([
            'bus',
            'startStoppage',
            'endStoppage',
            'busSubType'
        ]);

        // Apply filters

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }



        if ($request->filled('bus_id')) {
            $query->forBus($request->bus_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('bus', function ($busQuery) use ($search) {
                    $busQuery->where('model_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('startStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('endStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    });
            });
        }

        $dailyBusLists = $query->orderBy('list_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Get filter options
        $buses = Bus::where('status', Bus::STATUS_ACTIVE)
            ->get();


        $stoppages = Stoppage::all();

        $busSubTypes = BusSubType::all();

        return view('content.daily-bus-lists.all-buses-list', compact(
            'dailyBusLists',
            'buses',
            'stoppages',
            'busSubTypes'
        ));
    }

    /**
     * Get filtered data for AJAX requests
     */
    public function getFilteredData(Request $request)
    {
        try {
            $query = DailyBusList::with([
                'bus',
                'startStoppage',
                'endStoppage',
                'busSubType'
            ])->forUser(Auth::id());

            // Apply filters


            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->dateRange($request->date_from, $request->date_to);
            }

            if ($request->filled('bus_sub_type_id')) {
                $query->where('bus_sub_type_id', $request->bus_sub_type_id);
            }


            if ($request->filled('bus_id')) {
                $query->forBus($request->bus_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('bus', function ($busQuery) use ($search) {
                        $busQuery->where('model_name', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%");
                    })
                        ->orWhereHas('startStoppage', function ($stoppageQuery) use ($search) {
                            $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('endStoppage', function ($stoppageQuery) use ($search) {
                            $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                        });
                });
            }

            $dailyBusLists = $query->orderBy('list_date', 'desc')
                ->orderBy('start_time', 'asc')
                ->paginate(15);

            // Generate HTML for table rows
            $html = view('content.daily-bus-lists.partials.table-rows', compact('dailyBusLists'))->render();

            // Generate pagination HTML
            $paginationHtml = $dailyBusLists->links()->toHtml();

            // Generate results summary
            $summaryHtml = view('content.daily-bus-lists.partials.results-summary', compact('dailyBusLists'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $paginationHtml,
                'summary' => $summaryHtml,
                'total' => $dailyBusLists->total(),
                'current_page' => $dailyBusLists->currentPage(),
                'last_page' => $dailyBusLists->lastPage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all buses data for DataTables with filtering
     */
    public function getAllBusesData(Request $request)
    {
        $query = DailyBusList::with([
            'bus',
            'startStoppage',
            'endStoppage',
            'busSubType'
        ])->forUser(Auth::id());

        // Apply filters
        if ($request->filled('date')) {
            $query->forDate($request->date);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }



        if ($request->filled('bus_id')) {
            $query->forBus($request->bus_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('bus', function ($busQuery) use ($search) {
                    $busQuery->where('model_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('startStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('endStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    });
            });
        }

        $dailyBusLists = $query->orderBy('list_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'data' => $dailyBusLists
        ]);
    }

    /**
     * Get filter options for all buses list
     */
    public function getAllBusesFilterOptions()
    {
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();
        $buses = Bus::where('status_id', $busActiveStatus->id)
            ->get(['id', 'bus_number']);


        $stoppages = Stoppage::where('user_id', Auth::id())
            ->get(['id', 'stoppage_name']);

        $busSubTypes = BusSubType::where('user_id', Auth::id())
            ->get(['id', 'sub_type_name']);

        return response()->json([
            'buses' => $buses,
            'stoppages' => $stoppages,
            'bus_sub_types' => $busSubTypes,
        ]);
    }

    /**
     * Export filtered data to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = DailyBusList::with([
            'bus',
            'startStoppage',
            'endStoppage',
            'busSubType'
        ])->forUser(Auth::id());

        // Apply filters
        if ($request->filled('date')) {
            $query->forDate($request->date);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }



        if ($request->filled('bus_id')) {
            $query->forBus($request->bus_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('bus', function ($busQuery) use ($search) {
                    $busQuery->where('model_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('startStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('endStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    });
            });
        }

        $dailyBusLists = $query->orderBy('list_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        $pdf = Pdf::loadView('content.daily-bus-lists.pdf', [
            'dailyBusLists' => $dailyBusLists,
            'filters' => $request->all()
        ]);

        return $pdf->download('all-buses-list-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export filtered data to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = DailyBusList::with([
            'bus',
            'startStoppage',
            'endStoppage',
            'busSubType'
        ])->forUser(Auth::id());

        // Apply filters
        if ($request->filled('date')) {
            $query->forDate($request->date);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }



        if ($request->filled('bus_id')) {
            $query->forBus($request->bus_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('bus', function ($busQuery) use ($search) {
                    $busQuery->where('model_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('startStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('endStoppage', function ($stoppageQuery) use ($search) {
                        $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                    });
            });
        }

        $dailyBusLists = $query->orderBy('list_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        return Excel::download(new AllBusesListExport($dailyBusLists), 'all-buses-list-' . now()->format('Y-m-d') . '.xlsx');
    }
}
