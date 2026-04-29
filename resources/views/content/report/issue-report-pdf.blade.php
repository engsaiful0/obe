<!DOCTYPE html>
<html>
<head>
    <title>Issue Report</title>
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
        .quantity-badge {
            background-color: #007bff;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($appSetting && $appSetting->logo)
            <img src="{{ public_path('profile_pictures/' . $appSetting->logo) }}" alt="logo">
        @endif
        <h1>{{ $appSetting->name ?? 'Issue Report' }}</h1>
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
        @if(request('employee_id'))
            <p><strong>Employee:</strong>
                {{ \App\Models\Employee::find(request('employee_id'))->employee_name ?? 'N/A' }}
            </p>
        @endif
        @if(request('item_id'))
            <p><strong>Item:</strong>
                {{ \App\Models\Item::find(request('item_id'))->item_name ?? 'N/A' }}
            </p>
        @endif
        @if(request('issue_number'))
            <p><strong>Issue Number:</strong>
                {{ request('issue_number') }}
            </p>
        @endif
    </div>

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
        <tbody>
            @forelse ($issues as $issue)
            <tr>
                <td>{{ $issue->issue_number }}</td>
                <td>{{ $issue->date ? $issue->date->format('Y-m-d') : '' }}</td>
                <td>{{ $issue->employee ? $issue->employee->employee_name : '' }}</td>
                <td class="items-list">
                    @foreach($issue->issueItems as $item)
                        {{ $item->item->item_name ?? 'N/A' }} 
                        ({{ $item->quantity }} {{ $item->unit ? $item->unit->unit_name : 'pcs' }})<br>
                    @endforeach
                </td>
                <td class="text-center">
                    <span class="quantity-badge">{{ $issue->issueItems->sum('quantity') }}</span>
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
                <td colspan="4" class="total text-end">Total Items Issued:</td>
                <td class="total text-center">{{ $totalItemsIssued }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
