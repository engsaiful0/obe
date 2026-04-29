<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseItem;
use App\Models\IssueItem;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockReportController extends Controller
{
    public function index(Request $request)
    {
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

        $query = Item::with(['purchaseItems', 'issueItems']);

        if ($request->filled('item_id')) {
            $query->where('id', $request->item_id);
        }

        $items = $query->get();

        // Calculate stock for each item
        $stockData = [];
        foreach ($items as $item) {
            $openingStock = $item->opening_stock ?? 0;
            
            // Calculate total purchased quantity
            $purchasedQuery = PurchaseItem::where('item_id', $item->id);
            if ($fromDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalPurchased = $purchasedQuery->sum('quantity');

            // Calculate total issued quantity
            $issuedQuery = IssueItem::where('item_id', $item->id);
            if ($fromDate) {
                $issuedQuery->whereHas('issue', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $issuedQuery->whereHas('issue', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalIssued = $issuedQuery->sum('quantity');

            // Calculate current stock
            $currentStock = $openingStock + $totalPurchased - $totalIssued;

            // Only include items that have stock activity or opening stock
            if ($openingStock > 0 || $totalPurchased > 0 || $totalIssued > 0) {
                $stockData[] = [
                    'item' => $item,
                    'opening_stock' => $openingStock,
                    'total_purchased' => $totalPurchased,
                    'total_issued' => $totalIssued,
                    'current_stock' => $currentStock,
                    'total_purchased_amount' => $purchasedQuery->sum('total_price'),
                    'last_purchase_date' => $purchasedQuery->whereHas('purchase')->orderBy('created_at', 'desc')->first()?->purchase?->date,
                    'last_issue_date' => $issuedQuery->whereHas('issue')->orderBy('created_at', 'desc')->first()?->issue?->date,
                ];
            }
        }

        // Sort by current stock (descending)
        usort($stockData, function($a, $b) {
            return $b['current_stock'] <=> $a['current_stock'];
        });

        // Paginate the results
        $totalItems = count($stockData);
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedData = array_slice($stockData, $offset, $perPage);
        
        // Create pagination object
        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $totalItems,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        $items = collect($paginatedData);
        $allItems = Item::all();

        return view('content.report.stock-report', compact(
            'items',
            'allItems',
            'pagination',
            'perPage'
        ));
    }

    public function ajax(Request $request)
    {
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

        $query = Item::with(['purchaseItems', 'issueItems']);

        if ($request->filled('item_id')) {
            $query->where('id', $request->item_id);
        }

        $items = $query->get();

        // Calculate stock for each item
        $stockData = [];
        foreach ($items as $item) {
            $openingStock = $item->opening_stock ?? 0;
            
            // Calculate total purchased quantity
            $purchasedQuery = PurchaseItem::where('item_id', $item->id);
            if ($fromDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalPurchased = $purchasedQuery->sum('quantity');

            // Calculate total issued quantity
            $issuedQuery = IssueItem::where('item_id', $item->id);
            if ($fromDate) {
                $issuedQuery->whereHas('issue', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $issuedQuery->whereHas('issue', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalIssued = $issuedQuery->sum('quantity');

            // Calculate current stock
            $currentStock = $openingStock + $totalPurchased - $totalIssued;

            // Only include items that have stock activity or opening stock
            if ($openingStock > 0 || $totalPurchased > 0 || $totalIssued > 0) {
                $stockData[] = [
                    'item' => $item,
                    'opening_stock' => $openingStock,
                    'total_purchased' => $totalPurchased,
                    'total_issued' => $totalIssued,
                    'current_stock' => $currentStock,
                    'total_purchased_amount' => $purchasedQuery->sum('total_price'),
                    'last_purchase_date' => $purchasedQuery->whereHas('purchase')->orderBy('created_at', 'desc')->first()?->purchase?->date,
                    'last_issue_date' => $issuedQuery->whereHas('issue')->orderBy('created_at', 'desc')->first()?->issue?->date,
                ];
            }
        }

        // Sort by current stock (descending)
        usort($stockData, function($a, $b) {
            return $b['current_stock'] <=> $a['current_stock'];
        });

        // Paginate the results
        $totalItems = count($stockData);
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedData = array_slice($stockData, $offset, $perPage);
        
        // Create pagination object
        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $totalItems,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        $items = collect($paginatedData);

        return response()->json([
            'items' => $items,
            'pagination' => $pagination,
            'html' => view('content.report.stock-report-table', compact('items', 'pagination'))->render()
        ]);
    }

    public function printList(Request $request)
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

        $query = Item::with(['purchaseItems', 'issueItems']);

        if ($request->filled('item_id')) {
            $query->where('id', $request->item_id);
        }

        $items = $query->get();

        // Calculate stock for each item
        $stockData = [];
        foreach ($items as $item) {
            $openingStock = $item->opening_stock ?? 0;
            
            // Calculate total purchased quantity
            $purchasedQuery = PurchaseItem::where('item_id', $item->id);
            if ($fromDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalPurchased = $purchasedQuery->sum('quantity');

            // Calculate total issued quantity
            $issuedQuery = IssueItem::where('item_id', $item->id);
            if ($fromDate) {
                $issuedQuery->whereHas('issue', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $issuedQuery->whereHas('issue', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalIssued = $issuedQuery->sum('quantity');

            // Calculate current stock
            $currentStock = $openingStock + $totalPurchased - $totalIssued;

            // Only include items that have stock activity or opening stock
            if ($openingStock > 0 || $totalPurchased > 0 || $totalIssued > 0) {
                $stockData[] = [
                    'item' => $item,
                    'opening_stock' => $openingStock,
                    'total_purchased' => $totalPurchased,
                    'total_issued' => $totalIssued,
                    'current_stock' => $currentStock,
                    'total_purchased_amount' => $purchasedQuery->sum('total_price'),
                    'last_purchase_date' => $purchasedQuery->whereHas('purchase')->orderBy('created_at', 'desc')->first()?->purchase?->date,
                    'last_issue_date' => $issuedQuery->whereHas('issue')->orderBy('created_at', 'desc')->first()?->issue?->date,
                ];
            }
        }

        // Sort by current stock (descending)
        usort($stockData, function($a, $b) {
            return $b['current_stock'] <=> $a['current_stock'];
        });

        $items = collect($stockData);

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('item_id')) {
            $item = Item::find($request->item_id);
            $filterInfo['item'] = $item ? $item->item_name : 'N/A';
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

        return view('content.report.stock-report-print-list', compact('items', 'filterInfo', 'request'));
    }

    public function pdf(Request $request)
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

        $query = Item::with(['purchaseItems', 'issueItems']);

        if ($request->filled('item_id')) {
            $query->where('id', $request->item_id);
        }

        $items = $query->get();

        // Calculate stock for each item
        $stockData = [];
        foreach ($items as $item) {
            $openingStock = $item->opening_stock ?? 0;
            
            // Calculate total purchased quantity
            $purchasedQuery = PurchaseItem::where('item_id', $item->id);
            if ($fromDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalPurchased = $purchasedQuery->sum('quantity');

            // Calculate total issued quantity
            $issuedQuery = IssueItem::where('item_id', $item->id);
            if ($fromDate) {
                $issuedQuery->whereHas('issue', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $issuedQuery->whereHas('issue', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalIssued = $issuedQuery->sum('quantity');

            // Calculate current stock
            $currentStock = $openingStock + $totalPurchased - $totalIssued;

            // Only include items that have stock activity or opening stock
            if ($openingStock > 0 || $totalPurchased > 0 || $totalIssued > 0) {
                $stockData[] = [
                    'item' => $item,
                    'opening_stock' => $openingStock,
                    'total_purchased' => $totalPurchased,
                    'total_issued' => $totalIssued,
                    'current_stock' => $currentStock,
                    'total_purchased_amount' => $purchasedQuery->sum('total_price'),
                    'last_purchase_date' => $purchasedQuery->whereHas('purchase')->orderBy('created_at', 'desc')->first()?->purchase?->date,
                    'last_issue_date' => $issuedQuery->whereHas('issue')->orderBy('created_at', 'desc')->first()?->issue?->date,
                ];
            }
        }

        // Sort by current stock (descending)
        usort($stockData, function($a, $b) {
            return $b['current_stock'] <=> $a['current_stock'];
        });

        $items = collect($stockData);
        $appSetting = AppSetting::first();

        $pdf = Pdf::loadView('content.report.stock-report-pdf', compact('items', 'appSetting', 'request'));
        return $pdf->stream('stock-report.pdf');
    }

    public function excel(Request $request)
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

        $query = Item::with(['purchaseItems', 'issueItems']);

        if ($request->filled('item_id')) {
            $query->where('id', $request->item_id);
        }

        $items = $query->get();

        // Calculate stock for each item
        $stockData = [];
        foreach ($items as $item) {
            $openingStock = $item->opening_stock ?? 0;
            
            // Calculate total purchased quantity
            $purchasedQuery = PurchaseItem::where('item_id', $item->id);
            if ($fromDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $purchasedQuery->whereHas('purchase', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalPurchased = $purchasedQuery->sum('quantity');

            // Calculate total issued quantity
            $issuedQuery = IssueItem::where('item_id', $item->id);
            if ($fromDate) {
                $issuedQuery->whereHas('issue', function($q) use ($fromDate) {
                    $q->whereDate('date', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $issuedQuery->whereHas('issue', function($q) use ($toDate) {
                    $q->whereDate('date', '<=', $toDate);
                });
            }
            $totalIssued = $issuedQuery->sum('quantity');

            // Calculate current stock
            $currentStock = $openingStock + $totalPurchased - $totalIssued;

            // Only include items that have stock activity or opening stock
            if ($openingStock > 0 || $totalPurchased > 0 || $totalIssued > 0) {
                $stockData[] = [
                    'item' => $item,
                    'opening_stock' => $openingStock,
                    'total_purchased' => $totalPurchased,
                    'total_issued' => $totalIssued,
                    'current_stock' => $currentStock,
                    'total_purchased_amount' => $purchasedQuery->sum('total_price'),
                    'last_purchase_date' => $purchasedQuery->whereHas('purchase')->orderBy('created_at', 'desc')->first()?->purchase?->date,
                    'last_issue_date' => $issuedQuery->whereHas('issue')->orderBy('created_at', 'desc')->first()?->issue?->date,
                ];
            }
        }

        // Sort by current stock (descending)
        usort($stockData, function($a, $b) {
            return $b['current_stock'] <=> $a['current_stock'];
        });

        $items = collect($stockData);

        $fileName = "stock-report.csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Item Name', 'Opening Stock', 'Total Purchased', 'Total Issued', 'Current Stock', 'Total Purchased Amount', 'Last Purchase Date', 'Last Issue Date');

        $callback = function() use($items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($items as $stockItem) {
                $row['Item Name'] = $stockItem['item']->item_name;
                $row['Opening Stock'] = number_format($stockItem['opening_stock'], 2);
                $row['Total Purchased'] = number_format($stockItem['total_purchased'], 2);
                $row['Total Issued'] = number_format($stockItem['total_issued'], 2);
                $row['Current Stock'] = number_format($stockItem['current_stock'], 2);
                $row['Total Purchased Amount'] = number_format($stockItem['total_purchased_amount'], 2);
                $row['Last Purchase Date'] = $stockItem['last_purchase_date'] ? $stockItem['last_purchase_date']->format('Y-m-d') : '';
                $row['Last Issue Date'] = $stockItem['last_issue_date'] ? $stockItem['last_issue_date']->format('Y-m-d') : '';

                fputcsv($file, array($row['Item Name'], $row['Opening Stock'], $row['Total Purchased'], $row['Total Issued'], $row['Current Stock'], $row['Total Purchased Amount'], $row['Last Purchase Date'], $row['Last Issue Date']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
