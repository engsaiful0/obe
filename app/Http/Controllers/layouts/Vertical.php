<?php

namespace App\Http\Controllers\layouts;

use App\Http\Controllers\Controller;


use App\Models\Expense;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\BusHelper;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\Damage;
use App\Models\Reward;
use App\Models\Punishment;
use App\Models\BusTrip;
use App\Models\BusSchedule;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\PurchaseItem;
use App\Models\DamageItem;
use App\Models\MonthlyBill;
use App\Models\Supplier;
use App\Models\Status as StatusModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Vertical extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'vertical'];

        // Get current date and date ranges
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();
        $lastYear = Carbon::now()->subYear()->startOfYear();
        // Get active status ID for buses
        $activeStatus = StatusModel::query()
            ->whereHas('relatedTo', function ($q) {
                $q->whereRaw('LOWER(name) = ?', ['bus']);
            })
            ->where('status_name', 'like', '%active%')
            ->first();

         

        $busStats = [
            'total' => Bus::count(),
            'active' => $activeStatus ? Bus::where('status_id', $activeStatus->id)->count() : 0,
            'maintenance' => $activeStatus ? Bus::where('status_id', $activeStatus->id)->count() : 0,
            'inactive' => $activeStatus ? Bus::where('status_id', $activeStatus->id)->count() : 0,
            'this_month' => $activeStatus ? Bus::where('status_id', $activeStatus->id)->where('created_at', '>=', $thisMonth)->count() : 0,
            'this_year' => $activeStatus ? Bus::where('status_id', $activeStatus->id)->where('created_at', '>=', $thisYear)->count() : 0,
        ];

        $busByType = Bus::query()
            ->with('busType')
            ->select('bus_type_id', DB::raw('count(*) as total'))
            ->groupBy('bus_type_id')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->busType->bus_type_name ?? 'Unknown',
                    'count' => $item->total
                ];
            });

        $driverStats = [
            'total' => Driver::count(),
            'active' => Driver::where('status', 'active')->count(),
            'inactive' => Driver::where('status', 'inactive')->count(),
            'this_month' => Driver::where('created_at', '>=', $thisMonth)->count(),
            'this_year' => Driver::where('created_at', '>=', $thisYear)->count(),
        ];

        $activeBusHelperStatus = StatusModel::query()
            ->whereHas('relatedTo', function ($q) {
                $q->whereRaw('LOWER(name) = ?', ['bus-helper']);
            })
            ->where('status_name', 'like', '%active%')
            ->first();
        
        $busHelperQuery = BusHelper::query();
        $assistantStats = [
            'total' => (clone $busHelperQuery)->count(),
            'active' => $activeBusHelperStatus ? (clone $busHelperQuery)->where('status_id', $activeBusHelperStatus->id)->count() : 0,
            'inactive' => $activeBusHelperStatus ? (clone $busHelperQuery)->where('status_id', $activeBusHelperStatus->id)->count() : 0,
            'this_month' => $activeBusHelperStatus ? (clone $busHelperQuery)->where('status_id', $activeBusHelperStatus->id)->where('created_at', '>=', $thisMonth)->count() : 0,
        ];
        $employeeQuery = Employee::query();
  
    
        $employeeStats = [
          'total' => (clone $employeeQuery)->count(),
          'this_month' => (clone $employeeQuery)->where('created_at', '>=', $thisMonth)->count(),
          'this_year' => (clone $employeeQuery)->where('created_at', '>=', $thisYear)->count(),
        ];
        // Expense Analytics
        $expenseAnalytics = [
            'today' => Expense::whereDate('expense_date', $today)->sum('amount'),
            'this_week' => Expense::where('expense_date', '>=', $thisWeek)->sum('amount'),
            'this_month' => Expense::where('expense_date', '>=', $thisMonth)->sum('amount'),
            'last_month' => Expense::whereBetween('expense_date', [$lastMonth, $thisMonth])->sum('amount'),
        ];


        // Employee Statistics
        $employeeStats = [
            'total' => Employee::count(),
            'this_month' => Employee::where('created_at', '>=', $thisMonth)->count(),
        ];

        // Monthly Fee Collection Trend (Last 6 months)
        $monthlyFeeTrend = [];
        $monthlyFeeStats = [
            'total_collected' => 0,
            'average_monthly' => 0,
            'highest_month' => 0,
            'lowest_month' => 0,
            'growth_rate' => 0,
            'collection_efficiency' => 0
        ];




        // Monthly Expense Trend (Last 6 months)
        $monthlyExpenseTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyExpenseTrend[] = [
                'month' => $month->format('M Y'),
                'amount' => Expense::whereYear('expense_date', $month->year)
                    ->whereMonth('expense_date', $month->month)
                    ->sum('amount')
            ];
        }
        $purchaseQuery = Purchase::query();
 
    
        $purchaseStats = [
          'total' => (clone $purchaseQuery)->count(),
          'this_month' => (clone $purchaseQuery)->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)->count(),
          'this_month_amount' => (clone $purchaseQuery)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->sum('net_total'),
          'this_month_paid' => (clone $purchaseQuery)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->sum('paid'),
          'this_month_due' => (clone $purchaseQuery)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->sum('due'),
          'last_month_amount' => (clone $purchaseQuery)
            ->whereYear('date', $lastMonth->year)
            ->whereMonth('date', $lastMonth->month)
            ->sum('net_total'),
          'this_year_amount' => (clone $purchaseQuery)
            ->where('date', '>=', $thisYear)
            ->sum('net_total'),
          'total_amount' => (clone $purchaseQuery)->sum('net_total'),
          'total_due' => (clone $purchaseQuery)->sum('due'),
        ];
    
        $purchaseMonthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
          $month = Carbon::now()->subMonths($i);
          $monthQuery = clone $purchaseQuery;
          $purchaseMonthlyTrend[] = [
            'month' => $month->format('M Y'),
            'amount' => (clone $monthQuery)
              ->whereYear('date', $month->year)
              ->whereMonth('date', $month->month)
              ->sum('net_total'),
            'count' => (clone $monthQuery)
              ->whereYear('date', $month->year)
              ->whereMonth('date', $month->month)
              ->count(),
          ];
        }
    
        // ============ ISSUE MODULE STATISTICS ============
        $issueQuery = Issue::query();
        
        $issueStats = [
          'total' => (clone $issueQuery)->count(),
          'this_month' => (clone $issueQuery)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->count(),
          'this_year' => (clone $issueQuery)
            ->where('date', '>=', $thisYear)
            ->count(),
        ];
    
        // Get issue items statistics
        $issueItemsStats = [
          'this_month_quantity' => IssueItem::whereHas('issue', function($q) {
            $q->whereYear('date', Carbon::now()->year)
              ->whereMonth('date', Carbon::now()->month);
          })->sum('quantity'),
          'total_quantity' => IssueItem::sum('quantity'),
        ];
    
        // ============ DAMAGE MODULE STATISTICS ============
        $damageQuery = Damage::query();
       
        
        $damageStats = [
          'total' => (clone $damageQuery)->count(),
          'this_month' => (clone $damageQuery)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->count(),
          'total_quantity' => DB::table('damages')
            ->join('damage_items', 'damages.id', '=', 'damage_items.damage_id')
            ->sum('damage_items.quantity'),
          'total_approximate' => DB::table('damages')
            ->join('damage_items', 'damages.id', '=', 'damage_items.damage_id')
            ->sum('damage_items.approximate'),
          'this_month_quantity' => DB::table('damages')
            ->join('damage_items', 'damages.id', '=', 'damage_items.damage_id')
            ->whereYear('damages.date', Carbon::now()->year)
            ->whereMonth('damages.date', Carbon::now()->month)
            ->sum('damage_items.quantity'),
        ];
    
        // ============ EXPENSE MODULE STATISTICS ============
        $expenseStats = [
          'total' => Expense::count(),
          'this_month' => Expense::whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)->count(),
          'this_month_amount' => Expense::whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)->sum('amount'),
          'last_month_amount' => Expense::whereYear('expense_date', $lastMonth->year)
            ->whereMonth('expense_date', $lastMonth->month)->sum('amount'),
          'this_year_amount' => Expense::where('expense_date', '>=', $thisYear)->sum('amount'),
          'total_amount' => Expense::sum('amount'),
        ];
    
        $expenseMonthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
          $month = Carbon::now()->subMonths($i);
          $expenseMonthlyTrend[] = [
            'month' => $month->format('M Y'),
            'amount' => Expense::whereYear('expense_date', $month->year)
              ->whereMonth('expense_date', $month->month)
              ->sum('amount'),
          ];
        }
    
        $expenseByCategory = Expense::query()
          ->with('expenseHead')
          ->select('expense_head_id', DB::raw('sum(amount) as total'))
          ->groupBy('expense_head_id')
          ->get()
          ->map(function($item) {
            return [
              'category' => $item->expenseHead->name ?? 'Unknown',
              'amount' => $item->total
            ];
          })
          ->sortByDesc('amount')
          ->take(10);
    
        // ============ REWARD MODULE STATISTICS ============
        $rewardQuery = Reward::query();
       
        
        $rewardStats = [
          'total' => (clone $rewardQuery)->count(),
          'this_month' => (clone $rewardQuery)->where('created_at', '>=', $thisMonth)->count(),
          'this_month_amount' => (clone $rewardQuery)->where('created_at', '>=', $thisMonth)->sum('reward_amount'),
          'total_amount' => (clone $rewardQuery)->sum('reward_amount'),
          'this_year_amount' => (clone $rewardQuery)->where('created_at', '>=', $thisYear)->sum('reward_amount'),
        ];
    
        // ============ PUNISHMENT MODULE STATISTICS ============
        $punishmentQuery = Punishment::query();
      
        
        $punishmentStats = [
          'total' => (clone $punishmentQuery)->count(),
          'this_month' => (clone $punishmentQuery)
            ->whereYear('punishment_date', Carbon::now()->year)
            ->whereMonth('punishment_date', Carbon::now()->month)
            ->count(),
          'this_month_fine' => (clone $punishmentQuery)
            ->whereYear('punishment_date', Carbon::now()->year)
            ->whereMonth('punishment_date', Carbon::now()->month)
            ->sum('fine_amount'),
          'total_fine' => (clone $punishmentQuery)->sum('fine_amount'),
          'this_year_fine' => (clone $punishmentQuery)
            ->where('punishment_date', '>=', $thisYear)
            ->sum('fine_amount'),
        ];
    
        // ============ BUS TRIP STATISTICS ============
        $tripQuery = BusTrip::query();
        
        
        $tripStats = [
          'total' => (clone $tripQuery)->count(),
          'this_month' => (clone $tripQuery)
            ->whereYear('trip_date', Carbon::now()->year)
            ->whereMonth('trip_date', Carbon::now()->month)
            ->count(),
          'this_month_distance' => (clone $tripQuery)
            ->whereYear('trip_date', Carbon::now()->year)
            ->whereMonth('trip_date', Carbon::now()->month)
            ->sum('total_distance'),
          'total_distance' => (clone $tripQuery)->sum('total_distance'),
          'this_year_distance' => (clone $tripQuery)
            ->where('trip_date', '>=', $thisYear)
            ->sum('total_distance'),
          'avg_distance' => (clone $tripQuery)->avg('total_distance') ?? 0,
        ];
    
        $tripMonthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
          $month = Carbon::now()->subMonths($i);
          $monthQuery = clone $tripQuery;
          $tripMonthlyTrend[] = [
            'month' => $month->format('M Y'),
            'trips' => (clone $monthQuery)
              ->whereYear('trip_date', $month->year)
              ->whereMonth('trip_date', $month->month)
              ->count(),
            'distance' => (clone $monthQuery)
              ->whereYear('trip_date', $month->year)
              ->whereMonth('trip_date', $month->month)
              ->sum('total_distance'),
          ];
        }
        // Expense by Category
        $expenseByCategory = Expense::with('expenseHead')
            ->select('expense_head_id', DB::raw('sum(amount) as total'))
            ->groupBy('expense_head_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->expenseHead->name ?? 'Unknown',
                    'amount' => $item->total
                ];
            });

        $expenseGrowth = $expenseAnalytics['last_month'] > 0
            ? (($expenseAnalytics['this_month'] - $expenseAnalytics['last_month']) / $expenseAnalytics['last_month']) * 100
            : 0;


             // ============ BUS SCHEDULE STATISTICS ============
    try {
        $scheduleQuery = BusSchedule::query();
        
        $scheduleStats = [
          'total' => (clone $scheduleQuery)->count(),
          'active' => (clone $scheduleQuery)->where('status', 'active')->count(),
          'inactive' => (clone $scheduleQuery)->where('status', 'inactive')->count(),
        ];
      } catch (\Exception $e) {
        $scheduleStats = ['total' => 0, 'active' => 0, 'inactive' => 0];
      }
  
      // ============ ITEM & WAREHOUSE STATISTICS ============
      $itemQuery = Item::query();
      $warehouseQuery = Warehouse::query();

      
      $itemStats = [
        'total' => (clone $itemQuery)->count(),
        'total_warehouses' => (clone $warehouseQuery)->count(),
      ];
  
      // Inventory Statistics
      $inventoryStats = [
        'total_purchased_quantity' => PurchaseItem::sum('quantity'),
        'total_issued_quantity' => IssueItem::sum('quantity'),
        'total_damaged_quantity' => DamageItem::sum('quantity'),
        'this_month_purchased' => PurchaseItem::whereHas('purchase', function($q) {
          $q->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month);
        })->sum('quantity'),
        'this_month_issued' => IssueItem::whereHas('issue', function($q) {
          $q->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month);
        })->sum('quantity'),
      ];
  
      // ============ MONTHLY BILL STATISTICS ============
      try {
        $monthlyBillQuery = MonthlyBill::query();
        
        $monthlyBillStats = [
          'total' => (clone $monthlyBillQuery)->count(),
          'this_month' => (clone $monthlyBillQuery)
            ->whereYear('from_date', Carbon::now()->year)
            ->whereMonth('from_date', Carbon::now()->month)
            ->count(),
          'this_month_amount' => (clone $monthlyBillQuery)
            ->whereYear('from_date', Carbon::now()->year)
            ->whereMonth('from_date', Carbon::now()->month)
            ->sum('final_amount'),
          'total_amount' => (clone $monthlyBillQuery)->sum('final_amount'),
        ];
      } catch (\Exception $e) {
        $monthlyBillStats = ['total' => 0, 'this_month' => 0, 'this_month_amount' => 0, 'total_amount' => 0];
      }
  
      // ============ SUPPLIER STATISTICS ============
      try {
        $supplierQuery = Supplier::query();

        
        $supplierStats = [
          'total' => (clone $supplierQuery)->count(),
          'active' => (clone $supplierQuery)->where('status', 'active')->count(),
          'with_purchases' => DB::table('suppliers')
            ->join('purchases', 'suppliers.id', '=', 'purchases.supplier_id')
            ->distinct('suppliers.id')
            ->count('suppliers.id'),
        ];
      } catch (\Exception $e) {
        $supplierStats = ['total' => 0, 'active' => 0, 'with_purchases' => 0];
      }
  
      // ============ FINANCIAL SUMMARY ============
      $financialSummary = [
        'total_revenue' => ($monthlyBillStats['total_amount'] ?? 0),
        'total_expenses' => ($expenseStats['total_amount'] ?? 0) + ($purchaseStats['total_amount'] ?? 0),
        'total_rewards' => ($rewardStats['total_amount'] ?? 0),
        'total_fines' => ($punishmentStats['total_fine'] ?? 0),
        'total_damage_cost' => ($damageStats['total_approximate'] ?? 0),
        'net_profit' => ($monthlyBillStats['total_amount'] ?? 0) - ($expenseStats['total_amount'] ?? 0) - ($purchaseStats['total_amount'] ?? 0),
        'this_month_revenue' => ($monthlyBillStats['this_month_amount'] ?? 0),
        'this_month_expenses' => ($expenseStats['this_month_amount'] ?? 0) + ($purchaseStats['this_month_amount'] ?? 0),
        'this_month_profit' => ($monthlyBillStats['this_month_amount'] ?? 0) - ($expenseStats['this_month_amount'] ?? 0) - ($purchaseStats['this_month_amount'] ?? 0),
      ];
  
      // ============ RECENT ACTIVITIES ============
      $recentActivities = collect()
        ->merge(
          Bus::latest()->take(3)->get()->map(function($bus) {
            return [
              'type' => 'bus',
              'message' => "New bus {$bus->registration_number} added",
              'date' => $bus->created_at,
              'icon' => 'ti ti-bus',
              'color' => 'primary',
              'url' => route('buses.index')
            ];
          })
        )
        ->merge(
          Driver::latest()->take(3)->get()->map(function($driver) {
            return [
              'type' => 'driver',
              'message' => "Driver {$driver->full_name} added",
              'date' => $driver->created_at,
              'icon' => 'ti ti-user',
              'color' => 'success',
              'url' => route('drivers.index')
            ];
          })
        )
        ->merge(
          (clone $purchaseQuery)->latest()->take(3)->get()->map(function($purchase) {
            return [
              'type' => 'purchase',
              'message' => "Purchase #{$purchase->purchase_number} - ৳" . number_format($purchase->net_total, 2),
              'date' => $purchase->created_at,
              'icon' => 'ti ti-shopping-cart',
              'color' => 'info',
              'url' => route('app-purchase-view')
            ];
          })
        )
        ->merge(
          Expense::latest()->take(3)->get()->map(function($expense) {
            return [
              'type' => 'expense',
              'message' => "Expense recorded - ৳" . number_format($expense->amount, 2),
              'date' => $expense->expense_date,
              'icon' => 'ti ti-receipt',
              'color' => 'warning',
              'url' => route('expenses.index')
            ];
          })
        )
        ->merge(
          (clone $issueQuery)->latest()->take(3)->get()->map(function($issue) {
            return [
              'type' => 'issue',
              'message' => "Issue #{$issue->issue_number} created",
              'date' => $issue->created_at,
              'icon' => 'ti ti-package',
              'color' => 'primary',
              'url' => route('app-issue-view')
            ];
          })
        )
        ->sortByDesc('date')
        ->take(15);
  
      // Calculate growth rates
      $purchaseGrowth = $purchaseStats['last_month_amount'] > 0 
        ? (($purchaseStats['this_month_amount'] - $purchaseStats['last_month_amount']) / $purchaseStats['last_month_amount']) * 100 
        : 0;
  
      $expenseGrowth = $expenseStats['last_month_amount'] > 0 
        ? (($expenseStats['this_month_amount'] - $expenseStats['last_month_amount']) / $expenseStats['last_month_amount']) * 100 
        : 0;
  

        return view('content.dashboard.dashboards-analytics', compact(
            'pageConfigs',

            'expenseAnalytics',

            'employeeStats',

            'busStats',
            'busByType',
            'driverStats',
            'assistantStats',
            'employeeStats',
            'purchaseStats',
            'purchaseMonthlyTrend',
            'purchaseGrowth',
            'issueStats',
            'issueItemsStats',
            'damageStats',
            'expenseStats',
            'expenseMonthlyTrend',
            'expenseByCategory',
            'expenseGrowth',
            'monthlyExpenseTrend',
            'rewardStats',
            'punishmentStats',
            'tripStats', 'tripMonthlyTrend', 'scheduleStats', 
            'itemStats', 'inventoryStats', 'monthlyBillStats',
            'supplierStats', 'financialSummary',
            'recentActivities'
        ));
    }
}
