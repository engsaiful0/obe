<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Issue as IssueModel;
use App\Models\IssueUniqueId as IssueUniqueIdModel;
use App\Models\IssueItem as IssueItemModel;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IssueController extends Controller
{
    public function addIssue()
    {
        $employees = Employee::all();
        $items = Item::all();
        $units=Unit::all();
        
        // Get the last serial
        $latest = IssueUniqueIdModel::latest('serial')->first();
        $serial = $latest ? $latest->serial + 1 : 1;

        // Format as I-0001, I-0002, ...
        $issue_unique_id = 'I-' . str_pad($serial, 4, '0', STR_PAD_LEFT);
        
        return view('content.issue.add', compact('employees', 'items','units', 'issue_unique_id', 'serial'));
    }

    public function productRow(Request $request)
    {
        $items = Item::all();
        $units = Unit::all();
        $rowIndex = $request->get('row_index', 0);
        return view('content.issue.product_row', compact('items', 'units', 'rowIndex'));
    }

    public function viewIssue(Request $request)
    {
        $employees = Employee::where('user_id', Auth::id())->get();
        $items = Item::where('user_id', Auth::id())->get();
        $units = Unit::all();
        
        $query = IssueModel::with(['employee', 'issueItems.item', 'issueItems.unit'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('issue_number') && $request->issue_number) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('issue_number', 'like', '%' . $search . '%')
                  ->orWhere('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function($empQuery) use ($search) {
                      $empQuery->where('employee_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $issues = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('content.issue.index', compact('employees', 'items', 'units', 'issues'));
    }

    public function viewDetails($id)
    {
        $issue = IssueModel::with(['employee', 'issueItems.item', 'issueItems.unit', 'user'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('content.issue.details', compact('issue'));
    }

    public function edit($id)
    {
        $issue = IssueModel::with(['employee', 'issueItems.item', 'issueItems.unit'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $employees = Employee::all();
        $items = Item::all();
        $units=Unit::all();

        return view('content.issue.edit', compact('issue', 'employees', 'items','units'));
    }

    public function printIssueList(Request $request)
    {
        $query = IssueModel::with(['employee', 'issueItems.item', 'issueItems.unit'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('issue_number') && $request->issue_number) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('issue_number', 'like', '%' . $search . '%')
                  ->orWhere('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function($empQuery) use ($search) {
                      $empQuery->where('employee_name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Get all issues (no pagination for print)
        $issues = $query->orderBy('created_at', 'desc')->get();

        return view('content.issue.print_list', compact('issues'));
    }

    public function exportPdf(Request $request)
    {
        $query = IssueModel::with(['employee', 'issueItems.item', 'issueItems.unit'])
            ->where('user_id', Auth::id());

        // Apply same filters as view
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('issue_number') && $request->issue_number) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('issue_number', 'like', '%' . $search . '%')
                  ->orWhere('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function($empQuery) use ($search) {
                      $empQuery->where('employee_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $issues = $query->orderBy('created_at', 'desc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('content.issue.pdf', compact('issues'));
        return $pdf->download('issues-' . date('Y-m-d') . '.pdf');
    }

    public function printIssue($id)
    {
        $issue = IssueModel::with(['employee', 'issueItems.item', 'issueItems.unit', 'user'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('content.issue.print', compact('issue'));
    }

    public function getIssue(Request $request)
    {
        $query = IssueModel::with(['employee', 'issueItems.item', 'issueItems.unit'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('issue_number') && $request->issue_number) {
            $query->where('issue_number', 'like', '%' . $request->issue_number . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('issue_number', 'like', '%' . $search . '%')
                  ->orWhere('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function($empQuery) use ($search) {
                      $empQuery->where('employee_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $issues = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($issues);
    }

    public function getEmployees()
    {
        $employees = Employee::where('user_id', Auth::id())->get();
        return response()->json($employees);
    }

    public function getItems()
    {
        $items = Item::where('user_id', Auth::id())->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'issue_number' => 'required|string|max:255',
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $userId = $user->id;
            
            $issue = IssueModel::create([
                'issue_number' => $request->issue_number,
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'remarks' => $request->remarks,
                'user_id' => $userId,
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            // Create issue items
            foreach ($request->items as $item) {
                IssueItemModel::create([
                    'issue_id' => $issue->id,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'user_id' => $userId,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }

            IssueUniqueIdModel::create([
                'serial' => $request->serial,
                'issue_number' => $issue->issue_number,
                'issue_id' => $issue->id,
                'user_id' => $userId,
                'created_at' => now(),
            ]);
            
            DB::commit();
            return response()->json(['message' => 'Issue created successfully.', 'data' => $issue], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error creating issue: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $issue = IssueModel::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $request->validate([
            'issue_number' => 'required|string|max:255',
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $issue->update([
                'issue_number' => $request->issue_number,
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'remarks' => $request->remarks,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

            // Delete existing issue items
            IssueItemModel::where('issue_id', $issue->id)->delete();

            // Create new issue items
            foreach ($request->items as $item) {
                IssueItemModel::create([
                    'issue_id' => $issue->id,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'user_id' => $userId,
                    'created_by' => $userId,
                    'created_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Issue updated successfully.', 'data' => $issue], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error updating issue: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $issue = IssueModel::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Delete issue items first
            IssueItemModel::where('issue_id', $issue->id)->delete();
            
            // Delete issue unique id
            IssueUniqueIdModel::where('issue_id', $issue->id)->delete();
            
            // Delete issue
            $issue->delete();

            DB::commit();
            return response()->json(['message' => 'Issue deleted successfully.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error deleting issue: ' . $e->getMessage()], 500);
        }
    }
}