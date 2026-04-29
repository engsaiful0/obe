<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Opening Stock</th>
                <th>Total Purchased</th>
                <th>Total Issued</th>
                <th>Current Stock</th>
                <th>Total Purchased Amount</th>
                <th>Last Purchase Date</th>
                <th>Last Issue Date</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($items as $stockItem)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <strong>{{ $stockItem['item']->item_name }}</strong>
                </td>
                <td class="text-end">
                    <span class="badge bg-info">{{ number_format($stockItem['opening_stock'], 2) }}</span>
                </td>
                <td class="text-end">
                    <span class="badge bg-success">{{ number_format($stockItem['total_purchased'], 2) }}</span>
                </td>
                <td class="text-end">
                    <span class="badge bg-warning">{{ number_format($stockItem['total_issued'], 2) }}</span>
                </td>
                <td class="text-end">
                    @if($stockItem['current_stock'] > 0)
                        <span class="badge bg-primary">{{ number_format($stockItem['current_stock'], 2) }}</span>
                    @elseif($stockItem['current_stock'] == 0)
                        <span class="badge bg-secondary">{{ number_format($stockItem['current_stock'], 2) }}</span>
                    @else
                        <span class="badge bg-danger">{{ number_format($stockItem['current_stock'], 2) }}</span>
                    @endif
                </td>
                <td class="text-end">
                    <strong>{{ number_format($stockItem['total_purchased_amount'], 2) }}</strong>
                </td>
                <td class="text-center">
                    @if($stockItem['last_purchase_date'])
                        <small class="text-muted">{{ $stockItem['last_purchase_date']->format('Y-m-d') }}</small>
                    @else
                        <small class="text-muted">Never</small>
                    @endif
                </td>
                <td class="text-center">
                    @if($stockItem['last_issue_date'])
                        <small class="text-muted">{{ $stockItem['last_issue_date']->format('Y-m-d') }}</small>
                    @else
                        <small class="text-muted">Never</small>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No stock data found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th class="text-end">Total Items:</th>
                <th class="text-end">{{ $items->count() }}</th>
                <th class="text-end">{{ number_format($items->sum('total_purchased'), 2) }}</th>
                <th class="text-end">{{ number_format($items->sum('total_issued'), 2) }}</th>
                <th class="text-end">{{ number_format($items->sum('current_stock'), 2) }}</th>
                <th class="text-end">{{ number_format($items->sum('total_purchased_amount'), 2) }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
</div>
@if(isset($pagination) && is_object($pagination) && method_exists($pagination, 'links'))
<div class="card-footer">
    {{ $pagination->links() }}
</div>
@endif
