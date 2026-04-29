<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EmployeeAttendance::with(['employee', 'user']);

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(20);

        $employees = Employee::where('user_id', Auth::id())->get();

        return view('content.employee-attendances.index', compact('attendances', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('user_id', Auth::id())->get();
        return view('content.employee-attendances.create', compact('employees'));
    }

    /**
     * Show the form for adding all attendance.
     */
    public function addAllAttendance()
    {
        $employees = Employee::where('user_id', Auth::id())->get();
        return view('content.employee-attendances.add-all-attendance', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'required',
            'check_out_time' => 'nullable',
            'status' => 'required|in:present,absent,late,early_leave',
            'remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please correct the highlighted fields.',
                    'errors' => $validator->errors()
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        // Prevent duplicate attendance for same employee/date
        $existingAttendance = EmployeeAttendance::where('employee_id', $request->employee_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();

        if ($existingAttendance) {
            $message = 'Attendance already exists for this employee on this date.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }

            return back()->with('error', $message);
        }

        try {
            EmployeeAttendance::create([
                'employee_id' => $request->employee_id,
                'attendance_date' => $request->attendance_date,
                'check_in_time' => $request->check_in_time,
                'check_out_time' => $request->check_out_time,
                'status' => $request->status,
                'remarks' => $request->remarks,
                'user_id' => Auth::id(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee attendance added successfully.'
                ]);
            }

            return redirect()
                ->route('employee-attendances.index')
                ->with('success', 'Employee attendance added successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Log technical details for debugging
            Log::error('Database error in EmployeeAttendance store(): ' . $e->getMessage());

            $message = 'A database error occurred while saving the attendance. Please try again.';
        } catch (\Exception $e) {
            // Log unexpected exceptions
            Log::error('Error in EmployeeAttendance store(): ' . $e->getMessage());

            $message = 'Something went wrong while saving the attendance. Please try again later.';
        }

        // Return readable error message
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 500);
        }

        return back()->with('error', $message);
    }

    /**
     * Submit all attendance for multiple employees.
     */
    public function submitAllAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendance_date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.employee_id' => 'required|exists:employees,id',
            'attendances.*.check_in_time' => 'required|date_format:H:i',
            'attendances.*.check_out_time' => 'nullable|date_format:H:i',
            'attendances.*.status' => 'required|in:present,absent,late,early_leave',
            'attendances.*.remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            foreach ($request->attendances as $attendanceData) {
                $existingAttendance = EmployeeAttendance::where('employee_id', $attendanceData['employee_id'])
                    ->whereDate('attendance_date', $request->attendance_date)
                    ->first();

                if (!$existingAttendance) {
                    EmployeeAttendance::create([
                        'employee_id' => $attendanceData['employee_id'],
                        'attendance_date' => $request->attendance_date,
                        'check_in_time' => $attendanceData['check_in_time'],
                        'check_out_time' => $attendanceData['check_out_time'] ?? null,
                        'status' => $attendanceData['status'],
                        'remarks' => $attendanceData['remarks'] ?? null,
                        'user_id' => Auth::id(),
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All employee attendance added successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error adding employee attendance: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(EmployeeAttendance $employeeAttendance)
    {
        $employeeAttendance->load(['employee', 'user']);
        return view('content.employee-attendances.show', compact('employeeAttendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeAttendance $employeeAttendance)
    {
        $employees = Employee::where('user_id', Auth::id())->get();
        return view('content.employee-attendances.edit', compact('employeeAttendance', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeAttendance $employeeAttendance)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'required',
            'check_out_time' => 'nullable',
            'status' => 'required|in:present,absent,late,early_leave',
            'remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $employeeAttendance->update([
                'employee_id' => $request->employee_id,
                'attendance_date' => $request->attendance_date,
                'check_in_time' => $request->check_in_time,
                'check_out_time' => $request->check_out_time,
                'status' => $request->status,
                'remarks' => $request->remarks,
                'updated_by' => Auth::id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee attendance updated successfully.'
                ]);
            }

            return redirect()->route('employee-attendances.index')->with('success', 'Employee attendance updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating employee attendance: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating employee attendance: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeAttendance $employeeAttendance)
    {
        try {
            $employeeAttendance->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee attendance deleted successfully!'
                ]);
            }

            return redirect()->route('employee-attendances.index')->with('success', 'Employee attendance deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting employee attendance: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error deleting employee attendance: ' . $e->getMessage());
        }
    }
}
