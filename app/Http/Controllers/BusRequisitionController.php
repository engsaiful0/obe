<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusRequisition;
use App\Models\Department;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BusRequisitionController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::orderBy('name')->get();
        
        $query = BusRequisition::with(['department', 'user']);

        // Apply filters
        if ($request->filled('search')) {
            $searchValue = $request->search;
            $query->where(function ($q) use ($searchValue) {
                $q->where('purpose', 'like', "%{$searchValue}%")
                  ->orWhere('requisition_sender_name', 'like', "%{$searchValue}%")
                  ->orWhere('mobile_number', 'like', "%{$searchValue}%")
                  ->orWhere('email_address', 'like', "%{$searchValue}%")
                  ->orWhereHas('department', function ($deptQuery) use ($searchValue) {
                      $deptQuery->where('name', 'like', "%{$searchValue}%");
                  });
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('required_bus_date_from')) {
            $query->where('required_bus_date', '>=', $request->required_bus_date_from);
        }

        if ($request->filled('required_bus_date_to')) {
            $query->where('required_bus_date', '<=', $request->required_bus_date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get pagination per page
        $perPage = $request->get('per_page', 10);

        // Paginate results
        $busRequisitions = $query->latest('date')->paginate($perPage)->withQueryString();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.bus-requisitions.partials.table', compact('busRequisitions'))->render(),
                'pagination' => $busRequisitions->appends($request->query())->links()->toHtml(),
                'total' => $busRequisitions->total(),
                'showing' => $busRequisitions->count()
            ]);
        }

        return view('content.bus-requisitions.index', compact('busRequisitions', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('content.bus-requisitions.add_bus_requisition', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'purpose' => 'required|string|max:1000',
            'required_bus_date' => 'required|date',
            'required_time' => 'required',
            'number_of_buses' => 'required|integer|min:1',
            'total_passengers' => 'required|integer|min:1',
            'department_id' => 'required|exists:departments,id',
            'requisition_sender_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'email_address' => 'required|email|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        $busRequisition = BusRequisition::create($data);
        $busRequisition->load(['department', 'user']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus requisition created successfully.',
                'busRequisition' => $busRequisition
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('app-bus-requisitions')->with('success', 'Bus requisition created successfully.');
    }

    public function show($id)
    {
        $busRequisition = BusRequisition::with(['department', 'user'])->findOrFail($id);
        return view('content.bus-requisitions.view_bus_requisition', compact('busRequisition'));
    }

    public function print($id)
    {
        $busRequisition = BusRequisition::with(['department', 'user'])->findOrFail($id);
        return view('content.bus-requisitions.print', compact('busRequisition'));
    }

    public function pdf($id)
    {
        $busRequisition = BusRequisition::with(['department', 'user'])->findOrFail($id);
        
        $pdf = Pdf::loadView('content.bus-requisitions.pdf', compact('busRequisition'));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'bus-requisition-' . $busRequisition->id . '-' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function edit($id)
    {
        $busRequisition = BusRequisition::with(['department', 'user'])->findOrFail($id);
        $departments = Department::orderBy('name')->get();
        return view('content.bus-requisitions.edit_bus_requisition', compact('busRequisition', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'purpose' => 'required|string|max:1000',
            'required_bus_date' => 'required|date',
            'required_time' => 'required',
            'number_of_buses' => 'required|integer|min:1',
            'total_passengers' => 'required|integer|min:1',
            'department_id' => 'required|exists:departments,id',
            'requisition_sender_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'email_address' => 'required|email|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $busRequisition = BusRequisition::findOrFail($id);
        $busRequisition->update($request->all());
        $busRequisition->load(['department', 'user']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus requisition updated successfully.',
                'busRequisition' => $busRequisition
            ]);
        }

        return redirect()->route('app-bus-requisitions')->with('success', 'Bus requisition updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $busRequisition = BusRequisition::findOrFail($id);
        $busRequisition->update([
            'status' => $request->status
        ]);
        $busRequisition->load(['department', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'busRequisition' => $busRequisition
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $busRequisition = BusRequisition::findOrFail($id);
        $busRequisition->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus requisition deleted successfully.'
            ]);
        }

        return redirect()->route('app-bus-requisitions')->with('success', 'Bus requisition deleted successfully.');
    }

    /**
     * API: Submit requisition from external portal (POST)
     */
    public function apiStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'purpose' => 'required|string|max:1000',
            'required_bus_date' => 'required|date',
            'required_time' => 'required',
            'number_of_buses' => 'required|integer|min:1',
            'total_passengers' => 'required|integer|min:1',
            'department_id' => 'required|exists:departments,id',
            'requisition_sender_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'email_address' => 'required|email|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        // Set user_id to null for API submissions (or use a system user)
        $data['user_id'] = null;
        $data['status'] = 'pending'; // Default status for API submissions

        $busRequisition = BusRequisition::create($data);
        $busRequisition->load(['department']);

        return response()->json([
            'success' => true,
            'message' => 'Bus requisition submitted successfully.',
            'data' => [
                'id' => $busRequisition->id,
                'date' => $busRequisition->date->format('Y-m-d'),
                'purpose' => $busRequisition->purpose,
                'required_bus_date' => $busRequisition->required_bus_date->format('Y-m-d'),
                'required_time' => $busRequisition->required_time,
                'number_of_buses' => $busRequisition->number_of_buses,
                'total_passengers' => $busRequisition->total_passengers,
                'department' => $busRequisition->department->name ?? null,
                'department_id' => $busRequisition->department_id,
                'requisition_sender_name' => $busRequisition->requisition_sender_name,
                'mobile_number' => $busRequisition->mobile_number,
                'email_address' => $busRequisition->email_address,
                'remarks' => $busRequisition->remarks,
                'status' => $busRequisition->status,
                'created_at' => $busRequisition->created_at->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * API: Get requisitions (GET)
     */
    public function apiIndex(Request $request)
    {
        $query = BusRequisition::with(['department']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('required_bus_date_from')) {
            $query->where('required_bus_date', '>=', $request->required_bus_date_from);
        }

        if ($request->filled('required_bus_date_to')) {
            $query->where('required_bus_date', '<=', $request->required_bus_date_to);
        }

        // Get pagination per page
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        // Paginate results
        $busRequisitions = $query->latest('date')->paginate($perPage, ['*'], 'page', $page);

        $data = $busRequisitions->map(function ($requisition) {
            return [
                'id' => $requisition->id,
                'date' => $requisition->date->format('Y-m-d'),
                'purpose' => $requisition->purpose,
                'required_bus_date' => $requisition->required_bus_date->format('Y-m-d'),
                'required_time' => $requisition->required_time,
                'number_of_buses' => $requisition->number_of_buses,
                'total_passengers' => $requisition->total_passengers,
                'department' => $requisition->department->name ?? null,
                'department_id' => $requisition->department_id,
                'requisition_sender_name' => $requisition->requisition_sender_name,
                'mobile_number' => $requisition->mobile_number,
                'email_address' => $requisition->email_address,
                'remarks' => $requisition->remarks,
                'status' => $requisition->status,
                'created_at' => $requisition->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $requisition->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Bus requisitions retrieved successfully.',
            'data' => $data,
            'pagination' => [
                'current_page' => $busRequisitions->currentPage(),
                'per_page' => $busRequisitions->perPage(),
                'total' => $busRequisitions->total(),
                'last_page' => $busRequisitions->lastPage(),
                'from' => $busRequisitions->firstItem(),
                'to' => $busRequisitions->lastItem(),
            ]
        ]);
    }

    /**
     * API Documentation: POST Endpoint
     */
    public function apiDocPost()
    {
        return view('content.bus-requisitions.api-doc-post');
    }

    /**
     * API: Get departments (GET)
     */
    public function apiGetDepartments(Request $request)
    {
        $departments = Department::orderBy('name')->get();

        $data = $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'created_at' => $department->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $department->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved successfully.',
            'data' => $data,
            'total' => $departments->count()
        ]);
    }

    /**
     * API Documentation: GET Endpoint
     */
    public function apiDocGet()
    {
        return view('content.bus-requisitions.api-doc-get');
    }
}


