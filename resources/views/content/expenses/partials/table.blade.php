@if($expenses->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Memo No</th>
                    <th>Bill No</th>
                    <th>Expense Head</th>
                    <th>Supplier</th>
                    <th>Bus Sub Type</th>
                    <th>Bus Number</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Concerned<br> Employee</th>
                    <th>Remarks</th>
                    @if(auth()->user()->hasPermissionTo('expense-view') || auth()->user()->hasPermissionTo('expense-edit') || auth()->user()->hasPermissionTo('expense-delete'))
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td>{{ $loop->iteration + ($expenses->currentPage() - 1) * $expenses->perPage() }}</td>
                    <td>{{ $expense->memo_no ?? 'N/A' }}</td>
                    <td>{{ $expense->bill_no ?? 'N/A' }}</td>
                    <td>{{ $expense->expenseHead->name ?? 'N/A' }}</td>
                    <td>{{ $expense->supplier->supplier_name ?? 'N/A' }}</td>
                    <td>{{ $expense->busSubType->sub_type_name ?? 'N/A' }}</td>
                    <td>{{ $expense->bus->bus_number ?? 'N/A' }}</td>
                    <td>{{ $expense->expense_date }}</td>
                    <td>৳{{ number_format($expense->amount, 2) }}</td>
                    
                   
                    <td>{{ $expense->employee->employee_name ?? 'N/A' }}</td>
                    
                    <td>{{ str($expense->remarks)->limit(30) }}</td>
                    @if(auth()->user()->hasPermissionTo('expense-view') || auth()->user()->hasPermissionTo('expense-edit') || auth()->user()->hasPermissionTo('expense-delete'))
                    <td>
                        <div class="dropdown">
                            @if(auth()->user()->hasPermissionTo('expense-view'))
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            @endif
                            <div class="dropdown-menu">
                                @if(auth()->user()->hasPermissionTo('expense-edit'))
                                <a class="dropdown-item" href="{{ route('expenses.edit', $expense->id) }}">
                                    <i class="ti ti-edit me-1"></i> Edit
                                </a>
                                @endif
                                @if(auth()->user()->hasPermissionTo('expense-delete'))
                                <a class="dropdown-item text-danger" href="#" onclick="deleteExpense({{ $expense->id }})">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </a>
                                @endif
                            </div>
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            Showing {{ $expenses->firstItem() }} to {{ $expenses->lastItem() }} of {{ $expenses->total() }} results
        </div>
        <div>
            {{ $expenses->links() }}
        </div>
    </div>
@else
    <div class="text-center py-4">
        <i class="ti ti-receipt-off text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-2">No expenses found</h5>
        <p class="text-muted">Try adjusting your filters or add a new expense.</p>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add New Expense
        </a>
    </div>
@endif
