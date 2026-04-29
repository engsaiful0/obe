<?php

namespace App\Http\Controllers;

use App\Models\BusHelper;
use App\Models\HelperUniqueId;
use App\Models\Gender;
use App\Models\MaritalStatus;
use App\Models\Religion;
use App\Models\Status as StatusModel;
use App\Models\EmployeeType;
use App\Models\EducationalQualification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Status;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusHelperController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BusHelper::with([
            'gender',
            'maritalStatus',
            'religion',
            'employeeType'
        ]);

        // Apply search filter
        if ($request->filled('search')) {
            $searchValue = $request->search;
            $query->where(function ($q) use ($searchValue) {
                $q->where('bus_helper_name', 'like', "%{$searchValue}%")
                  ->orWhere('bus_helper_id', 'like', "%{$searchValue}%")
                  ->orWhere('mobile', 'like', "%{$searchValue}%")
                  ->orWhere('nid_number', 'like', "%{$searchValue}%")
                  ->orWhere('father_name', 'like', "%{$searchValue}%")
                  ->orWhereHas('gender', function ($genderQuery) use ($searchValue) {
                      $genderQuery->where('gender_name', 'like', "%{$searchValue}%");
                  })
                  ->orWhereHas('employeeType', function ($typeQuery) use ($searchValue) {
                      $typeQuery->where('employee_type_name', 'like', "%{$searchValue}%");
                  });
            });
        }

        // Apply gender filter
        if ($request->filled('gender_filter')) {
            $query->where('gender_id', $request->gender_filter);
        }

        // Apply status filter
        if ($request->filled('status_filter')) {
            $query->where('status_id', $request->status_filter);
        }

        // Apply employee type filter
        if ($request->filled('employee_type_filter')) {
            $query->where('employee_type_id', $request->employee_type_filter);
        }

        // Apply experience filter
        if ($request->filled('experience_filter')) {
            switch ($request->experience_filter) {
                case 'beginner':
                    $query->where('years_of_experience', '<=', 1);
                    break;
                case 'intermediate':
                    $query->whereBetween('years_of_experience', [2, 3]);
                    break;
                case 'experienced':
                    $query->whereBetween('years_of_experience', [4, 5]);
                    break;
                case 'senior':
                    $query->where('years_of_experience', '>', 5);
                    break;
            }
        }

        // Apply salary range filter
        if ($request->filled('min_salary')) {
            $query->where('gross_salary', '>=', $request->min_salary);
        }
        if ($request->filled('max_salary')) {
            $query->where('gross_salary', '<=', $request->max_salary);
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['bus_helper_id', 'bus_helper_name', 'mobile', 'years_of_experience', 'gross_salary', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get paginated results
        $busHelpers = $query->paginate(10)->appends($request->query());

        // Get filter options for dropdowns
        $genders = Gender::all();
        $employeeTypes = EmployeeType::all();
        $statusOptions=StatusModel::where('related_to', 'bus-helper')->get();

        // If AJAX request, return JSON with HTML
        if ($request->ajax()) {
            $html = view('content.bus-helpers.partials.table', compact('busHelpers'))->render();
            $pagination = view('content.bus-helpers.partials.pagination', compact('busHelpers'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'total' => $busHelpers->total(),
                'showing' => $busHelpers->count(),
                'from' => $busHelpers->firstItem(),
                'to' => $busHelpers->lastItem()
            ]);
        }

        return view('content.bus-helpers.index', compact(
            'busHelpers',
            'genders',
            'employeeTypes',
            'statusOptions'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genders = Gender::all();
        $statusOptions=StatusModel::where('related_to', 'bus-helper')->get();
        $maritalStatuses = MaritalStatus::all();
        $religions = Religion::all();
        $employeeTypes = EmployeeType::all();
        $educationalQualifications = EducationalQualification::all();

        // Get the last serial
        $latest = HelperUniqueId::latest('serial')->first();
        $nextSerial = $latest ? $latest->serial + 1 : 1;

       
        return view('content.bus-helpers.create', compact(
            'genders',
            'statusOptions',
            'maritalStatuses',
            'religions',
            'employeeTypes',
            
            'nextSerial',
            'educationalQualifications'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_helper_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            
            'gender_id' => 'required|exists:genders,id',
            'marital_status_id' => 'required|exists:marital_statuses,id',
            'nid_copy' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'religion_id' => 'required|exists:religions,id',
           
            'assigned_bus_id' => 'nullable|exists:buses,id',
            'employee_type_id' => 'required|exists:employee_types,id',
            'status_id' => 'required|exists:statuses,id',
        ]);

       
        // Handle file uploads
        if ($request->hasFile('nid_copy')) {
            $validated['nid_copy'] = $request->file('nid_copy')->store('bus_helpers/documents', 'public');
        }

        if ($request->hasFile('picture')) {
            $validated['picture'] = $request->file('picture')->store('bus_helpers/pictures', 'public');
        }
        // Set user_id
        $validated['user_id'] = Auth::id();
      

        $busHelper = BusHelper::create($validated);

       

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus Helper created successfully.',
                'data' => $busHelper->load(['gender', 'maritalStatus', 'religion', 'employeeType'])
            ]);
        }

        return redirect()->route('bus-helpers.index')
            ->with('success', 'Bus Helper created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusHelper $busHelper)
    {
        return view('content.bus-helpers.show', compact('busHelper'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusHelper $busHelper)
    {
        $genders = Gender::all();
        $maritalStatuses = MaritalStatus::all();
        $religions = Religion::all();
        $statusOptions=StatusModel::where('related_to', 'bus-helper')->get();
        $employeeTypes = EmployeeType::all();
        $educationalQualifications = EducationalQualification::all();

        return view('content.bus-helpers.edit', compact(
            'busHelper',
            'genders',
            'maritalStatuses',
            'religions',
            'statusOptions',
            'employeeTypes',
            'educationalQualifications'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusHelper $busHelper)
    {
        $validated = $request->validate([
            'bus_helper_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
         
          
            'gender_id' => 'required|exists:genders,id',
            'marital_status_id' => 'required|exists:marital_statuses,id',
            'nid_copy' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'religion_id' => 'required|exists:religions,id',
            'status_id' => 'required|exists:statuses,id',
        ]);

        // Handle file uploads
        if ($request->hasFile('nid_copy')) {
            // Delete old file if exists
            if ($busHelper->nid_copy) {
                Storage::disk('public')->delete($busHelper->nid_copy);
            }
            $validated['nid_copy'] = $request->file('nid_copy')->store('bus_helpers/documents', 'public');
        }

        if ($request->hasFile('picture')) {
            // Delete old picture if exists
            if ($busHelper->picture) {
                Storage::disk('public')->delete($busHelper->picture);
            }
            $validated['picture'] = $request->file('picture')->store('bus_helpers/pictures', 'public');
        }

      
        $busHelper->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus Helper updated successfully.',
                'data' => $busHelper->load(['gender', 'maritalStatus', 'religion', 'employeeType'])
            ]);
        }

        return redirect()->route('bus-helpers.index')
            ->with('success', 'Bus Helper updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, BusHelper $busHelper)
    {
        try {
            // Delete associated files
            if ($busHelper->nid_copy) {
                Storage::disk('public')->delete($busHelper->nid_copy);
            }
            if ($busHelper->picture) {
                Storage::disk('public')->delete($busHelper->picture);
            }

            $busHelper->delete();

            // Check if request is AJAX or expects JSON
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bus Helper deleted successfully.'
                ]);
            }

            return redirect()->route('bus-helpers.index')
                ->with('success', 'Bus Helper deleted successfully.');
        } catch (\Exception $e) {
            // Handle errors for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting bus helper: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('bus-helpers.index')
                ->with('error', 'Error deleting bus helper: ' . $e->getMessage());
        }
    }

    /**
     * AJAX method to get bus helpers data for DataTables
     */
    public function getBusHelpersData(Request $request)
    {
        try {
            $query = BusHelper::with([
                'gender',
                'maritalStatus', 
                'religion',
                'assignedBus',
                'employeeType',
                'status'
            ]);

            // Get total count before filtering
            $totalRecords = BusHelper::count();

        // Apply search filter
        if ($request->filled('search') && $request->search['value'] != '') {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('bus_helper_name', 'like', "%{$searchValue}%")
                  ->orWhere('bus_helper_id', 'like', "%{$searchValue}%")
                  ->orWhere('mobile', 'like', "%{$searchValue}%")
                  ->orWhere('nid_number', 'like', "%{$searchValue}%")
                  ->orWhereHas('gender', function ($genderQuery) use ($searchValue) {
                      $genderQuery->where('gender_name', 'like', "%{$searchValue}%");
                  })
                  ->orWhereHas('employeeType', function ($typeQuery) use ($searchValue) {
                      $typeQuery->where('employee_type_name', 'like', "%{$searchValue}%");
                  });
            });
        }

        // Get filtered count
        $filteredRecords = $query->count();

        // Apply ordering - simplified to avoid join issues
        if ($request->has('order') && !empty($request->order)) {
            $orderColumnIndex = $request->order[0]['column'] ?? 0;
            $orderDirection = $request->order[0]['dir'] ?? 'desc';
            
            $columns = ['bus_helper_id', 'bus_helper_name', 'mobile', 'years_of_experience', 'gross_salary'];
            
            if (isset($columns[$orderColumnIndex])) {
                $query->orderBy($columns[$orderColumnIndex], $orderDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Apply pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        $busHelpers = $query->skip($start)->take($length)->get();

        $response = [
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $busHelpers
        ];

        return response()->json($response);
        
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load bus helpers data'
            ], 500);
        }
    }

    /**
     * AJAX method to get bus helper details
     */
    public function getBusHelperDetails(BusHelper $busHelper)
    {
        $busHelper->load([
            'gender',
            'maritalStatus',
            'religion',
            'assignedBus',
            'employeeType',
            'status'
        ]);

        return response()->json([
            'success' => true,
            'data' => $busHelper
        ]);
    }


}