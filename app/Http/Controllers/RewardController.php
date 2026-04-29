<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\BusType;
use App\Models\BusSubType;
use App\Models\RewardType;
use App\Models\BusHelper;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Reward::with(['bus','busSubType','user', 'rewardType']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('bus', function ($busQuery) use ($search) {
                      $busQuery->where('registration_number', 'like', "%{$search}%")
                                  ->orWhere('model_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('driver', function ($driverQuery) use ($search) {
                      $driverQuery->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by reward type
        if ($request->filled('reward_type_id')) {
            $query->where('reward_type_id', $request->reward_type_id);
        }

        // Filter by bus
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        // Filter by bus type
        if ($request->filled('bus_type_id')) {
            $query->whereHas('bus', function ($busQuery) use ($request) {
                $busQuery->where('bus_type_id', $request->bus_type_id);
            });
        }

        // Filter by bus sub type
        if ($request->filled('bus_sub_type_id')) {
            $query->whereHas('bus', function ($busQuery) use ($request) {
                $busQuery->where('bus_sub_type_id', $request->bus_sub_type_id);
            });
        }

        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('reward_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('reward_date', '<=', $request->date_to);
        }

        $rewards = $query->orderBy('reward_date', 'desc')->paginate(15);

        // Calculate total for current page
        $pageTotal = $rewards->sum('reward_amount');

        // Get filter options
        $buses = Bus::all();
        $busTypes = BusType::orderBy('bus_type_name', 'asc')->get();
        $busSubTypes = BusSubType::orderBy('sub_type_name', 'asc')->get();
        $drivers = Driver::orderBy('full_name', 'asc')->get();
        $rewardTypes = RewardType::orderBy('name', 'asc')->get();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.rewards.partials.table', compact('rewards', 'pageTotal'))->render(),
                'pagination' => $rewards->links()->render(),
                'count' => $rewards->total()
            ]);
        }

        return view('content.rewards.index', compact(
            'rewards',
            'buses',
            'rewardTypes',
            'busTypes',
            'busSubTypes',
            'pageTotal'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buses = Bus::all();
        $users = User::all();
        $busSubTypes = BusSubType::all();
        $rewardTypes = RewardType::orderBy('name', 'asc')->get();
        $witness_employees = Employee::all();
        

        return view('content.rewards.create', compact(
            'buses',
            'busSubTypes',
            'rewardTypes',
            'witness_employees',
        ));
    }

    /**
     * Validate reward data via AJAX
     */
    public function validateReward(Request $request)
    {
        try {
            $rules = [
                'bus_id' => 'required|exists:buses,id',
                'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
                'witness_employee_id' => 'nullable|exists:employees,id',
                'reward_amount' => 'required|numeric|min:0.01',
                'reward_date' => 'required|date',
                'reason' => 'required|string|min:10',
                'reward_type_id' => 'nullable|exists:reward_types,id',
                'remarks' => 'nullable|string',
            ];

            // Only validate document if file is present
            if ($request->hasFile('document')) {
                $rules['document'] = 'file|mimes:pdf,jpg,jpeg,png|max:5120';
            }

            $request->validate($rules);

            return response()->json([
                'success' => true,
                'message' => 'Validation passed'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'bus_id' => 'required|exists:buses,id',
                'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
                'reward_amount' => 'required|numeric|min:0',
                'reward_date' => 'required|date',
                'reason' => 'required|string',
                'witness_employee_id' => 'nullable|exists:employees,id',
                'reward_type_id' => 'nullable|exists:reward_types,id',
                'remarks' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            ]);

            // Handle document upload
            if ($request->hasFile('document')) {
                // Create directory if it doesn't exist
                $directory = public_path('reward_documents');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($directory, $filename);
                $validated['document'] = $filename;
            }

            $validated['user_id'] = Auth::id();

            $reward = Reward::create($validated);

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reward record created successfully.',
                    'data' => $reward
                ], 201);
            }

            return redirect()->route('rewards.index')
                ->with('success', 'Reward record created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reward $reward)
    {
        $reward->load(['bus', 'driver', 'user', 'rewardType']);

        return view('content.rewards.show', compact('reward'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reward $reward)
    {
        $buses = Bus::all();
        $busSubTypes = BusSubType::all();
        $rewardTypes = RewardType::orderBy('name', 'asc')->get();
        $witness_employees = Employee::all();

        return view('content.rewards.edit', compact(
            'reward',
            'buses',
            'busSubTypes',
            'rewardTypes',
            'witness_employees'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reward $reward)
    {
        try {
            $validated = $request->validate([
                'bus_id' => 'required|exists:buses,id',
                'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
                'witness_employee_id' => 'nullable|exists:employees,id',
                'reward_amount' => 'required|numeric|min:0',
                'reward_date' => 'required|date',
                'reason' => 'required|string',
                'reward_type_id' => 'nullable|exists:reward_types,id',
                'remarks' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            ]);

            // Handle document upload
            if ($request->hasFile('document')) {
                // Create directory if it doesn't exist
                $directory = public_path('reward_documents');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Delete old document if exists
                if ($reward->document && file_exists(public_path('reward_documents/' . $reward->document))) {
                    unlink(public_path('reward_documents/' . $reward->document));
                }
                
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($directory, $filename);
                $validated['document'] = $filename;
            }

            $reward->update($validated);

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reward record updated successfully.',
                    'data' => $reward
                ], 200);
            }

            return redirect()->route('rewards.index')
                ->with('success', 'Reward record updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Reward $reward)
    {
        try {
            // Delete document if exists
            if ($reward->document && file_exists(public_path('reward_documents/' . $reward->document))) {
                unlink(public_path('reward_documents/' . $reward->document));
            }
            
            $reward->delete();

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reward record deleted successfully.'
                ], 200);
            }

            return redirect()->route('rewards.index')
                ->with('success', 'Reward record deleted successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the reward: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('rewards.index')
                ->with('error', 'An error occurred while deleting the reward.');
        }
    }
}
