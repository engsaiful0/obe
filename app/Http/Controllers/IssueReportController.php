<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Unit;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IssueReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Issue::with(['employee', 'issueItems.item', 'issueItems.unit', 'user']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $perPage = $request->input('per_page', 10);

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

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('item_id')) {
            $query->whereHas('issueItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('issue_number')) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $totalItemsIssued = $query->with('issueItems')->get()->sum(function($issue) {
            return $issue->issueItems->sum('quantity');
        });
        
        $issues = $query->latest('date')->paginate($perPage);
        
        $employees = Employee::all();
        $items = Item::all();

        return view('content.report.issue-report', compact(
            'issues', 
            'employees', 
            'items',
            'totalItemsIssued',
            'perPage'
        ));
    }

    public function ajax(Request $request)
    {
        $query = Issue::with(['employee', 'issueItems.item', 'issueItems.unit', 'user']);

        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $perPage = $request->input('per_page', 10);

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

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('item_id')) {
            $query->whereHas('issueItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('issue_number')) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $totalItemsIssued = $query->with('issueItems')->get()->sum(function($issue) {
            return $issue->issueItems->sum('quantity');
        });
        
        $issues = $query->latest('date')->paginate($perPage);

        return response()->json([
            'issues' => $issues,
            'totalItemsIssued' => $totalItemsIssued,
            'html' => view('content.report.issue-report-table', compact('issues', 'totalItemsIssued'))->render()
        ]);
    }

    public function printList(Request $request)
    {
        $query = Issue::with(['employee', 'issueItems.item', 'issueItems.unit', 'user']);

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

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('item_id')) {
            $query->whereHas('issueItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('issue_number')) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        // Get all issues (no pagination for print)
        $issues = $query->latest('date')->get();
        $totalItemsIssued = $issues->sum(function($issue) {
            return $issue->issueItems->sum('quantity');
        });

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('employee_id')) {
            $employee = Employee::find($request->employee_id);
            $filterInfo['employee'] = $employee ? $employee->employee_name : 'N/A';
        }
        if ($request->filled('item_id')) {
            $item = Item::find($request->item_id);
            $filterInfo['item'] = $item ? $item->item_name : 'N/A';
        }
        if ($request->filled('issue_number')) {
            $filterInfo['issue_number'] = $request->issue_number;
        }
        if ($request->filled('date_range')) {
            $filterInfo['date_range'] = ucfirst(str_replace('_', ' ', $request->date_range));
        }
        if ($request->filled('from_date')) {
            $filterInfo['from_date'] = Carbon::parse($request->from_date)->format('d M Y');
        }
        if ($request->filled('to_date')) {
            $filterInfo['to_date'] = Carbon::parse($request->to_date)->format('d M Y');
        }

        return view('content.report.issue-report-print-list', compact('issues', 'totalItemsIssued', 'filterInfo', 'request'));
    }

    public function pdf(Request $request)
    {
        $query = Issue::with(['employee', 'issueItems.item', 'issueItems.unit', 'user']);

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

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('item_id')) {
            $query->whereHas('issueItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('issue_number')) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $issues = $query->latest('date')->get();
        $totalItemsIssued = $issues->sum(function($issue) {
            return $issue->issueItems->sum('quantity');
        });
        $appSetting = AppSetting::first();

        $pdf = Pdf::loadView('content.report.issue-report-pdf', compact('issues', 'totalItemsIssued', 'appSetting', 'request'));
        return $pdf->stream('issue-report.pdf');
    }

    public function excel(Request $request)
    {
        $query = Issue::with(['employee', 'issueItems.item', 'issueItems.unit', 'user']);

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

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('item_id')) {
            $query->whereHas('issueItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('issue_number')) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $issues = $query->latest('date')->get();

        $fileName = "issue-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Issue Number', 'Date', 'Employee', 'Items', 'Total Quantity', 'Remarks');

        $callback = function() use($issues, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($issues as $issue) {
                $items = $issue->issueItems->map(function($item) {
                    return $item->item->item_name . ' (' . $item->quantity . ' ' . ($item->unit ? $item->unit->unit_name : 'pcs') . ')';
                })->implode(', ');

                $totalQuantity = $issue->issueItems->sum('quantity');

                $row['Issue Number'] = $issue->issue_number;
                $row['Date'] = $issue->date;
                $row['Employee'] = $issue->employee ? $issue->employee->employee_name : '';
                $row['Items'] = $items;
                $row['Total Quantity'] = $totalQuantity;
                $row['Remarks'] = $issue->remarks;

                fputcsv($file, array($row['Issue Number'], $row['Date'], $row['Employee'], $row['Items'], $row['Total Quantity'], $row['Remarks']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
