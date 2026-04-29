<!DOCTYPE html>
<html>
<head>
    <title>Reward Report</title>
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
    </style>
</head>
<body>
    <div class="header">
        @if($appSetting && $appSetting->logo)
            <img src="{{ public_path('profile_pictures/' . $appSetting->logo) }}" alt="logo">
        @endif
        <h1>{{ $appSetting->name ?? 'Reward Report' }}</h1>
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
        @if(request('reward_type_id'))
            <p><strong>Reward Type:</strong>
                {{ \App\Models\RewardType::find(request('reward_type_id'))->name ?? 'N/A' }}
            </p>
        @endif
        @if(request('bus_sub_type_id'))
            <p><strong>Bus Sub Type:</strong>
                {{ \App\Models\BusSubType::find(request('bus_sub_type_id'))->sub_type_name ?? 'N/A' }}
            </p>
        @endif
        @if(request('bus_id'))
            <p><strong>Bus:</strong>
                {{ \App\Models\Bus::find(request('bus_id'))->bus_number ?? 'N/A' }}
            </p>
        @endif
        @if(request('driver_id'))
            <p><strong>Driver:</strong>
                {{ \App\Models\Driver::find(request('driver_id'))->full_name ?? 'N/A' }}
            </p>
        @endif
        @if(request('assistant_id'))
            <p><strong>Assistant:</strong>
                {{ \App\Models\Assistant::find(request('assistant_id'))->full_name ?? 'N/A' }}
            </p>
        @endif
    </div>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Date</th>
                <th>Bus Sub Type</th>
                <th>Bus</th>
                <th>Driver</th>
                <th>Bus Helper</th>
                <th>Reward Type</th>
                <th>Reason</th>
                <th>Amount</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rewards as $reward)
            <tr>
                <td>{{ $reward->reward_date ? $reward->reward_date->format('Y-m-d') : '' }}</td>
                <td>{{ $reward->bus && $reward->bus->busSubType ? $reward->bus->busSubType->sub_type_name : '' }}</td>
                <td>{{ $reward->bus ? $reward->bus->bus_number : '' }}</td>
                <td>{{ $reward->driver ? $reward->driver->full_name : '' }}</td>
                <td>{{ $reward->bus_helper ? $reward->bus_helper->bus_helper_name : '' }}</td>
                <td>{{ $reward->rewardType ? $reward->rewardType->name : '' }}</td>
                <td>{{ $reward->reason }}</td>
                <td class="text-end">{{ number_format($reward->reward_amount, 2) }}</td>
                <td>{{ $reward->remarks }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No rewards found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="total text-end">Total Amount:</td>
                <td class="total text-end">{{ number_format($totalAmount, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
