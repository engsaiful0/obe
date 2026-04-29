<!DOCTYPE html>
<html>
<head>
    <title>Purchase Report</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 12px;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left;
        }
        .table th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .header { 
            margin-bottom: 20px; 
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header img { width: 100px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0 0 0; color: #666; }
        .total { font-weight: bold; }
        .filter-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .filter-info p {
            margin: 5px 0;
        }
        .items-list {
            font-size: 10px;
            line-height: 1.2;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($appSetting && $appSetting->logo)
            <img src="{{ public_path('profile_pictures/' . $appSetting->logo) }}" alt="logo">
        @endif
        <h1>{{ $appSetting->name ?? 'Purchase Report' }}</h1>
        <p>{{ $appSetting->address ?? '' }}</p>
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
    </div>

    <div class="filter-info">
        <h4>Report Filters</h4>
        <p><strong>Date Range:</strong>
            @if(request('from_date') && request('to_date'))
                {{ request('from_date') }} to {{ request('to_date') }}
            @else
                All
            @endif
        </p>
        @if(request('supplier_id'))
            <p><strong>Supplier:</strong>
                {{ \App\Models\Supplier::find(request('supplier_id'))->supplier_name ?? 'N/A' }}
            </p>
        @endif
        @if(request('item_id'))
            <p><strong>Item:</strong>
                {{ \App\Models\Item::find(request('item_id'))->item_name ?? 'N/A' }}
            </p>
        @endif
        @if(request('payment_method_id'))
            <p><strong>Payment Method:</strong>
                {{ \App\Models\PaymentMethod::find(request('payment_method_id'))->payment_method_name ?? 'N/A' }}
            </p>
        @endif
        @if(request('purchase_number'))
            <p><strong>Purchase Number:</strong>
                {{ request('purchase_number') }}
            </p>
        @endif
    </div>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
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
        <tbody>
            @forelse ($purchases as $purchase)
            <tr>
                <td>{{ $purchase->purchase_number }}</td>
                <td>{{ $purchase->date ? $purchase->date->format('Y-m-d') : '' }}</td>
                <td>{{ $purchase->supplier ? $purchase->supplier->supplier_name : '' }}</td>
                <td>{{ $purchase->paymentMethod ? $purchase->paymentMethod->payment_method_name : '' }}</td>
                <td class="items-list">
                    @foreach($purchase->purchaseItems as $item)
                        {{ $item->item->item_name ?? 'N/A' }} 
                        ({{ $item->quantity }} {{ $item->unit ? $item->unit->unit_name : 'pcs' }})<br>
                    @endforeach
                </td>
                <td class="text-end">{{ number_format($purchase->net_total, 2) }}</td>
                <td class="text-end">{{ number_format($purchase->paid, 2) }}</td>
                <td class="text-end">{{ number_format($purchase->due, 2) }}</td>
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
                <td colspan="5" class="total text-end">Total Amounts:</td>
                <td class="total text-end">{{ number_format($totalNetAmount, 2) }}</td>
                <td class="total text-end">{{ number_format($totalPaidAmount, 2) }}</td>
                <td class="total text-end">{{ number_format($totalDueAmount, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
