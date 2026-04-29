<?php

namespace App\Http\Controllers;

use App\Models\Distance;
use App\Models\Stoppage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DistanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Distance::with(['startStoppage', 'endStoppage', 'user']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('distance_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('distance_km', 'like', "%{$search}%")
                  ->orWhereHas('startStoppage', function ($stoppageQuery) use ($search) {
                      $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('endStoppage', function ($stoppageQuery) use ($search) {
                      $stoppageQuery->where('stoppage_name', 'like', "%{$search}%");
                  });
            });
        }

        // Note: Status filtering removed as status is no longer user-selectable

        // Filter by start stoppage
        if ($request->filled('start_stoppage_id')) {
            $query->where('start_stoppage_id', $request->start_stoppage_id);
        }

        // Filter by end stoppage
        if ($request->filled('end_stoppage_id')) {
            $query->where('end_stoppage_id', $request->end_stoppage_id);
        }

        // Filter by distance range
        if ($request->filled('distance_from')) {
            $query->where('distance_km', '>=', $request->distance_from);
        }

        if ($request->filled('distance_to')) {
            $query->where('distance_km', '<=', $request->distance_to);
        }

        $distances = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get filter options
        $stoppages = Stoppage::orderBy('stoppage_name')->get();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.distances.partials.table', compact('distances'))->render(),
                'pagination' => $distances->appends($request->query())->links()->toHtml()
            ]);
        }

        return view('content.distances.index', compact(
            'distances', 
            'stoppages'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stoppages = Stoppage::orderBy('stoppage_name')->get();

        return view('content.distances.create', compact('stoppages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'distance_name' => 'nullable|string|max:255',
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id|different:start_stoppage_id',
            'distance_km' => 'required|numeric|min:0.01|max:9999.99',
            'description' => 'nullable|string|max:1000'
        ]);

        // Check for duplicate start/end stoppage combination
        $existingDistance = Distance::where('start_stoppage_id', $validated['start_stoppage_id'])
            ->where('end_stoppage_id', $validated['end_stoppage_id'])
            ->first();

        if ($existingDistance) {
            return back()->withErrors([
                'start_stoppage_id' => 'A distance between these two stoppages already exists.',
                'end_stoppage_id' => 'A distance between these two stoppages already exists.'
            ])->withInput();
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'active'; // Set default status

        $distance = Distance::create($validated);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Distance created successfully.',
                'distance' => $distance
            ]);
        }

        return redirect()->route('distances.index')
            ->with('success', 'Distance created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Distance $distance)
    {
        $distance->load(['startStoppage', 'endStoppage', 'user']);
        
        return view('content.distances.show', compact('distance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Distance $distance)
    {
        $stoppages = Stoppage::orderBy('stoppage_name')->get();

        return view('content.distances.edit', compact('distance', 'stoppages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Distance $distance)
    {
        $validated = $request->validate([
            'distance_name' => 'nullable|string|max:255',
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id|different:start_stoppage_id',
            'distance_km' => 'required|numeric|min:0.01|max:9999.99',
            'description' => 'nullable|string|max:1000'
        ]);

        // Check for duplicate start/end stoppage combination (excluding current record)
        $existingDistance = Distance::where('start_stoppage_id', $validated['start_stoppage_id'])
            ->where('end_stoppage_id', $validated['end_stoppage_id'])
            ->where('id', '!=', $distance->id)
            ->first();

        if ($existingDistance) {
            return back()->withErrors([
                'start_stoppage_id' => 'A distance between these two stoppages already exists.',
                'end_stoppage_id' => 'A distance between these two stoppages already exists.'
            ])->withInput();
        }

        $distance->update($validated);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Distance updated successfully.',
                'distance' => $distance
            ]);
        }

        return redirect()->route('distances.index')
            ->with('success', 'Distance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Distance $distance)
    {
        $distance->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Distance deleted successfully.'
            ]);
        }

        return redirect()->route('distances.index')
            ->with('success', 'Distance deleted successfully.');
    }

    /**
     * Get distance between two stoppages
     */
    public function getDistance(Request $request)
    {
        $request->validate([
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id'
        ]);

        $startStoppageId = $request->start_stoppage_id;
        $endStoppageId = $request->end_stoppage_id;

        // Try to find distance in both directions
        $distance = Distance::where(function($query) use ($startStoppageId, $endStoppageId) {
            $query->where('start_stoppage_id', $startStoppageId)
                  ->where('end_stoppage_id', $endStoppageId);
        })->orWhere(function($query) use ($startStoppageId, $endStoppageId) {
            $query->where('start_stoppage_id', $endStoppageId)
                  ->where('end_stoppage_id', $startStoppageId);
        })->where('status', 'active')->first();

        if ($distance) {
            return response()->json([
                'success' => true,
                'distance_km' => $distance->distance_km
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Distance not found between these stoppages.'
        ], 404);
    }
}
