<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseHead;
use Illuminate\Http\Response;
use App\Models\BusSubType;
use App\Models\Bus;
use App\Models\Employee;
use App\Models\Supplier;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExpensesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $busSubTypes = BusSubType::all();
        $buses = Bus::with('busSubType')->get();
        $employees = Employee::all();
        $expenseHeads = ExpenseHead::all();
        $suppliers = Supplier::all();
        $query = Expense::with(['expenseHead', 'busSubType', 'bus', 'employee']);

        // Apply expense head filter
        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Apply bus sub type filter
        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        // Apply bus filter
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

      

        // Apply employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }


        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('remarks', 'like', "%{$searchTerm}%")
                    ->orWhere('amount', 'like', "%{$searchTerm}%")
                    ->orWhereHas('expenseHead', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('busSubType', function ($q) use ($searchTerm) {
                        $q->where('sub_type_name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('bus', function ($q) use ($searchTerm) {
                        $q->where('bus_number', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('employee', function ($q) use ($searchTerm) {
                        $q->where('employee_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Get pagination per page
        $perPage = $request->get('per_page', 10);

        // Paginate results
        $expenses = $query->latest('expense_date')->paginate($perPage)->withQueryString();

        // Get filter options with caching
        $expenseHeads = cache()->remember('expense_heads', 3600, function () {
            return ExpenseHead::all();
        });

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.expenses.partials.table', compact('expenses'))->render(),
                'pagination' => $expenses->appends($request->query())->links()->toHtml(),
                'total' => $expenses->total(),
                'showing' => $expenses->count()
            ]);
        }

        return view('content.expenses.index', compact('expenses', 'expenseHeads', 'busSubTypes', 'buses', 'employees', 'suppliers'));
    }

    public function getExpenses(Request $request)
    {
        $expenses = Expense::with('expenseHead')->get();
        return response()->json([
            'data' => $expenses,
        ]);
    }

    public function create()
    {
        $busSubTypes = BusSubType::all();
        $buses = Bus::with('busType', 'busSubType')->get();
        $employees = Employee::all();
        $suppliers = Supplier::all();
        $expenseHeads = ExpenseHead::all();
        return view('content.expenses.add-expense', compact('busSubTypes', 'buses', 'employees', 'expenseHeads', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_head_id' => 'required|exists:expense_heads,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        $expense = Expense::create($data);

        // ✅ Return JSON response for AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense added successfully!',
                'data' => $expense,
                'redirect_url' => route('expenses.index'),
            ], Response::HTTP_CREATED);
        }

        // ✅ Return normal redirect for non-AJAX
        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense added successfully!');
    }

    public function edit($id)
    {
        $busSubTypes = BusSubType::all();
        $buses = Bus::with('busSubType')->get();
        $employees = Employee::all();
        $suppliers = Supplier::all();
        $expenseHeads = ExpenseHead::all();
        $expense = Expense::findOrFail($id);
        return view('content.expenses.edit-expense', compact('expense', 'busSubTypes', 'buses', 'employees','expenseHeads', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'expense_head_id' => 'required|exists:expense_heads,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
      
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully!',
                'redirect_url' => route('expenses.index')
            ]);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully!');
    }


    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        if (request()->expectsJson()) {
            return response()->json(null, Response::HTTP_NO_CONTENT);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully!');
    }

    /**
     * Export expenses to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = Expense::with('expenseHead');

        // Apply expense head filter
        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('remarks', 'like', "%{$searchTerm}%")
                    ->orWhere('amount', 'like', "%{$searchTerm}%")
                    ->orWhereHas('expenseHead', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $expenses = $query->latest('expense_date')->get();

        return Excel::download(new ExpensesExport($expenses), 'expenses_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Export expenses to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Expense::with(['expenseHead', 'busSubType', 'bus', 'employee']);

        // Apply expense head filter
        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Apply bus sub type filter
        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        // Apply bus filter
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

      

        // Apply employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }


        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('remarks', 'like', "%{$searchTerm}%")
                    ->orWhere('amount', 'like', "%{$searchTerm}%")
                    ->orWhereHas('expenseHead', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('busSubType', function ($q) use ($searchTerm) {
                        $q->where('sub_type_name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('bus', function ($q) use ($searchTerm) {
                        $q->where('bus_number', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('employee', function ($q) use ($searchTerm) {
                        $q->where('employee_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $expenses = $query->latest('expense_date')->get();

        $pdf = Pdf::loadView('content.expenses.export-pdf', compact('expenses'));
        return $pdf->download('expenses_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
