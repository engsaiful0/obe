<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Issue Number</th>
                <th>Date</th>
                <th>Employee</th>
                <th>Items</th>
                <th>Total Quantity</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($issues as $issue)
            <tr>
                <td>
                    <a href="{{ route('app-issue-view-details', $issue->id) }}" class="text-primary">
                        {{ $issue->issue_number }}
                    </a>
                </td>
                <td>{{ $issue->date ? $issue->date->format('Y-m-d') : '' }}</td>
                <td>{{ $issue->employee ? $issue->employee->employee_name : '' }}</td>
                <td>
                    <div class="d-flex flex-column">
                        @foreach($issue->issueItems as $item)
                        <small class="text-muted">
                            {{ $item->item->item_name ?? 'N/A' }} 
                            ({{ $item->quantity }} {{ $item->unit ? $item->unit->unit_name : 'pcs' }})
                        </small>
                        @endforeach
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-primary">
                        {{ $issue->issueItems->sum('quantity') }}
                    </span>
                </td>
                <td>{{ str($issue->remarks)->limit(30) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No issues found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total Items Issued:</th>
                <th class="text-center">{{ $totalItemsIssued }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
@if(isset($issues) && is_object($issues) && method_exists($issues, 'links'))
<div class="card-footer">
    {{ $issues->links() }}
</div>
@endif
