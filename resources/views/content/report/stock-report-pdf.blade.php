<!DOCTYPE html>
<html>
<head>
    <title>Stock Report</title>
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
        .stock-positive { color: #28a745; font-weight: bold; }
        .stock-zero { color: #6c757d; font-weight: bold; }
        .stock-negative { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if($appSetting && $appSetting->logo)
            <img src="{{ public_path('profile_pictures/' . $appSetting->logo) }}" alt="logo">
        @endif
        <h1>{{ $appSetting->name ?? 'Stock Report' }}</h1>
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
        @if(request('item_id'))
            <p><strong>Item:</strong>
                {{ \App\Models\Item::find(request('item_id'))->item_name ?? 'N/A' }}
            </p>
        @endif
    </div>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
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
        <tbody>
            @forelse ($items as $stockItem)
            <tr>
                <td><strong>{{ $stockItem['item']->item_name }}</strong></td>
                <td class="text-end">{{ number_format($stockItem['opening_stock'], 2) }}</td>
                <td class="text-end">{{ number_format($stockItem['total_purchased'], 2) }}</td>
                <td class="text-end">{{ number_format($stockItem['total_issued'], 2) }}</td>
                <td class="text-end 
                    @if($stockItem['current_stock'] > 0) stock-positive
                    @elseif($stockItem['current_stock'] == 0) stock-zero
                    @else stock-negative
                    @endif">
                    {{ number_format($stockItem['current_stock'], 2) }}
                </td>
                <td class="text-end">{{ number_format($stockItem['total_purchased_amount'], 2) }}</td>
                <td class="text-center">
                    @if($stockItem['last_purchase_date'])
                        {{ $stockItem['last_purchase_date']->format('Y-m-d') }}
                    @else
                        Never
                    @endif
                </td>
                <td class="text-center">
                    @if($stockItem['last_issue_date'])
                        {{ $stockItem['last_issue_date']->format('Y-m-d') }}
                    @else
                        Never
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
                <td class="total text-end">Total Items:</td>
                <td class="total text-end">{{ $items->count() }}</td>
                <td class="total text-end">{{ number_format($items->sum('total_purchased'), 2) }}</td>
                <td class="total text-end">{{ number_format($items->sum('total_issued'), 2) }}</td>
                <td class="total text-end">{{ number_format($items->sum('current_stock'), 2) }}</td>
                <td class="total text-end">{{ number_format($items->sum('total_purchased_amount'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
