<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Income Head</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Concerned Employee</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomes as $income)
            <tr>
                <td>{{ $income->id }}</td>
                <td>{{ $income->incomeHead->name ?? 'N/A' }}</td>
                <td>৳{{ number_format($income->amount, 2) }}</td>
                <td>{{ $income->income_date ? \Carbon\Carbon::parse($income->income_date)->format('M d, Y') : 'N/A' }}</td>
                <td>
                    @if($income->employee)
                        {{ $income->employee->employee_name }} ({{ $income->employee->employee_unique_id }})
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($income->remarks)
                        <span title="{{ $income->remarks }}">
                            {{ \Illuminate\Support\Str::limit($income->remarks, 50) }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    <div class="d-inline-block">
                        <a href="{{ route('app-incomes.show', $income->id) }}" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" title="View">
                            <i class="ti ti-eye ti-md"></i>
                        </a>
                        <a href="{{ route('app-incomes.edit', $income->id) }}" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" title="Edit">
                            <i class="ti ti-pencil ti-md"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon delete-record" data-id="{{ $income->id }}" title="Delete">
                            <i class="ti ti-trash ti-md"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">
                    <p class="text-muted py-4">No incomes found.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($incomes->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <p class="text-muted mb-0">
                Showing {{ $incomes->firstItem() ?? 0 }} to {{ $incomes->lastItem() ?? 0 }} of {{ $incomes->total() }} results
            </p>
        </div>
        <div>
            {{ $incomes->links() }}
        </div>
    </div>
@endif

