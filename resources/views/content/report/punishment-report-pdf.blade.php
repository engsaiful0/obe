<!DOCTYPE html>
<html>
<head>
    <title>Punishment Report</title>
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
        .status-active { color: #ffc107; font-weight: bold; }
        .status-completed { color: #28a745; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if($appSetting && $appSetting->logo)
            <img src="{{ public_path('profile_pictures/' . $appSetting->logo) }}" alt="logo">
        @endif
        <h1>{{ $appSetting->name ?? 'Punishment Report' }}</h1>
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
        @if(request('punishment_type_id'))
            <p><strong>Punishment Type:</strong>
                {{ \App\Models\PunishmentType::find(request('punishment_type_id'))->name ?? 'N/A' }}
            </p>
        @endif
        @if(request('violation_type_id'))
            <p><strong>Violation Type:</strong>
                {{ \App\Models\ViolationType::find(request('violation_type_id'))->name ?? 'N/A' }}
            </p>
        @endif
        @if(request('vehicle_sub_type_id'))
            <p><strong>Vehicle Sub Type:</strong>
                {{ \App\Models\VehicleSubType::find(request('vehicle_sub_type_id'))->sub_type_name ?? 'N/A' }}
            </p>
        @endif
        @if(request('vehicle_id'))
            <p><strong>Vehicle:</strong>
                {{ \App\Models\Vehicle::find(request('vehicle_id'))->display_name ?? 'N/A' }}
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
        @if(request('status'))
            <p><strong>Status:</strong>
                {{ ucfirst(request('status')) }}
            </p>
        @endif
    </div>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Date</th>
                <th>Vehicle Sub Type</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Bus Helper</th>
                <th>Punishment Type</th>
                <th>Violation Type</th>
                <th>Description</th>
                <th>Fine Amount</th>
                <th>Suspension Days</th>
                <th>Status</th>
                <th>Witness</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($punishments as $punishment)
            <tr>
                <td>{{ $punishment->punishment_date ? $punishment->punishment_date->format('Y-m-d') : '' }}</td>
                <td>{{ $punishment->bus && $punishment->bus->busSubType ? $punishment->bus->busSubType->sub_type_name : '' }}</td>
                <td>{{ $punishment->bus ? $punishment->bus->bus_number : '' }}</td>
                <td>{{ $punishment->driver ? $punishment->driver->full_name : '' }}</td>
                <td>{{ $punishment->bus_helper ? $punishment->bus_helper->bus_helper_name : '' }}</td>
                <td>{{ $punishment->punishmentType ? $punishment->punishmentType->name : '' }}</td>
                <td>{{ $punishment->violationType ? $punishment->violationType->name : '' }}</td>
                <td>{{ str($punishment->description)->limit(50) }}</td>
                <td class="text-end">{{ $punishment->fine_amount ? number_format($punishment->fine_amount, 2) : '-' }}</td>
                <td class="text-center">{{ $punishment->suspension_days ?: '-' }}</td>
                <td class="status-{{ $punishment->status }}">{{ ucfirst($punishment->status) }}</td>
                <td>{{ $punishment->witnessEmployee ? $punishment->witnessEmployee->employee_name : '' }}</td>
                <td>{{ str($punishment->remarks)->limit(30) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center">No punishments found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="total text-end">Total Fine Amount:</td>
                <td class="total text-end">{{ number_format($totalFineAmount, 2) }}</td>
                <td class="total text-center">{{ $totalSuspensionDays ?: '-' }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
