<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\EmployeeUniqueId;
use App\Models\Nationality;
use App\Models\Religion;
use App\Models\Shift;
use App\Models\Technology;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Get employees data for AJAX requests
     */
    public function getData(Request $request)
    {
        $employees = Employee::select('id', 'employee_name', 'employee_unique_id')
            ->orderBy('employee_name')
            ->get();

        return response()->json([
            'data' => $employees
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get paginated employees with their relationships
        $employees = Employee::with(['designation', 'religion', 'employeeType'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);  // 10 employees per page

        return view('content.employees.index', compact('employees'));
    }

    public function create()
    {
        $nationalities = Nationality::all();
        $religions = Religion::all();
        $designations = Designation::where('designation_type', 'Employee')->get();
        $religions = Religion::all();
        $employee_type = EmployeeType::all();

      
        return view('content.employees.create', compact('nationalities', 'religions', 'designations',  'employee_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Custom validation messages
        $messages = [
            'email.unique' => 'This email is already taken.',
            'mobile.unique' => 'This mobile number is already registered.',
        ];

        // Validation rules
        $validatedData = $request->validate([
            'employee_name' => 'required|string|max:255',
            'gender' => 'required|string',
            
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'mobile' => [
                'required',
                'string',
                'max:15',
                Rule::unique('employees', 'mobile')->ignore($request->id),
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                'email',
                Rule::unique('employees', 'email')->ignore($request->id),
            ],
            'nid' => 'required|string|max:20',
            'religion_id' => 'required|exists:religions,id',
            'designation_id' => 'required|exists:designations,id',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cv_upload' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'employee_type_id' => 'nullable|exists:employee_types,id',
            'ssc_or_equivalent_group' => 'nullable|string|max:255',
            'ssc_result' => 'nullable|string|max:255',
            'ssc_documents_upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'hsc_or_equivalent_group' => 'nullable|string|max:255',
            'hsc_result' => 'nullable|string|max:255',
            'hsc_documents_upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'bachelor_or_equivalent_group' => 'nullable|string|max:255',
            'result' => 'nullable|string|max:255',
            'honors_documents_upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'master_or_equivalent_group' => 'nullable|string|max:255',
            'masters_result' => 'nullable|string|max:255',
            'masters_document_upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'years_of_experience' => 'nullable|integer',
            'date_of_join' => 'nullable|date',
            'basic_salary' => 'nullable|numeric',
            'house_rent' => 'nullable|numeric',
            'medical_allowance' => 'nullable|numeric',
            'other_allowance' => 'nullable|numeric',
            'gross_salary' => 'nullable|numeric',
        ], $messages);

        DB::beginTransaction();

        // Track uploaded files to delete them if transaction fails
        $uploadedFiles = [];

        try {
            $data = $validatedData;
            $data['user_id'] = Auth::id();

            // Handle file uploads
            if ($request->hasFile('picture')) {
                $data['picture'] = $request->file('picture')->store('profile_pictures', 'public');
                $uploadedFiles[] = $data['picture'];
            }
            if ($request->hasFile('cv_upload')) {
                $data['cv_upload'] = $request->file('cv_upload')->store('cvs', 'public');
                $uploadedFiles[] = $data['cv_upload'];
            }
            if ($request->hasFile('ssc_documents_upload')) {
                $data['ssc_documents_upload'] = $request->file('ssc_documents_upload')->store('documents', 'public');
                $uploadedFiles[] = $data['ssc_documents_upload'];
            }
            if ($request->hasFile('hsc_documents_upload')) {
                $data['hsc_documents_upload'] = $request->file('hsc_documents_upload')->store('documents', 'public');
                $uploadedFiles[] = $data['hsc_documents_upload'];
            }
            if ($request->hasFile('honors_documents_upload')) {
                $data['honors_documents_upload'] = $request->file('honors_documents_upload')->store('documents', 'public');
                $uploadedFiles[] = $data['honors_documents_upload'];
            }
            if ($request->hasFile('masters_document_upload')) {
                $data['masters_document_upload'] = $request->file('masters_document_upload')->store('documents', 'public');
                $uploadedFiles[] = $data['masters_document_upload'];
            }

            // Create or update employee
            $employee = Employee::updateOrCreate(
                ['id' => $request->id],
                $data
            );

        

            DB::commit();

            // Handle AJAX requests
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee saved successfully.',
                    'redirect' => route('employees.view-employee')
                ]);
            }

            // Regular form submission
            return redirect()
                ->route('employees.view-employee')
                ->with('success', 'Employee saved successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            // Delete uploaded files if validation fails
            foreach ($uploadedFiles as $file) {
                if ($file && Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            // Handle AJAX validation errors
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            // Delete uploaded files if transaction failed
            foreach ($uploadedFiles as $file) {
                if ($file && Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            // Handle AJAX errors
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. No data was saved.',
                    'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Something went wrong. No data was saved.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load(['religion', 'designation', 'employeeType']);
        return view('content.employees.show', compact('employee'));
    }

    /**
     * Print employee details
     */
    public function print(Employee $employee)
    {
        $employee->load(['religion', 'designation', 'employeeType']);
        return view('content.employees.show', compact('employee'));
    }

    /**
     * Generate PDF for employee details
     */
    public function pdf(Employee $employee)
    {
        $employee->load(['religion', 'designation', 'employeeType']);

        $pdf = Pdf::loadView('content.employees.pdf', compact('employee'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'employee-' . ($employee->employee_unique_id ?? $employee->id) . '-' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function edit(Employee $employee)
    {
        $nationalities = Nationality::all();
        $religions = Religion::all();
        $employee_type = EmployeeType::all();
        $designations = Designation::where('designation_type', 'Employee')->get();
        $employee_type = EmployeeType::all();
        return view('content.employees.edit', compact('employee', 'nationalities', 'religions', 'designations', 'employee_type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        // Custom validation messages
        $messages = [
            'email.unique' => 'This email is already taken.',
            'mobile.unique' => 'This mobile number is already registered.',
        ];

        // Validation rules
        $validatedData = $request->validate([
            'employee_name' => 'required|string|max:255',
            'gender' => 'required|string',
            'employee_unique_id' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'mobile' => [
                'required',
                'string',
                'max:15',
                Rule::unique('employees', 'mobile')->ignore($employee->id),
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id),
            ],
            'nid' => 'required|string|max:20',
            'religion_id' => 'required|exists:religions,id',
            'designation_id' => 'required|exists:designations,id',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cv_upload' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'employee_type_id' => 'nullable|exists:employee_types,id',
            'ssc_or_equivalent_group' => 'nullable|string|max:255',
            'ssc_result' => 'nullable|string|max:255',
            'ssc_documents_upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'hsc_or_equivalent_group' => 'nullable|string|max:255',
            'hsc_result' => 'nullable|string|max:255',
            'hsc_documents_upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'bachelor_or_equivalent_group' => 'nullable|string|max:255',
            'result' => 'nullable|string|max:255',
            'honors_documents_upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'master_or_equivalent_group' => 'nullable|string|max:255',
            'masters_result' => 'nullable|string|max:255',
            'masters_document_upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'years_of_experience' => 'nullable|integer',
            'date_of_join' => 'nullable|date',
            'basic_salary' => 'nullable|numeric',
            'house_rent' => 'nullable|numeric',
            'medical_allowance' => 'nullable|numeric',
            'other_allowance' => 'nullable|numeric',
            'gross_salary' => 'nullable|numeric',
        ], $messages);

        DB::beginTransaction();

        // Track uploaded files to delete them if transaction fails
        $uploadedFiles = [];

        try {
            $data = $validatedData;
            $data['user_id'] = Auth::id();

            // Handle file uploads - only update if new file is uploaded
            if ($request->hasFile('picture')) {
                // Delete old picture if exists
                if ($employee->picture) {
                    $this->deleteFile($employee->picture);
                }
                $data['picture'] = $request->file('picture')->store('profile_pictures', 'public');
                $uploadedFiles[] = $data['picture'];
            }
            if ($request->hasFile('cv_upload')) {
                // Delete old CV if exists
                if ($employee->cv_upload) {
                    $this->deleteFile($employee->cv_upload);
                }
                $data['cv_upload'] = $request->file('cv_upload')->store('cvs', 'public');
                $uploadedFiles[] = $data['cv_upload'];
            }
            if ($request->hasFile('ssc_documents_upload')) {
                // Delete old document if exists
                if ($employee->ssc_documents_upload) {
                    $this->deleteFile($employee->ssc_documents_upload);
                }
                $data['ssc_documents_upload'] = $request->file('ssc_documents_upload')->store('documents', 'public');
                $uploadedFiles[] = $data['ssc_documents_upload'];
            }
            if ($request->hasFile('hsc_documents_upload')) {
                // Delete old document if exists
                if ($employee->hsc_documents_upload) {
                    $this->deleteFile($employee->hsc_documents_upload);
                }
                $data['hsc_documents_upload'] = $request->file('hsc_documents_upload')->store('documents', 'public');
                $uploadedFiles[] = $data['hsc_documents_upload'];
            }
            if ($request->hasFile('honors_documents_upload')) {
                // Delete old document if exists
                if ($employee->honors_documents_upload) {
                    $this->deleteFile($employee->honors_documents_upload);
                }
                $data['honors_documents_upload'] = $request->file('honors_documents_upload')->store('documents', 'public');
                $uploadedFiles[] = $data['honors_documents_upload'];
            }
            if ($request->hasFile('masters_document_upload')) {
                // Delete old document if exists
                if ($employee->masters_document_upload) {
                    $this->deleteFile($employee->masters_document_upload);
                }
                $data['masters_document_upload'] = $request->file('masters_document_upload')->store('documents', 'public');
                $uploadedFiles[] = $data['masters_document_upload'];
            }

            // Update employee
            $employee->update($data);

            DB::commit();

            // Handle AJAX requests
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee updated successfully.',
                    'redirect' => route('employees.view-employee')
                ]);
            }

            // Regular form submission - redirect with success message
            return redirect()
                ->route('employees.view-employee')
                ->with('success', 'Employee updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            // Delete uploaded files if validation fails
            foreach ($uploadedFiles as $file) {
                if ($file && Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            // Handle AJAX validation errors
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            // Delete uploaded files if transaction failed
            foreach ($uploadedFiles as $file) {
                if ($file && Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            // Handle AJAX errors
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. No data was saved.',
                    'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Something went wrong. No data was saved.');
        }
    }

    public function destroy(Employee $employee)
    {
        // Delete associated files
        $this->deleteFile($employee->picture);
        $this->deleteFile($employee->cv_upload);
        $this->deleteFile($employee->ssc_documents_upload);
        $this->deleteFile($employee->hsc_documents_upload);
        $this->deleteFile($employee->honors_documents_upload);
        $this->deleteFile($employee->masters_document_upload);

        $employee->delete();

        return redirect()->route('employees.view-employee')->with('success', 'Employee deleted successfully.');
    }

    private function deleteFile($filePath)
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }
}
