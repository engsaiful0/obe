<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Income;
use App\Models\IncomeHead;
use App\Models\Employee;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncomeExport;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $incomeHeads = IncomeHead::all();
        $employees = Employee::select('id', 'employee_name', 'employee_unique_id')
            ->orderBy('employee_name')
            ->get();
        
        $query = Income::with(['incomeHead', 'employee', 'user']);

        // Apply income head filter
        if ($request->filled('income_head_id')) {
            $query->where('income_head_id', $request->income_head_id);
        }

        // Apply employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('income_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('income_date', '<=', $request->date_to);
    }

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('remarks', 'like', "%{$searchTerm}%")
                    ->orWhere('amount', 'like', "%{$searchTerm}%")
                    ->orWhereHas('incomeHead', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('employee', function ($q) use ($searchTerm) {
                        $q->where('employee_name', 'like', "%{$searchTerm}%")
                            ->orWhere('employee_unique_id', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Get pagination per page
        $perPage = $request->get('per_page', 10);

        // Paginate results
        $incomes = $query->latest('income_date')->paginate($perPage)->withQueryString();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.incomes.partials.table', compact('incomes'))->render(),
                'pagination' => $incomes->appends($request->query())->links()->toHtml(),
                'total' => $incomes->total(),
                'showing' => $incomes->count()
            ]);
        }

        return view('content.incomes.index', compact('incomes', 'incomeHeads', 'employees'));
    }

    public function create()
    {
        $incomeHeads = IncomeHead::all();
        $employees = Employee::select('id', 'employee_name', 'employee_unique_id')
            ->orderBy('employee_name')
            ->get();
        
        return view('content.incomes.add_income', compact('incomeHeads', 'employees'));
    }

    public function show($id)
    {
        $income = Income::with(['incomeHead', 'employee', 'user'])->findOrFail($id);
        return view('content.incomes.view_income', compact('income'));
    }

    public function edit($id)
    {
        $income = Income::with(['incomeHead', 'employee', 'user'])->findOrFail($id);
        $incomeHeads = IncomeHead::all();
        $employees = Employee::select('id', 'employee_name', 'employee_unique_id')
            ->orderBy('employee_name')
            ->get();

        return view('content.incomes.edit_income', compact('income', 'incomeHeads', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'income_head_id' => 'required|exists:income_heads,id',
            'income_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'employee_id' => 'nullable|exists:employees,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        
        // Handle empty employee_id
        if (empty($data['employee_id'])) {
            $data['employee_id'] = null;
        }

        $income = Income::create($data);
        $income->load(['incomeHead', 'employee', 'user']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Income added successfully.',
                'income' => [
                    'id' => $income->id,
                    'income_head_id' => $income->income_head_id,
                    'income_head_name' => $income->incomeHead ? $income->incomeHead->name : '',
                    'amount' => $income->amount,
                    'income_date' => $income->income_date ? $income->income_date->format('Y-m-d') : '',
                    'employee_id' => $income->employee_id,
                    'employee_name' => $income->employee ? ($income->employee->employee_name . ' (' . $income->employee->employee_unique_id . ')') : '',
                    'remarks' => $income->remarks,
                ]
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('app-incomes')->with('success', 'Income added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'income_head_id' => 'required|exists:income_heads,id',
            'income_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'employee_id' => 'nullable|exists:employees,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $income = Income::findOrFail($id);
        $data = $request->all();
        
        // Handle empty employee_id
        if (empty($data['employee_id'])) {
            $data['employee_id'] = null;
        }
        
        $income->update($data);
        $income->load(['incomeHead', 'employee', 'user']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Income updated successfully.',
                'income' => [
                    'id' => $income->id,
                    'income_head_id' => $income->income_head_id,
                    'income_head_name' => $income->incomeHead ? $income->incomeHead->name : '',
                    'amount' => $income->amount,
                    'income_date' => $income->income_date ? $income->income_date->format('Y-m-d') : '',
                    'employee_id' => $income->employee_id,
                    'employee_name' => $income->employee ? ($income->employee->employee_name . ' (' . $income->employee->employee_unique_id . ')') : '',
                    'remarks' => $income->remarks,
                ]
            ]);
        }

        return redirect()->route('app-incomes')->with('success', 'Income updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $income = Income::findOrFail($id);
        $income->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Income deleted successfully.'
            ]);
        }

        return redirect()->route('app-incomes')->with('success', 'Income deleted successfully.');
    }

    public function getIncomeHeads()
    {
        $incomeHeads = IncomeHead::all();
        return response()->json([
            'data' => $incomeHeads,
        ]);
    }

    public function getEmployees()
    {
        $employees = Employee::select('id', 'employee_name', 'employee_unique_id')
            ->orderBy('employee_name')
            ->get();
        return response()->json([
            'data' => $employees,
        ]);
    }

    /**
     * Export incomes to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = Income::with(['incomeHead', 'employee', 'user']);

        // Apply income head filter
        if ($request->filled('income_head_id')) {
            $query->where('income_head_id', $request->income_head_id);
        }

        // Apply employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('income_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('income_date', '<=', $request->date_to);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('remarks', 'like', "%{$searchTerm}%")
                    ->orWhere('amount', 'like', "%{$searchTerm}%")
                    ->orWhereHas('incomeHead', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('employee', function ($q) use ($searchTerm) {
                        $q->where('employee_name', 'like', "%{$searchTerm}%")
                            ->orWhere('employee_unique_id', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $incomes = $query->latest('income_date')->get();

        return Excel::download(new IncomeExport($incomes), 'incomes_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Export incomes to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Income::with(['incomeHead', 'employee', 'user']);

        // Apply income head filter
        if ($request->filled('income_head_id')) {
            $query->where('income_head_id', $request->income_head_id);
        }

        // Apply employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('income_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('income_date', '<=', $request->date_to);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('remarks', 'like', "%{$searchTerm}%")
                    ->orWhere('amount', 'like', "%{$searchTerm}%")
                    ->orWhereHas('incomeHead', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('employee', function ($q) use ($searchTerm) {
                        $q->where('employee_name', 'like', "%{$searchTerm}%")
                            ->orWhere('employee_unique_id', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $incomes = $query->latest('income_date')->get();

        $pdf = Pdf::loadView('content.incomes.export-pdf', compact('incomes'));
        return $pdf->download('incomes_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
