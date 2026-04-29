<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\AppSetting;
use App\Models\FeeCollect;
use App\Models\FeeHead;
use App\Models\Student as StudentModel;
use Illuminate\Http\Request;

use App\Models\Technology;

use App\Models\AcademicYear; // Assuming you have an AcademicYear model
use App\Models\Semester; // Assuming you have a Semester model
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\Employee as EmployeeModel;
use App\Models\BusSubType;
use App\Models\Bus;
use App\Models\Teacher as TeacherModel;
use App\Models\Designation;


class ReportController extends Controller
{
    public function employeeListReport(Request $request)
    {
        $designations = Designation::all();
        $query = EmployeeModel::with(['designation']);

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        if ($request->filled('employee_unique_id')) {
            $query->where('employee_unique_id', 'like', '%' . $request->employee_unique_id . '%');
        }

        if ($request->filled('year')) {
            $query->whereYear('date_of_join', $request->year);
        }

        if ($request->has('excel')) {
            return $this->employeeListReportExcel($query);
        }

        if ($request->has('pdf')) {
            return $this->employeeListReportPdf($query);
        }

        $perPage = $request->input('per_page', 10);
        $employees = $query->paginate($perPage);

        return view('content.report.employee-list-report', compact('employees', 'designations'));
    }

    public function employeeListReportExcel($query)
    {
        $employees = $query->get();

        $fileName = "employee-list-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('#', 'Employee ID', 'Employee Name', 'Designation', 'Email', 'Phone', 'Gender', 'Date of Join', 'Basic Salary', 'Gross Salary');

        $callback = function() use($employees, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($employees as $key => $employee) {
                $row['#']            = $key + 1;
                $row['Employee ID']   = $employee->employee_unique_id;
                $row['Employee Name'] = $employee->employee_name;
                $row['Designation'] = $employee->designation->designation_name ?? '';
                $row['Email']        = $employee->email;
                $row['Phone']        = $employee->mobile;
                $row['Gender']       = ucfirst($employee->gender);
                $row['Date of Join'] = $employee->date_of_join ? \Carbon\Carbon::parse($employee->date_of_join)->format('d-m-Y') : '';
                $row['Basic Salary'] = $employee->basic_salary ? '৳' . number_format($employee->basic_salary) : '';
                $row['Gross Salary'] = $employee->gross_salary ? '৳' . number_format($employee->gross_salary) : '';

                fputcsv($file, array($row['#'], $row['Employee ID'], $row['Employee Name'], $row['Designation'], $row['Email'], $row['Phone'], $row['Gender'], $row['Date of Join'], $row['Basic Salary'], $row['Gross Salary']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    /**
     * Display a listing of the resource.
     */
    public function studentWiseReport(Request $request)
    {
        $query = StudentModel::query();

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('student_info')) {
            $studentInfo = $request->student_info;
            $query->where(function ($q) use ($studentInfo) {
                $q->where('student_name', 'like', "%{$studentInfo}%")
                    ->orWhere('student_id', 'like', "%{$studentInfo}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $students = $query->latest()->paginate(10);
        $academicYears = AcademicYear::all();

        return view('content.report.student-wise-report', compact('students', 'academicYears'));
    }

    public function generateStudentWiseReport(Request $request)
    {
        $query = StudentModel::query();

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('student_info')) {
            $studentInfo = $request->student_info;
            $query->where(function ($q) use ($studentInfo) {
                $q->where('student_name', 'like', "%{$studentInfo}%")
                    ->orWhere('student_id', 'like', "%{$studentInfo}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $students = $query->latest()->get();

        $pdf = Pdf::loadView('content.report.student-wise-report-pdf', compact('students'));
        return $pdf->stream('student-wise-report.pdf');
    }

    public function feeCollectionReport(Request $request)
    {
        $query = FeeCollect::with(['student', 'academic_year', 'semester', 'payment_method', 'user']);
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }


        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('student_info')) {
            $query->where('student_id', $request->student_info);
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $totalAmount = $query->sum('total_amount');
        $feeCollections = $query->latest()->paginate(10);

        // Process fee heads for each collection
        foreach ($feeCollections as $collection) {
            $feeHeadDetails = [];

            $feeHeads = is_string($collection->fee_heads)
                ? json_decode($collection->fee_heads, true)
                : $collection->fee_heads;

            if (is_array($feeHeads)) {
                foreach ($feeHeads as $feeHead) {
                    // Case 1: fee_heads = ["3","4","5"]
                    if (is_numeric($feeHead)) {
                        $feeHeadModel = FeeHead::find($feeHead);
                    }

                    // Case 2: fee_heads = [{"id":3,"amount":500}]
                    elseif (is_array($feeHead) && isset($feeHead['id'])) {
                        $feeHeadModel = FeeHead::find($feeHead['id']);
                    } else {
                        $feeHeadModel = null;
                    }

                    if ($feeHeadModel) {
                        $feeHeadDetails[] = (object)[
                            'id'     => $feeHeadModel->id,
                            'name'   => $feeHeadModel->name,
                            'amount' => $feeHeadModel->amount,
                        ];
                    }
                }
            }

            $collection->fee_heads = $feeHeadDetails;
        }





        $academicYears = AcademicYear::all();
        // info($feeCollections->toArray());

        return view('content.report.fee-collection-report', compact('feeCollections', 'academicYears', 'totalAmount'));
    }

    public function getStudentsByYear($academic_year_id, Request $request)
    {
        $query = StudentModel::where('academic_year_id', $academic_year_id);
        
        // Apply semester filter if provided
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        
        $students = $query->orderBy('student_unique_id')->get();
        return response()->json($students);
    }

    public function generateFeeCollectionDetailsPdf($id)
    {
        $feeCollection = FeeCollect::with(['student', 'academic_year', 'semester', 'payment_method', 'user'])->findOrFail($id);
        $appSetting = AppSetting::first();
        $pdf = Pdf::loadView('content.report.fee-collection-details-pdf', compact('feeCollection', 'appSetting'));
        return $pdf->stream('fee-collection-details.pdf');
    }

    public function feeCollectionReportExcel(Request $request)
    {
        $query = FeeCollect::with(['student', 'academic_year', 'semester', 'payment_method', 'user']);
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('student_info')) {
            $query->where('student_id', $request->student_info);
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $feeCollections = $query->latest()->get();

        $fileName = "fee-collection-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Student Name', 'Academic Year', 'Semester', 'Payment Method', 'Total Amount', 'Date');

        $callback = function() use($feeCollections, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($feeCollections as $collection) {
                $row['Student Name']  = $collection->student->full_name_in_english_block_letter ?? '';
                $row['Academic Year'] = $collection->academic_year->academic_year_name ?? '';
                $row['Semester']      = $collection->semester->semester_name ?? '';
                $row['Payment Method'] = $collection->payment_method->payment_method_name ?? '';
                $row['Total Amount']  = $collection->total_amount;
                $row['Date']          = $collection->date;

                fputcsv($file, array($row['Student Name'], $row['Academic Year'], $row['Semester'], $row['Payment Method'], $row['Total Amount'], $row['Date']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function expenseReport(Request $request)
    {
        $query = Expense::with(['expenseHead', 'busSubType', 'bus', 'employee']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($fromDate) {
            $query->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        $totalAmount = $query->sum('amount');
        $expenses = $query->latest()->paginate(10);
        $expenseHeads = ExpenseHead::all();
        $busSubTypes = BusSubType::all();
        $buses = Bus::with(['brand', 'yearOfManufacture'])->get();
        $employees = EmployeeModel::all();
        $suppliers=Supplier::all();

        return view('content.report.expense-report', compact('expenses', 'expenseHeads', 'busSubTypes', 'buses', 'employees', 'totalAmount','suppliers'));
    }

    public function expenseReportAjax(Request $request)
    {
        $query = Expense::with(['expenseHead', 'busSubType', 'bus', 'employee']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }
        

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($fromDate) {
            $query->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        $totalAmount = $query->sum('amount');
        $expenses = $query->latest()->paginate(10);

        return response()->json([
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
            'html' => view('content.report.expense-report-table', compact('expenses', 'totalAmount'))->render()
        ]);
    }

    public function printExpenseReportList(Request $request)
    {
        $query = Expense::with(['expenseHead', 'busSubType', 'bus', 'employee', 'supplier']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($fromDate) {
            $query->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        // Get all expenses (no pagination for print)
        $expenses = $query->latest()->get();
        $totalAmount = $expenses->sum('amount');

        // Prepare filter information for display
        $filterInfo = [];

        // Human-readable filters for header / summary
        if ($request->filled('supplier_id')) {
            $supplier = Supplier::find($request->supplier_id);
            $filterInfo['supplier'] = $supplier ? $supplier->supplier_name : 'N/A';
        }
        if ($request->filled('expense_head_id')) {
            $expenseHead = ExpenseHead::find($request->expense_head_id);
            $filterInfo['expense_head'] = $expenseHead ? $expenseHead->name : 'N/A';
        }
        if ($request->filled('bus_sub_type_id')) {
            $busSubType = BusSubType::find($request->bus_sub_type_id);
            $filterInfo['bus_sub_type'] = $busSubType ? $busSubType->sub_type_name : 'N/A';
        }
        if ($request->filled('bus_id')) {
            $bus = Bus::find($request->bus_id);
            $filterInfo['bus'] = $bus ? $bus->bus_number : 'N/A';
        }
        if ($request->filled('employee_id')) {
            $employee = EmployeeModel::find($request->employee_id);
            $filterInfo['employee'] = $employee ? $employee->employee_name : 'N/A';
        }
        if ($request->filled('date_range')) {
            $filterInfo['date_range'] = ucfirst(str_replace('_', ' ', $request->date_range));
        }
        // Prefer explicit custom dates from the request; fall back to computed range (date_range)
        if ($request->filled('from_date')) {
            $filterInfo['from_date'] = $request->from_date;
        } elseif ($fromDate) {
            $filterInfo['from_date'] = $fromDate instanceof Carbon ? $fromDate->toDateString() : $fromDate;
        }
        if ($request->filled('to_date')) {
            $filterInfo['to_date'] = $request->to_date;
        } elseif ($toDate) {
            $filterInfo['to_date'] = $toDate instanceof Carbon ? $toDate->toDateString() : $toDate;
        }

        return view('content.report.expense-report-print-list', compact('expenses', 'totalAmount', 'request', 'filterInfo'));
    }

    public function expenseReportPdf(Request $request)
    {
        $query = Expense::with(['expenseHead', 'busSubType', 'bus', 'employee']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($fromDate) {
            $query->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate) {
$query->whereDate('expense_date', '<=', $toDate);
        }

        $expenses = $query->latest()->get();
        $totalAmount = $expenses->sum('amount');
        $appSetting = AppSetting::first();


        $pdf = Pdf::loadView('content.report.expense-report-pdf', compact('expenses', 'totalAmount', 'appSetting', 'request'));
        return $pdf->stream('expense-report.pdf');
    }

    public function expenseReportExcel(Request $request)
    {
        $query = Expense::with(['expenseHead', 'busSubType', 'bus', 'employee']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($dateRange) {
            switch ($dateRange) {
                case 'this_week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $fromDate = Carbon::now()->subMonth()->startOfMonth();
                    $toDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate = Carbon::now()->endOfYear();
                    break;
                case 'last_six_months':
                    $fromDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $toDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($fromDate) {
            $query->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        $expenses = $query->latest()->get();

        $fileName = "expense-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Expense Head', 'Bus Sub Type', 'Bus Name', 'Employee', 'Date', 'Remarks', 'Amount');

        $callback = function() use($expenses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($expenses as $expense) {
                $row['Expense Head'] = $expense->expenseHead->name ?? '';
                $row['Bus Sub Type'] = $expense->busSubType->sub_type_name ?? '';
                $row['Bus Name'] = $expense->bus ? $expense->bus->display_name : '';
                $row['Employee'] = $expense->employee->employee_name ?? '';
                $row['Date']         = $expense->expense_date;
                $row['Remarks']      = $expense->remarks;
                $row['Amount']       = $expense->amount;

                fputcsv($file, array($row['Expense Head'], $row['Bus Sub Type'], $row['Bus Name'], $row['Employee'], $row['Date'], $row['Remarks'], $row['Amount']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function studentListReport(Request $request)
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $technologies = Technology::all();

        $query = StudentModel::with(['academicYear', 'semester', 'technology']);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('technology_id')) {
            $query->where('technology_id', $request->technology_id);
        }

        if ($request->has('excel')) {
            return $this->studentListReportExcel($query);
        }

        if ($request->has('pdf')) {
            return $this->studentListReportPdf($query);
        }

        $perPage = $request->input('per_page', 10);
        $students = $query->paginate($perPage);

        return view('content.report.student-list-report', compact('students', 'academicYears', 'semesters', 'technologies'));
    }

    public function studentListReportExcel($query)
    {
        $students = $query->get();

        $fileName = "student-list-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('#', 'Student ID', 'Student Name', 'Academic Year', 'Semester', 'Technology', 'Email', 'Phone');

        $callback = function() use($students, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($students as $key => $student) {
                $row['#']            = $key + 1;
                $row['Student ID']   = $student->student_unique_id;
                $row['Student Name'] = $student->full_name_in_english_block_letter;
                $row['Academic Year'] = $student->academicYear->academic_year_name ?? '';
                $row['Semester']     = $student->semester->semester_name ?? '';
                $row['Technology']   = $student->technology->technology_name ?? '';
                $row['Email']        = $student->email;
                $row['Phone']        = $student->phone;

                fputcsv($file, array($row['#'], $row['Student ID'], $row['Student Name'], $row['Academic Year'], $row['Semester'], $row['Technology'], $row['Email'], $row['Phone']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function studentListReportPdf($query)
    {
        $students = $query->get();
        
        // Get filter information
        $filters = request()->only(['academic_year_id', 'semester_id', 'technology_id']);
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $technologies = Technology::all();
        
        $pdf = \PDF::loadView('content.report.student-list-pdf', compact('students', 'filters', 'academicYears', 'semesters', 'technologies'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('student-list-report-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function teacherListReport(Request $request)
    {
        $designations = Designation::all();
        $query = TeacherModel::with(['designation']);

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        if ($request->filled('teacher_unique_id')) {
            $query->where('teacher_unique_id', 'like', '%' . $request->teacher_unique_id . '%');
        }

        if ($request->has('excel')) {
            return $this->teacherListReportExcel($query);
        }

        if ($request->has('pdf')) {
            return $this->teacherListReportPdf($query);
        }

        $perPage = $request->input('per_page', 10);
        $teachers = $query->paginate($perPage);

        return view('content.report.teacher-list-report', compact('teachers', 'designations'));
    }

    public function teacherListReportExcel($query)
    {
        $teachers = $query->get();

        $fileName = "teacher-list-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('#', 'Teacher ID', 'Teacher Name', 'Designation', 'Email', 'Phone', 'Gender', 'Joining Date', 'Basic Salary', 'Gross Salary');

        $callback = function() use($teachers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($teachers as $key => $teacher) {
                $row['#']            = $key + 1;
                $row['Teacher ID']   = $teacher->teacher_unique_id;
                $row['Teacher Name'] = $teacher->teacher_name;
                $row['Designation'] = $teacher->designation->designation_name ?? '';
                $row['Email']        = $teacher->email;
                $row['Phone']        = $teacher->mobile;
                $row['Gender']       = ucfirst($teacher->gender);
                $row['Joining Date'] = $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->format('d-m-Y') : '';
                $row['Basic Salary'] = $teacher->basic_salary ? '৳' . number_format($teacher->basic_salary) : '';
                $row['Gross Salary'] = $teacher->gross_salary ? '৳' . number_format($teacher->gross_salary) : '';

                fputcsv($file, array($row['#'], $row['Teacher ID'], $row['Teacher Name'], $row['Designation'], $row['Email'], $row['Phone'], $row['Gender'], $row['Joining Date'], $row['Basic Salary'], $row['Gross Salary']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function teacherListReportPdf($query)
    {
        $teachers = $query->get();
        
        // Get filter information
        $filters = request()->only(['designation_id', 'teacher_unique_id']);
        $designations = Designation::all();
        
        $pdf = \PDF::loadView('content.report.teacher-list-pdf', compact('teachers', 'filters', 'designations'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('teacher-list-report-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function printEmployeeListReport(Request $request)
    {
        $query = EmployeeModel::with(['designation']);

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        if ($request->filled('employee_unique_id')) {
            $query->where('employee_unique_id', 'like', '%' . $request->employee_unique_id . '%');
        }

        if ($request->filled('year')) {
            $query->whereYear('date_of_join', $request->year);
        }

        // Get all employees (no pagination for print)
        $employees = $query->get();

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('designation_id')) {
            $designation = Designation::find($request->designation_id);
            $filterInfo['designation'] = $designation ? $designation->designation_name : 'N/A';
        }
        if ($request->filled('employee_unique_id')) {
            $filterInfo['employee_unique_id'] = $request->employee_unique_id;
        }
        if ($request->filled('year')) {
            $filterInfo['year'] = $request->year;
        }

        return view('content.report.employee-list-print-list', compact('employees', 'request', 'filterInfo'));
    }

    public function employeeListReportPdf($query)
    {
        $employees = $query->get();
        
        // Get filter information
        $filters = request()->only(['designation_id', 'employee_unique_id', 'year']);
        $designations = Designation::all();
        
        $pdf = \PDF::loadView('content.report.employee-list-pdf', compact('employees', 'filters', 'designations'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('employee-list-report-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function feeDetailsReport(Request $request)
    {
        $students = StudentModel::all();
        $feeHeads = FeeHead::where('fee_type', 'Regular')->get();
        $monthlyFeeHead = FeeHead::where('fee_type', 'Monthly')->first();

        $query = FeeCollect::with(['student']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $feeCollections = $query->get();

        $reportData = [];

        $studentQuery = StudentModel::query();
        if ($request->filled('student_id')) {
            $studentQuery->where('id', $request->student_id);
        }

        $paginatedStudents = $studentQuery->paginate(10);

        foreach ($paginatedStudents as $student) {
            $studentCollections = $feeCollections->where('student_id', $student->id);
            $paidFeeHeadIds = [];
            $paidMonths = [];

            foreach ($studentCollections as $collection) {
                $decodedFeeHeads = json_decode($collection->fee_heads, true);
                if (is_array($decodedFeeHeads)) {
                    foreach ($decodedFeeHeads as $feeHead) {
                        if (isset($feeHead['id'])) {
                            $paidFeeHeadIds[] = $feeHead['id'];
                            if (isset($feeHead['months'])) {
                                $paidMonths = array_merge($paidMonths, $feeHead['months']);
                            }
                        } else {
                            $paidFeeHeadIds[] = $feeHead;
                        }
                    }
                }
            }

            $monthlyPayments = [];
            for ($year = 1; $year <= 4; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $monthlyPayments[$year][$month] = in_array($month, $paidMonths);
                }
            }

            $reportData[] = [
                'student' => $student,
                'paid_fee_heads' => collect(array_unique($paidFeeHeadIds)),
                'monthly_payments' => $monthlyPayments,
            ];
        }


        return view('content.report.fee-details-report', compact('reportData', 'students', 'feeHeads', 'monthlyFeeHead', 'paginatedStudents'));
    }

    public function feeDetailsReportExcel(Request $request)
    {
        $students = StudentModel::all();
        $feeHeads = FeeHead::where('fee_type', 'Regular')->get();
        $monthlyFeeHead = FeeHead::where('fee_type', 'Monthly')->first();

        $query = FeeCollect::with(['student']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        $feeCollections = $query->get();


        $studentQuery = StudentModel::query();
        if ($request->filled('student_id')) {
            $studentQuery->where('id', $request->student_id);
        }
        $studentsToExport = $studentQuery->get();


        $fileName = "fee-details-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array_merge(['#', 'Student ID', 'Student Name'], $feeHeads->pluck('name')->toArray());
        if ($monthlyFeeHead) {
            for ($y = 1; $y <= 4; $y++) {
                for ($m = 1; $m <= 12; $m++) {
                    $columns[] = "Year $y - " . date('M', mktime(0, 0, 0, $m, 10));
                }
            }
        }


        $callback = function() use($studentsToExport, $feeHeads, $feeCollections, $columns, $monthlyFeeHead) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($studentsToExport as $key => $student) {
                $studentCollections = $feeCollections->where('student_id', $student->id);
                $paidFeeHeadIds = [];
                $paidMonths = [];

                foreach ($studentCollections as $collection) {
                    $decodedFeeHeads = json_decode($collection->fee_heads, true);
                    if (is_array($decodedFeeHeads)) {
                        foreach ($decodedFeeHeads as $feeHead) {
                            if (isset($feeHead['id'])) {
                                $paidFeeHeadIds[] = $feeHead['id'];
                                if (isset($feeHead['months'])) {
                                    $paidMonths = array_merge($paidMonths, $feeHead['months']);
                                }
                            } else {
                                $paidFeeHeadIds[] = $feeHead;
                            }
                        }
                    }
                }
                $paidFeeHeadIds = collect(array_unique($paidFeeHeadIds));

                $row = [
                    '#' => $key + 1,
                    'Student ID' => $student->student_unique_id,
                    'Student Name' => $student->full_name_in_english_block_letter,
                ];

                foreach ($feeHeads as $feeHead) {
                    $row[$feeHead->name] = $paidFeeHeadIds->contains($feeHead->id) ? 'Paid' : 'Unpaid';
                }

                if ($monthlyFeeHead) {
                    for ($y = 1; $y <= 4; $y++) {
                        for ($m = 1; $m <= 12; $m++) {
                            $row[] = in_array($m, $paidMonths) ? 'Paid' : 'Unpaid';
                        }
                    }
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function headWiseFeeReport(Request $request)
    {
        $students = StudentModel::all();
        $feeHeads = FeeHead::all();

        $query = FeeCollect::with(['student']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('fee_head_id')) {
            $query->whereJsonContains('fee_heads', $request->fee_head_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $perPage = $request->input('per_page', 10);
        $feeCollections = $query->orderBy('date', 'desc')->paginate($perPage);

        $reportData = [];

        foreach ($feeCollections as $collection) {
            $decodedFeeHeads = json_decode($collection->fee_heads, true);
            if (is_array($decodedFeeHeads)) {
                foreach ($decodedFeeHeads as $feeHeadData) {
                    $feeHeadId = is_array($feeHeadData) ? $feeHeadData['id'] : $feeHeadData;
                    $feeHead = $feeHeads->find($feeHeadId);
                    
                    if ($feeHead) {
            $reportData[] = [
                            'student' => $collection->student,
                            'fee_head' => $feeHead,
                            'amount' => is_array($feeHeadData) && isset($feeHeadData['amount']) ? $feeHeadData['amount'] : $feeHead->amount,
                            'payment_date' => $collection->date,
                            'status' => 'Paid'
                        ];
                    }
                }
            }
        }

        return view('content.report.head-wise-fee-report', compact('reportData', 'students', 'feeHeads', 'feeCollections'));
    }

    public function headWiseFeeReportExcel(Request $request)
    {
        $students = StudentModel::all();
        $feeHeads = FeeHead::all();

        $query = FeeCollect::with(['student']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('fee_head_id')) {
            $query->whereJsonContains('fee_heads', $request->fee_head_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $feeCollections = $query->orderBy('date', 'desc')->get();

        $reportData = [];

        foreach ($feeCollections as $collection) {
            $decodedFeeHeads = json_decode($collection->fee_heads, true);
            if (is_array($decodedFeeHeads)) {
                foreach ($decodedFeeHeads as $feeHeadData) {
                    $feeHeadId = is_array($feeHeadData) ? $feeHeadData['id'] : $feeHeadData;
                    $feeHead = $feeHeads->find($feeHeadId);
                    
                    if ($feeHead) {
                        $reportData[] = [
                            'student' => $collection->student,
                            'fee_head' => $feeHead,
                            'amount' => is_array($feeHeadData) && isset($feeHeadData['amount']) ? $feeHeadData['amount'] : $feeHead->amount,
                            'payment_date' => $collection->date,
                            'status' => 'Paid'
                        ];
                    }
                }
            }
        }

        $fileName = "head-wise-fee-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('#', 'Student ID', 'Student Name', 'Fee Head', 'Amount', 'Payment Date', 'Status');

        $callback = function() use($reportData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($reportData as $key => $data) {
                $row = [
                    '#' => $key + 1,
                    'Student ID' => $data['student']->student_unique_id,
                    'Student Name' => $data['student']->full_name_in_english_block_letter,
                    'Fee Head' => $data['fee_head']->name,
                    'Amount' => '৳' . number_format($data['amount']),
                    'Payment Date' => \Carbon\Carbon::parse($data['payment_date'])->format('d-m-Y'),
                    'Status' => $data['status']
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
