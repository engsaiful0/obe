<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\MonthlySalarySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalarySheetController extends Controller
{
    public function index(Request $request)
    {
        $year = (int)($request->get('year') ?: date('Y'));
        $month = (int)($request->get('month') ?: date('n'));

        $setting = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $employees = Employee::where('user_id', Auth::id())
            ->orderBy('id', 'asc')
            ->paginate(20)
            ->through(function ($employee) use ($year, $month, $setting) {
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate = date('Y-m-t', strtotime($startDate));

                $presentDays = EmployeeAttendance::where('employee_id', $employee->id)
                    ->whereBetween('attendance_date', [$startDate, $endDate])
                    ->whereIn('status', ['present', 'late'])
                    ->count();

                $overtimeHours = 0; // Placeholder if overtime tracked separately
                $deductions = 0; // Placeholder for manual deductions

                $basicSalary = (float)($employee->basic_salary ?? 0);

                $calc = null;
                if ($setting) {
                    $calc = $setting->calculateMonthlySalary(
                        $basicSalary,
                        $presentDays,
                        $overtimeHours,
                        $deductions
                    );
                }

                $employee->present_days = $presentDays;
                $employee->salary_calculation = $calc;

                return $employee;
            });

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = \Carbon\Carbon::createFromDate(null, $i, 1)->format('F');
        }

        if ($request->ajax()) {
            return view('content.hr.partials.salary-sheet-table', compact('employees', 'year', 'month', 'setting'));
        }

        return view('content.hr.salary-sheet', compact('employees', 'year', 'month', 'months', 'setting'));
    }

    public function printList(Request $request)
    {
        $year = (int)($request->get('year') ?: date('Y'));
        $month = (int)($request->get('month') ?: date('n'));

        $setting = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $employees = Employee::where('user_id', Auth::id())->orderBy('id', 'asc')->get();

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $employees->transform(function ($employee) use ($year, $month, $setting, $startDate, $endDate) {
            $presentDays = EmployeeAttendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->whereIn('status', ['present', 'late'])
                ->count();

            $basicSalary = (float)($employee->basic_salary ?? 0);
            $calc = $setting ? $setting->calculateMonthlySalary($basicSalary, $presentDays, 0, 0) : null;

            $employee->present_days = $presentDays;
            $employee->salary_calculation = $calc;
            return $employee;
        });

        // Calculate totals
        $totals = [
            'total_basic' => $employees->sum(function($emp) {
                return $emp->salary_calculation['basic_salary'] ?? ($emp->basic_salary ?? 0);
            }),
            'total_base_salary' => $employees->sum(function($emp) {
                return $emp->salary_calculation['base_salary'] ?? 0;
            }),
            'total_overtime' => $employees->sum(function($emp) {
                return $emp->salary_calculation['overtime_amount'] ?? 0;
            }),
            'total_deductions' => $employees->sum(function($emp) {
                return $emp->salary_calculation['deductions'] ?? 0;
            }),
            'total_salary' => $employees->sum(function($emp) {
                return $emp->salary_calculation['total_salary'] ?? 0;
            }),
        ];

        return view('content.hr.salary-sheet-print-list', compact('employees', 'year', 'month', 'setting', 'totals'));
    }

    public function exportPdf(Request $request)
    {
        $year = (int)($request->get('year') ?: date('Y'));
        $month = (int)($request->get('month') ?: date('n'));

        $setting = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $employees = Employee::where('user_id', Auth::id())->orderBy('id', 'asc')->get();

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $employees->transform(function ($employee) use ($year, $month, $setting, $startDate, $endDate) {
            $presentDays = EmployeeAttendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->whereIn('status', ['present', 'late'])
                ->count();

            $basicSalary = (float)($employee->basic_salary ?? 0);
            $calc = $setting ? $setting->calculateMonthlySalary($basicSalary, $presentDays, 0, 0) : null;

            $employee->present_days = $presentDays;
            $employee->salary_calculation = $calc;
            return $employee;
        });

        $pdf = Pdf::loadView('content.hr.salary-sheet-pdf', compact('employees', 'year', 'month', 'setting'));
        return $pdf->download('salary-sheet-' . $year . '-' . sprintf('%02d', $month) . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $year = (int)($request->get('year') ?: date('Y'));
        $month = (int)($request->get('month') ?: date('n'));

        $setting = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $rows = [];
        $employees = Employee::where('user_id', Auth::id())->orderBy('id', 'asc')->get();
        foreach ($employees as $employee) {
            $presentDays = EmployeeAttendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->whereIn('status', ['present', 'late'])
                ->count();

            $basicSalary = (float)($employee->basic_salary ?? 0);
            $calc = $setting ? $setting->calculateMonthlySalary($basicSalary, $presentDays, 0, 0) : null;

            $rows[] = [
                'Employee' => $employee->employee_name ?? ($employee->full_name ?? 'Employee #' . $employee->id),
                'Present Days' => $presentDays,
                'Basic Salary' => $basicSalary,
                'Daily Rate' => $calc['daily_rate'] ?? 0,
                'Base Salary' => $calc['base_salary'] ?? 0,
                'Overtime Hours' => $calc['overtime_hours'] ?? 0,
                'Overtime Amount' => $calc['overtime_amount'] ?? 0,
                'Deductions' => $calc['deductions'] ?? 0,
                'Total Salary' => $calc['total_salary'] ?? 0,
            ];
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="salary-sheet-' . $year . '-' . sprintf('%02d', $month) . '.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            if (!empty($rows)) {
                fputcsv($out, array_keys($rows[0]));
            }
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}


