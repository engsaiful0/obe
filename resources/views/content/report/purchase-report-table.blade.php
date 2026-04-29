<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Purchase Number</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Payment Method</th>
                <th>Items</th>
                <th>Net Total</th>
                <th>Paid Amount</th>
                <th>Due Amount</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($purchases as $purchase)
            <tr>
            <td>{{ $loop->iteration }}</td>
                <td>
                    <a href="{{ route('app-purchase-view-details', $purchase->id) }}" class="text-primary">
                        {{ $purchase->purchase_number }}
                    </a>
                </td>
                <td>{{ $purchase->date ? $purchase->date->format('Y-m-d') : '' }}</td>
                <td>{{ $purchase->supplier ? $purchase->supplier->supplier_name : '' }}</td>
                <td>{{ $purchase->paymentMethod ? $purchase->paymentMethod->payment_method_name : '' }}</td>
                <td>
                    <div class="d-flex flex-column">
                        @foreach($purchase->purchaseItems as $item)
                        <small class="text-muted">
                            {{ $item->item->item_name ?? 'N/A' }} 
                            ({{ $item->quantity }} {{ $item->unit ? $item->unit->unit_name : 'pcs' }})
                        </small>
                        @endforeach
                    </div>
                </td>
                <td class="text-end">{{ number_format($purchase->net_total, 2) }}</td>
                <td class="text-end">{{ number_format($purchase->paid, 2) }}</td>
                <td class="text-end">
                    @if($purchase->due > 0)
                        <span class="text-danger">{{ number_format($purchase->due, 2) }}</span>
                    @else
                        <span class="text-success">0.00</span>
                    @endif
                </td>
                <td>{{ str($purchase->remarks)->limit(30) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No purchases found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Total Amounts:</th>
                <th class="text-end">{{ number_format($totalNetAmount, 2) }}</th>
                <th class="text-end">{{ number_format($totalPaidAmount, 2) }}</th>
                <th class="text-end">{{ number_format($totalDueAmount, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
<div class="card-footer">
    {{ $purchases->links() }}
</div>
