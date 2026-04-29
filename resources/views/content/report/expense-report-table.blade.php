<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Memo No</th>
                <th>Bill No</th>
                <th>Expense Head</th>
                <th>Supplier</th>
                <th>Bus Number</th>
                <th>Date</th>
                
                <th>Amount</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($expenses as $expense)
            <tr>
                <td>{{ $loop->iteration + ($expenses->currentPage() - 1) * $expenses->perPage() }}</td>
                <td>{{ $expense->memo_no ?? 'N/A' }}</td>
                <td>{{ $expense->bill_no ?? 'N/A' }}</td>
                <td>{{ $expense->expenseHead->name ?? '' }}</td>
                <td>{{ $expense->supplier->supplier_name ?? 'N/A' }}</td>
                
                <td>{{ $expense->bus ? $expense->bus->bus_number : '' }}</td>
                <td>{{ $expense->expense_date }}</td>
                <td>{{ number_format($expense->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No expenses found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-end">Total Amount:</th>
                <th>{{ number_format($totalAmount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>
<div class="card-footer">
    {{ $expenses->links() }}
</div>
