<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'paymentMethod', 'purchaseItems.item', 'purchaseItems.unit', 'user']);

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

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('item_id')) {
            $query->whereHas('purchaseItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $totalNetAmount = $query->sum('net_total');
        $totalPaidAmount = $query->sum('paid');
        $totalDueAmount = $query->sum('due');
        $purchases = $query->latest('date')->paginate($perPage);
        
        $suppliers = Supplier::all();
        $items = Item::all();
        $paymentMethods = PaymentMethod::all();

        return view('content.report.purchase-report', compact(
            'purchases', 
            'suppliers', 
            'items', 
            'paymentMethods',
            'totalNetAmount',
            'totalPaidAmount',
            'totalDueAmount',
            'perPage'
        ));
    }

    public function ajax(Request $request)
    {
        $query = Purchase::with(['supplier', 'paymentMethod', 'purchaseItems.item', 'purchaseItems.unit', 'user']);

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

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('item_id')) {
            $query->whereHas('purchaseItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $totalNetAmount = $query->sum('net_total');
        $totalPaidAmount = $query->sum('paid');
        $totalDueAmount = $query->sum('due');
        $purchases = $query->latest('date')->paginate($perPage);

        return response()->json([
            'purchases' => $purchases,
            'totalNetAmount' => $totalNetAmount,
            'totalPaidAmount' => $totalPaidAmount,
            'totalDueAmount' => $totalDueAmount,
            'html' => view('content.report.purchase-report-table', compact('purchases', 'totalNetAmount', 'totalPaidAmount', 'totalDueAmount'))->render()
        ]);
    }

    public function printList(Request $request)
    {
        $query = Purchase::with(['supplier', 'paymentMethod', 'purchaseItems.item', 'purchaseItems.unit', 'user']);

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

        if ($request->filled('item_id')) {
            $query->whereHas('purchaseItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        // Get all purchases (no pagination for print)
        $purchases = $query->latest('date')->get();
        $totalNetAmount = $purchases->sum('net_total');
        $totalPaidAmount = $purchases->sum('paid');
        $totalDueAmount = $purchases->sum('due');

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('supplier_id')) {
            $supplier = Supplier::find($request->supplier_id);
            $filterInfo['supplier'] = $supplier ? $supplier->supplier_name : 'N/A';
        }
        if ($request->filled('item_id')) {
            $item = Item::find($request->item_id);
            $filterInfo['item'] = $item ? $item->item_name : 'N/A';
        }
        if ($request->filled('payment_method_id')) {
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
            $filterInfo['payment_method'] = $paymentMethod ? $paymentMethod->payment_method_name : 'N/A';
        }
        if ($request->filled('purchase_number')) {
            $filterInfo['purchase_number'] = $request->purchase_number;
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

        return view('content.report.purchase-report-print-list', compact('purchases', 'totalNetAmount', 'totalPaidAmount', 'totalDueAmount', 'filterInfo', 'request'));
    }

    public function pdf(Request $request)
    {
        $query = Purchase::with(['supplier', 'paymentMethod', 'purchaseItems.item', 'purchaseItems.unit', 'user']);

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

        if ($request->filled('item_id')) {
            $query->whereHas('purchaseItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $purchases = $query->latest('date')->get();
        $totalNetAmount = $purchases->sum('net_total');
        $totalPaidAmount = $purchases->sum('paid');
        $totalDueAmount = $purchases->sum('due');
        $appSetting = AppSetting::first();

        $pdf = Pdf::loadView('content.report.purchase-report-pdf', compact('purchases', 'totalNetAmount', 'totalPaidAmount', 'totalDueAmount', 'appSetting', 'request'));
        return $pdf->stream('purchase-report.pdf');
    }

    public function excel(Request $request)
    {
        $query = Purchase::with(['supplier', 'paymentMethod', 'purchaseItems.item', 'purchaseItems.unit', 'user']);

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

        if ($request->filled('item_id')) {
            $query->whereHas('purchaseItems', function ($itemQuery) use ($request) {
                $itemQuery->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $purchases = $query->latest('date')->get();

        $fileName = "purchase-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Purchase Number', 'Date', 'Supplier', 'Payment Method', 'Items', 'Net Total', 'Paid Amount', 'Due Amount', 'Remarks');

        $callback = function() use($purchases, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($purchases as $purchase) {
                $items = $purchase->purchaseItems->map(function($item) {
                    return $item->item->item_name . ' (' . $item->quantity . ' ' . ($item->unit ? $item->unit->unit_name : 'pcs') . ')';
                })->implode(', ');

                $row['Purchase Number'] = $purchase->purchase_number;
                $row['Date'] = $purchase->date;
                $row['Supplier'] = $purchase->supplier ? $purchase->supplier->supplier_name : '';
                $row['Payment Method'] = $purchase->paymentMethod ? $purchase->paymentMethod->payment_method_name : '';
                $row['Items'] = $items;
                $row['Net Total'] = $purchase->net_total;
                $row['Paid Amount'] = $purchase->paid;
                $row['Due Amount'] = $purchase->due;
                $row['Remarks'] = $purchase->remarks;

                fputcsv($file, array($row['Purchase Number'], $row['Date'], $row['Supplier'], $row['Payment Method'], $row['Items'], $row['Net Total'], $row['Paid Amount'], $row['Due Amount'], $row['Remarks']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
