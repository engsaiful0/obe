<?php

namespace App\Http\Controllers;


use App\Models\Stoppage;
use App\Models\BusSubType;
use App\Models\Bus;
use App\Models\Status as StatusModel;
use App\Models\DeploymentType as DeploymentTypeModel;
use App\Models\TripTime;
use App\Models\BusUser;
use App\Models\DeploymentPlan;
use App\Models\DeploymentPlanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class DeploymentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Build query with relationships
        $query = DeploymentPlan::with([
            'tripTime',
            'busUser',
            'user',
            'items.stoppage',
            'items.busSubType',
            'items.bus'
        ]);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('deployment_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('deployment_date', '<=', $request->date_to);
        }

        if ($request->filled('trip_time_id')) {
            $query->where('trip_time_id', (int)$request->trip_time_id);
        }
        if ($request->filled('deployment_type_id')) {
            $query->where('deployment_type_id', (int)$request->deployment_type_id);
        }
        if ($request->filled('trip_type')) {
            $query->where('trip_type', $request->trip_type);
        }

        if ($request->filled('bus_user_id')) {
            $busUserId = (int)$request->bus_user_id;
            $query->where('bus_user_id', $busUserId);
        }

        // Paginate results
        $perPage = $request->get('per_page', 50);
        $plans = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // Set pagination path
        $plans->setPath('/app/deployment-plans/view-daily-deployment-plan');
        $plans->appends($request->except('page'));

        // Get filter options
        $tripTimes = TripTime::orderBy('time_name')->get();
        $busUsers = BusUser::orderBy('bus_user_name')->get();
        $deploymentTypes = DeploymentTypeModel::orderBy('deployment_type_name')->get();
        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('content.daily-deployment-plans.partials.table', compact('plans'))->render(),
                'pagination' => $plans->links()->toHtml(),
                'total' => $plans->total(),
            ]);
        }

        // Return view for non-AJAX requests
        return view('content.daily-deployment-plans.index', compact('plans', 'tripTimes', 'busUsers', 'deploymentTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stoppages = Stoppage::where('status', Stoppage::STATUS_ACTIVE)->orderBy('stoppage_name')->get();
       
        $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
        $tripTimes = TripTime::orderBy('time_name')->get();
        $deploymentTypes = DeploymentTypeModel::orderBy('deployment_type_name')->get();
        //dd($tripTimes);
        $busUsers = BusUser::orderBy('bus_user_name')->get();
        
        
        // Get active status ID for buses
        $activeStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();
        
        if ($activeStatus) {
            // Get buses with active status, grouped by sub-type
            $busesBySubType = Bus::where('status_id', $activeStatus->id)
                ->with('busSubType')
                ->get()
                ->groupBy('bus_sub_type_id');
            
            // Get buses by sub-type for specific use cases
            $ownBus = Bus::where('status_id', $activeStatus->id)
                ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
                ->get();
            
            $brtcBuses = Bus::where('status_id', $activeStatus->id)
                ->where('bus_sub_type_id', BusSubType::BRTC_BUS_SUB_TYPE_ID)
                ->get();
            
            $hiredBus = Bus::where('status_id', $activeStatus->id)
                ->where('bus_sub_type_id', BusSubType::HIRED_BUS_SUB_TYPE_ID)
                ->get();
        } else {
            // If no active status found, return empty collections
            $busesBySubType = collect();
            $ownBus = collect();
            $brtcBuses = collect();
            $hiredBus = collect();
        }

        return view('content.daily-deployment-plans.create', compact(
            'stoppages',
            'busSubTypes',
            'deploymentTypes',
            'tripTimes',
            'busUsers',
            'busesBySubType',
            'ownBus',
            'brtcBuses',
            'hiredBus'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handle JSON items if sent as string
        $items = $request->items;
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        // Normalize bus_assignments from associative array to sequential array for validation
        if (is_array($items)) {
            foreach ($items as &$item) {
                if (isset($item['bus_assignments']) && is_array($item['bus_assignments'])) {
                    // Check if it's an associative array (not sequential)
                    $isAssociative = !isset($item['bus_assignments'][0]) && !empty($item['bus_assignments']);
                    if ($isAssociative) {
                        // Convert associative array to sequential array
                        $normalizedAssignments = [];
                        foreach ($item['bus_assignments'] as $key => $assignment) {
                            if (is_array($assignment)) {
                                $normalizedAssignments[] = $assignment;
                            } else {
                                // If value is not an array, create one
                                $normalizedAssignments[] = [
                                    'bus_sub_type_id' => is_numeric($key) ? (int)$key : $key,
                                    'bus_id' => $assignment
                                ];
                            }
                        }
                        $item['bus_assignments'] = $normalizedAssignments;
                    }
                }
            }
            $request->merge(['items' => $items]);
        }

        $validated = $request->validate([
            'deployment_date' => ['required', 'date'],
            'trip_time_id' => ['required', 'exists:trip_times,id'],
            'bus_user_id' => ['required', 'exists:bus_users,id'],
            'deployment_type_id' => ['required', 'exists:deployment_types,id'],
            'trip_type' => ['required', 'in:arrival,departure'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.stoppage_id' => ['required', 'exists:stoppages,id'],
            'items.*.bus_assignments' => ['required', 'array'],
            'items.*.bus_assignments.*.bus_sub_type_id' => ['required', 'exists:bus_sub_types,id'],
            // bus_id may be a single value or an array of IDs; handle both in code
            'items.*.bus_assignments.*.bus_id' => ['nullable'],
            'items.*.bus_assignments.*.bus_id.*' => ['nullable', 'exists:buses,id'],
        ], [
            'deployment_date.required' => 'Deployment date is required.',
            'trip_time_id.required' => 'Trip time must be selected.',
            'bus_user_id.required' => 'Bus user must be selected.',
            'deployment_type_id.required' => 'Deployment type must be selected.',
            'deployment_type_id.exists' => 'Selected deployment type is invalid.',
            'trip_type.required' => 'Trip type must be selected.',
            'trip_type.arrival' => 'Trip type must be either "arrival" or "departure".',
            'items.required' => 'At least one stoppage must be added.',
            'items.*.stoppage_id.required' => 'Stoppage is required for each row.',
        ]);

        DB::beginTransaction();
        try {
            // Check for duplicate plan (same date, trip time, bus user, deployment type, and trip type)
            $existingPlan = DeploymentPlan::where('deployment_date', $validated['deployment_date'])
                ->where('trip_time_id', $validated['trip_time_id'])
                ->where('bus_user_id', $validated['bus_user_id'])
                ->where('deployment_type_id', $validated['deployment_type_id'])
                ->where('trip_type', $validated['trip_type'])
                ->first();

            if ($existingPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'A deployment plan already exists for this date, trip time, bus user, deployment type, and trip type combination.',
                ], 422);
            }

            // Create the deployment plan
            $plan = DeploymentPlan::create([
                'deployment_date' => $validated['deployment_date'],
                'trip_time_id' => $validated['trip_time_id'],
                'bus_user_id' => $validated['bus_user_id'],
                'deployment_type_id' => $validated['deployment_type_id'],
                'trip_type' => $validated['trip_type'],
                'user_id' => Auth::id(),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // Create items for each stoppage - save one row per selected bus
            $createdKeys = []; // dedupe keys: stoppage_id::bus_sub_type_id::bus_id
            foreach ($validated['items'] as $item) {
                $stoppageId = $item['stoppage_id'];
                $busAssignments = $item['bus_assignments'];

                // Helper closure to avoid duplicate records
                $createItem = function ($stoppageId, $subTypeId, $busId) use ($plan, &$createdKeys) {
                    if (empty($subTypeId) || empty($busId)) {
                        return;
                    }

                    $key = $stoppageId . '::' . $subTypeId . '::' . $busId;
                    if (isset($createdKeys[$key])) {
                        return; // skip duplicate
                    }
                    $createdKeys[$key] = true;

                    DeploymentPlanItem::create([
                        'deployment_plan_id' => $plan->id,
                        'stoppage_id' => $stoppageId,
                        'bus_sub_type_id' => $subTypeId,
                        'bus_id' => $busId,
                    ]);
                };

                if (isset($busAssignments[0])) {
                    // Sequential array: each $assignment contains bus_sub_type_id and bus_id (array or single)
                    foreach ($busAssignments as $assignment) {
                        $subTypeId = $assignment['bus_sub_type_id'] ?? null;
                        $busIds = $assignment['bus_id'] ?? [];

                        if (is_array($busIds)) {
                            foreach ($busIds as $busId) {
                                $createItem($stoppageId, $subTypeId, $busId);
                            }
                        } else {
                            $createItem($stoppageId, $subTypeId, $busIds);
                        }
                    }
                } else {
                    // Associative array (keyed by sub_type_id)
                    foreach ($busAssignments as $subTypeKey => $assignment) {
                        if (!is_array($assignment)) {
                            continue;
                        }

                        $subTypeId = $assignment['bus_sub_type_id'] ?? $subTypeKey;
                        $busIds = $assignment['bus_id'] ?? [];

                        if (is_array($busIds)) {
                            foreach ($busIds as $busId) {
                                $createItem($stoppageId, $subTypeId, $busId);
                            }
                        } else {
                            $createItem($stoppageId, $subTypeId, $busIds);
                        }
                    }
                }
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Daily deployment plan created successfully.',
                'data' => $plan->load(['tripTime', 'busUser', 'items.stoppage', 'items.busSubType', 'items.bus']),
            ];

            // Always return JSON for AJAX requests
            return response()->json($response, 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating daily deployment plan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Always return JSON for AJAX requests
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the deployment plan. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DeploymentPlan $deploymentPlan)
    {
        $deploymentPlan->load([
            'tripTime',
            'busUser',
            'deploymentType',
            'user',
            'items.stoppage',
            'items.busSubType',
            'items.bus'
        ]);

        return view('content.daily-deployment-plans.show', compact('deploymentPlan'));
    }

    /**
     * Export deployment plan as PDF
     */
    public function pdf(DeploymentPlan $deploymentPlan)
{
    $deploymentPlan->load([
        'tripTime',
        'busUser',
        'deploymentType',
        'user',
        'items.stoppage',
        'items.busSubType',
        'items.bus'
    ]);

    $allBusSubTypes = BusSubType::orderBy('sub_type_name')->get();
    $itemsByStoppage = $deploymentPlan->items->groupBy('stoppage_id');

    $pdf = Pdf::loadView('content.daily-deployment-plans.pdf', compact(
        'deploymentPlan',
        'allBusSubTypes',
        'itemsByStoppage'
    ));

    $pdf->setPaper('A4', 'landscape');

    // Required for loading local fonts
    $pdf->setOption('enable-local-file-access', true);
    $pdf->setOption('isHtml5ParserEnabled', true);
    $pdf->setOption('isRemoteEnabled', false);

    $filename = 'deployment-plan-' .
        $deploymentPlan->deployment_date->format('Y-m-d') .
        '-' .
        $deploymentPlan->id .
        '.pdf';

    return $pdf->download($filename);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeploymentPlan $dailyDeploymentPlan)
    {
        $dailyDeploymentPlan->load(['items.stoppage', 'items.busSubType', 'items.bus']);
        
        $stoppages = Stoppage::orderBy('stoppage_name')->get();
        $busSubTypes = BusSubType::orderBy('sub_type_name')->get();
        $tripTimes = TripTime::orderBy('time_name')->get();
        $busUsers = BusUser::orderBy('bus_user_name')->get();
        $deploymentTypes = DeploymentTypeModel::orderBy('deployment_type_name')->get();
        
        // Get active status ID for buses
        $activeStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'active')
            ->where('user_id', Auth::id())
            ->first();
        
        // Get buses grouped by sub-type with active status
        if ($activeStatus) {
            // Get buses with active status, grouped by sub-type
            $busesBySubType = Bus::where('status_id', $activeStatus->id)
                ->with('busSubType')
                ->get()
                ->groupBy('bus_sub_type_id');
            
            // Get buses by sub-type for specific use cases
            $ownBus = Bus::where('status_id', $activeStatus->id)
                ->where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
                ->get();
            
            $brtcBuses = Bus::where('status_id', $activeStatus->id)
                ->where('bus_sub_type_id', BusSubType::BRTC_BUS_SUB_TYPE_ID)
                ->get();
            
            $hiredBus = Bus::where('status_id', $activeStatus->id)
                ->where('bus_sub_type_id', BusSubType::HIRED_BUS_SUB_TYPE_ID)
                ->get();
        } else {
            // If no active status found, return empty collections
            $busesBySubType = collect();
            $ownBus = collect();
            $brtcBuses = collect();
            $hiredBus = collect();
        }

        return view('content.daily-deployment-plans.edit', compact(
            'dailyDeploymentPlan',
            'stoppages',
            'busSubTypes',
            'deploymentTypes',
            'tripTimes',
            'busUsers',
            'busesBySubType',
            'ownBus',
            'brtcBuses',
            'hiredBus'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeploymentPlan $deploymentPlan)
    {
        // Use input() method to ensure we get the data correctly
        // This handles both form data and JSON requests
        $deploymentDate = $request->input('deployment_date');
        $tripTimeId = $request->input('trip_time_id');
        $busUserId = $request->input('bus_user_id');
        $deploymentTypeId = $request->input('deployment_type_id');
        $tripType = $request->input('trip_type');
        $remarks = $request->input('remarks');
        
        // Handle JSON items if sent as string
        $items = $request->input('items');
        if (is_string($items)) {
            $items = json_decode($items, true);
        }
        
        // If data is missing, try to get from request directly
        if (empty($deploymentDate)) {
            $deploymentDate = $request->deployment_date;
        }
        if (empty($tripTimeId)) {
            $tripTimeId = $request->trip_time_id;
        }
        if (empty($busUserId)) {
            $busUserId = $request->bus_user_id;
        }
        if (empty($deploymentTypeId)) {
            $deploymentTypeId = $request->deployment_type_id;
        }
        if (empty($tripType)) {
            $tripType = $request->trip_type;
        }
        
        // Debug: Log request data (remove in production)
    Log::error('Update Request Data', [
            'all' => $request->all(),
            'input_method' => [
                'deployment_date' => $deploymentDate,
                'trip_time_id' => $tripTimeId,
                'bus_user_id' => $busUserId,
                'deployment_type_id' => $deploymentTypeId,
                'trip_type' => $tripType,
                'items' => $items,
            ],
            'direct_access' => [
                'deployment_date' => $request->deployment_date,
                'trip_time_id' => $request->trip_time_id,
            ],
        ]);

        // Normalize bus_assignments from associative array to sequential array for validation
        if (is_array($items)) {
            foreach ($items as &$item) {
                if (isset($item['bus_assignments']) && is_array($item['bus_assignments'])) {
                    // Check if it's an associative array (not sequential)
                    $isAssociative = !isset($item['bus_assignments'][0]) && !empty($item['bus_assignments']);
                    if ($isAssociative) {
                        // Convert associative array to sequential array
                        $normalizedAssignments = [];
                        foreach ($item['bus_assignments'] as $key => $assignment) {
                            if (is_array($assignment)) {
                                $normalizedAssignments[] = $assignment;
                            } else {
                                // If value is not an array, create one
                                $normalizedAssignments[] = [
                                    'bus_sub_type_id' => is_numeric($key) ? (int)$key : $key,
                                    'bus_id' => $assignment
                                ];
                            }
                        }
                        $item['bus_assignments'] = $normalizedAssignments;
                    }
                }
            }
            // Merge items back into request without affecting other fields
            $request->merge(['items' => $items]);
        }

        try {
            // Validate - Laravel will read from $request automatically
            $validated = $request->validate([
                'deployment_date' => ['required', 'date'],
                'trip_time_id' => ['required', 'exists:trip_times,id'],
                'bus_user_id' => ['required', 'exists:bus_users,id'],
                'deployment_type_id' => ['required', 'exists:deployment_types,id'],
                'trip_type' => ['required', 'in:arrival,departure'],
                'remarks' => ['nullable', 'string', 'max:1000'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.stoppage_id' => ['required', 'exists:stoppages,id'],
                'items.*.bus_assignments' => ['required', 'array'],
                'items.*.bus_assignments.*.bus_sub_type_id' => ['required', 'exists:bus_sub_types,id'],
                // bus_id may be a single value or an array of IDs; handle both in code
                'items.*.bus_assignments.*.bus_id' => ['nullable'],
                'items.*.bus_assignments.*.bus_id.*' => ['nullable', 'exists:buses,id'],
            ], [
                'deployment_date.required' => 'Deployment date is required.',
                'trip_time_id.required' => 'Trip time must be selected.',
                'bus_user_id.required' => 'Bus user must be selected.',
                'deployment_type_id.required' => 'Deployment type must be selected.',
                'deployment_type_id.exists' => 'Selected deployment type is invalid.',
                'trip_type.required' => 'Trip type must be selected.',
                'trip_type.arrival' => 'Trip type must be either "arrival" or "departure".',
                'items.required' => 'At least one stoppage must be added.',
                'items.*.stoppage_id.required' => 'Stoppage is required for each row.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        DB::beginTransaction();
        try {
            // Check for duplicate plan (same date, trip time, bus user, deployment type, and trip type) excluding current one
            $existingPlan = DeploymentPlan::where('deployment_date', $validated['deployment_date'])
                ->where('trip_time_id', $validated['trip_time_id'])
                ->where('bus_user_id', $validated['bus_user_id'])
                ->where('deployment_type_id', $validated['deployment_type_id'])
                ->where('trip_type', $validated['trip_type'])
                ->where('id', '!=', $deploymentPlan->id)
                ->first();

            if ($existingPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'A deployment plan already exists for this date, trip time, bus user, deployment type, and trip type combination.',
                ], 422);
            }

            // Update the deployment plan
            $deploymentPlan->update([
                'deployment_date' => $validated['deployment_date'],
                'trip_time_id' => $validated['trip_time_id'],
                'bus_user_id' => $validated['bus_user_id'],
                'deployment_type_id' => $validated['deployment_type_id'],
                'trip_type' => $validated['trip_type'],
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // Delete existing items
            $deploymentPlan->items()->delete();

            // Create new items - one row per selected bus
            $createdKeys = []; // dedupe keys: stoppage_id::bus_sub_type_id::bus_id
            foreach ($validated['items'] as $item) {
                $stoppageId = $item['stoppage_id'];
                $busAssignments = $item['bus_assignments'];

            // Helper closure to avoid duplicate records
            $createItem = function ($stoppageId, $subTypeId, $busId) use ($deploymentPlan, &$createdKeys) {
                if (empty($subTypeId) || empty($busId)) {
                    return;
                }

                $key = $stoppageId . '::' . $subTypeId . '::' . $busId;
                if (isset($createdKeys[$key])) {
                    return; // skip duplicate
                }
                $createdKeys[$key] = true;

                DeploymentPlanItem::create([
                    'deployment_plan_id' => $deploymentPlan->id,
                    'stoppage_id' => $stoppageId,
                    'bus_sub_type_id' => $subTypeId,
                    'bus_id' => $busId,
                ]);
            };

            if (isset($busAssignments[0])) {
                // Sequential array: each $assignment contains bus_sub_type_id and bus_id (array or single)
                foreach ($busAssignments as $assignment) {
                    $subTypeId = $assignment['bus_sub_type_id'] ?? null;
                    $busIds = $assignment['bus_id'] ?? [];

                    if (is_array($busIds)) {
                        foreach ($busIds as $busId) {
                            $createItem($stoppageId, $subTypeId, $busId);
                        }
                    } else {
                        $createItem($stoppageId, $subTypeId, $busIds);
                    }
                }
            } else {
                // Associative array (keyed by sub_type_id)
                foreach ($busAssignments as $subTypeKey => $assignment) {
                    if (!is_array($assignment)) {
                        continue;
                    }

                    $subTypeId = $assignment['bus_sub_type_id'] ?? $subTypeKey;
                    $busIds = $assignment['bus_id'] ?? [];

                    if (is_array($busIds)) {
                        foreach ($busIds as $busId) {
                            $createItem($stoppageId, $subTypeId, $busId);
                        }
                    } else {
                        $createItem($stoppageId, $subTypeId, $busIds);
                    }
                }
            }
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Daily deployment plan updated successfully.',
                'data' => $deploymentPlan->load(['tripTime', 'busUser', 'items.stoppage', 'items.busSubType', 'items.bus']),
            ];

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                return response()->json($response);
            }

            return redirect()
                ->route('deployment-plans.view-daily-deployment-plan')
                ->with('success', $response['message']);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error updating daily deployment plan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the deployment plan.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'An error occurred while updating the deployment plan.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeploymentPlan $deploymentPlan)
    {
        try {
            $deploymentPlan->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Daily deployment plan deleted successfully.',
                ]);
            }

            return redirect()
                ->route('daily-deployment-plans.index')
                ->with('success', 'Daily deployment plan deleted successfully.');

        } catch (\Throwable $e) {
            Log::error('Error deleting daily deployment plan', [
                'error' => $e->getMessage(),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the deployment plan.',
                ], 500);
            }

            return back()
                ->with('error', 'An error occurred while deleting the deployment plan.');
        }
    }

    /**
     * Get buses by bus sub-type for AJAX
     */
    public function getBusesBySubType(Request $request)
    {
        $busSubTypeId = $request->bus_sub_type_id;
        
        if (!$busSubTypeId) {
            return response()->json([
                'success' => true,
                'buses' => []
            ]);
        }

        // Get active status ID for buses
        $activeStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'active')
            ->where('user_id', Auth::id())
            ->first();
        
        if (!$activeStatus) {
            return response()->json([
                'success' => true,
                'buses' => []
            ]);
        }

        $buses = Bus::where('bus_sub_type_id', $busSubTypeId)
            ->where('status_id', $activeStatus->id)
            ->orderBy('model_name')
            ->get(['id', 'model_name', 'registration_number', 'bus_sub_type_id']);

        return response()->json([
            'success' => true,
            'buses' => $buses
        ]);
    }
}

