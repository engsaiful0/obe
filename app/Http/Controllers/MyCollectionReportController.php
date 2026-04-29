<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeCollect;
use App\Models\Expense;
use App\Models\User;
use App\Models\ExpenseHead;
use App\Models\FeeHead;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MyCollectionReportController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $userRole = $currentUser->rule->name ?? '';

        // Get all users for superadmin filter
        $users = collect();
        if ($userRole === 'Super Admin') {
            $users = User::with('rule')->get();
        }

        // Build fee collection query
        $feeCollectionQuery = FeeCollect::with(['student', 'academic_year', 'semester', 'payment_method', 'user'])->where('date', date('Y-m-d'));

        // Build expense query
        $expenseQuery = Expense::with(['expenseHead', 'user'])->where('expense_date', date('Y-m-d'));

        // Apply user filter based on role
        if ($userRole === 'Super Admin') {
            // Super admin can see all users' data
            if ($request->filled('user_id')) {
                $feeCollectionQuery->where('user_id', $request->user_id);
                $expenseQuery->where('user_id', $request->user_id);
            }
        } else {
            // Accountant and other users can only see their own data
            $feeCollectionQuery->where('user_id', $currentUser->id);
            $expenseQuery->where('user_id', $currentUser->id);
        }

        // Apply date range filters
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

        // Apply date filters to fee collections
        if ($fromDate) {
            $feeCollectionQuery->whereDate('date', '>=', $fromDate);
        }
        if ($toDate) {
            $feeCollectionQuery->whereDate('date', '<=', $toDate);
        }

        // Apply date filters to expenses
        if ($fromDate) {
            $expenseQuery->whereDate('expense_date', '>=', $fromDate);
        }
        if ($toDate) {
            $expenseQuery->whereDate('expense_date', '<=', $toDate);
        }

        // Calculate totals BEFORE pagination
        $totalFeeCollection = (clone $feeCollectionQuery)->sum('total_amount');
        $totalExpenses = (clone $expenseQuery)->sum('amount');

        // Get paginated results
        $perPage = $request->input('per_page', 10);
        $feeCollections = $feeCollectionQuery->latest('date')->paginate($perPage, ['*'], 'fee_page');
        $expenses = $expenseQuery->latest('expense_date')->paginate($perPage, ['*'], 'expense_page');
        $netAmount = $totalFeeCollection - $totalExpenses;

        // Process fee heads for each collection
        foreach ($feeCollections as $collection) {
            $feeHeadDetails = [];
            $feeHeads = is_string($collection->fee_heads) 
                ? json_decode($collection->fee_heads, true) 
                : $collection->fee_heads;

            if (is_array($feeHeads)) {
                foreach ($feeHeads as $feeHead) {
                    if (is_numeric($feeHead)) {
                        $feeHeadModel = FeeHead::find($feeHead);
                    } elseif (is_array($feeHead) && isset($feeHead['id'])) {
                        $feeHeadModel = FeeHead::find($feeHead['id']);
                    } else {
                        $feeHeadModel = null;
                    }

                    if ($feeHeadModel) {
                        $feeHeadDetails[] = (object)[
                            'id' => $feeHeadModel->id,
                            'name' => $feeHeadModel->name,
                            'amount' => $feeHeadModel->amount,
                        ];
                    }
                }
            }
            $collection->fee_heads = $feeHeadDetails;
        }

        return view('content.report.my-collection-report', compact(
            'feeCollections', 
            'expenses', 
            'users', 
            'totalFeeCollection', 
            'totalExpenses', 
            'netAmount',
            'userRole'
        ));
    }

    public function exportExcel(Request $request)
    {
        $currentUser = Auth::user();
        $userRole = $currentUser->rule->name ?? '';

        // Build queries (same logic as index method)
        $feeCollectionQuery = FeeCollect::with(['student', 'academic_year', 'semester', 'payment_method', 'user']);
        $expenseQuery = Expense::with(['expenseHead', 'user']);

        // Apply user filter
        if ($userRole === 'Super Admin') {
            if ($request->filled('user_id')) {
                $feeCollectionQuery->where('user_id', $request->user_id);
                $expenseQuery->where('user_id', $request->user_id);
            }
        } else {
            $feeCollectionQuery->where('user_id', $currentUser->id);
            $expenseQuery->where('user_id', $currentUser->id);
        }

        // Apply date filters
        $this->applyDateFilters($feeCollectionQuery, $expenseQuery, $request);

        $feeCollections = $feeCollectionQuery->latest('date')->get();
        $expenses = $expenseQuery->latest('expense_date')->get();

        $fileName = "my-collection-report.csv";
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function() use($feeCollections, $expenses) {
            $file = fopen('php://output', 'w');
            
            // Fee Collections Section
            fputcsv($file, ['FEE COLLECTIONS']);
            fputcsv($file, ['Student Name', 'Academic Year', 'Semester', 'Payment Method', 'Amount', 'Date', 'Collected By']);
            
            foreach ($feeCollections as $collection) {
                fputcsv($file, [
                    $collection->student->full_name_in_english_block_letter ?? '',
                    $collection->academic_year->academic_year_name ?? '',
                    $collection->semester->semester_name ?? '',
                    $collection->payment_method->payment_method_name ?? '',
                    $collection->total_amount,
                    $collection->date,
                    $collection->user->name ?? ''
                ]);
            }
            
            fputcsv($file, []); // Empty row
            
            // Expenses Section
            fputcsv($file, ['EXPENSES']);
            fputcsv($file, ['Expense Head', 'Date', 'Remarks', 'Amount', 'Created By']);
            
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expenseHead->name ?? '',
                    $expense->expense_date,
                    $expense->remarks,
                    $expense->amount,
                    $expense->user->name ?? ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $currentUser = Auth::user();
        $userRole = $currentUser->rule->name ?? '';

        // Build queries
        $feeCollectionQuery = FeeCollect::with(['student', 'academic_year', 'semester', 'payment_method', 'user']);
        $expenseQuery = Expense::with(['expenseHead', 'user']);

        // Apply user filter
        if ($userRole === 'Super Admin') {
            if ($request->filled('user_id')) {
                $feeCollectionQuery->where('user_id', $request->user_id);
                $expenseQuery->where('user_id', $request->user_id);
            }
        } else {
            $feeCollectionQuery->where('user_id', $currentUser->id);
            $expenseQuery->where('user_id', $currentUser->id);
        }

        // Apply date filters
        $this->applyDateFilters($feeCollectionQuery, $expenseQuery, $request);

        // Calculate totals BEFORE getting data
        $totalFeeCollection = (clone $feeCollectionQuery)->sum('total_amount');
        $totalExpenses = (clone $expenseQuery)->sum('amount');
        
        $feeCollections = $feeCollectionQuery->latest('date')->get();
        $expenses = $expenseQuery->latest('expense_date')->get();
        $netAmount = $totalFeeCollection - $totalExpenses;

        $appSetting = AppSetting::first();
        $selectedUser = null;
        if ($request->filled('user_id')) {
            $selectedUser = User::find($request->user_id);
        }

        $pdf = Pdf::loadView('content.report.my-collection-report-pdf', compact(
            'feeCollections', 
            'expenses', 
            'totalFeeCollection', 
            'totalExpenses', 
            'netAmount',
            'appSetting',
            'selectedUser',
            'userRole'
        ));
        
        return $pdf->stream('my-collection-report.pdf');
    }

    private function applyDateFilters($feeCollectionQuery, $expenseQuery, $request)
    {
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

        if ($fromDate) {
            $feeCollectionQuery->whereDate('date', '>=', $fromDate);
            $expenseQuery->whereDate('expense_date', '>=', $fromDate);
        }
        if ($toDate) {
            $feeCollectionQuery->whereDate('date', '<=', $toDate);
            $expenseQuery->whereDate('expense_date', '<=', $toDate);
        }
    }
}
