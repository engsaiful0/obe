<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Buses List Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .filters p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .summary {
            background-color: #e9ecef;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>All Buses List Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>Total Records: {{ $dailyBusLists->count() }}</p>
    </div>

    @if(!empty($filters) && array_filter($filters))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(isset($filters['date']) && $filters['date'])
            <p><strong>Date:</strong> {{ $filters['date'] }}</p>
        @endif
        @if(isset($filters['date_from']) && $filters['date_from'])
            <p><strong>Date From:</strong> {{ $filters['date_from'] }}</p>
        @endif
        @if(isset($filters['date_to']) && $filters['date_to'])
            <p><strong>Date To:</strong> {{ $filters['date_to'] }}</p>
        @endif
        @if(isset($filters['vehicle_sub_type_id']) && $filters['vehicle_sub_type_id'])
            <p><strong>Vehicle Sub Type:</strong> {{ $filters['vehicle_sub_type_id'] }}</p>
        @endif
        @if(isset($filters['bus_type']) && $filters['bus_type'])
            <p><strong>Bus Type:</strong> {{ ucfirst($filters['bus_type']) }}</p>
        @endif
        @if(isset($filters['search']) && $filters['search'])
            <p><strong>Search:</strong> {{ $filters['search'] }}</p>
        @endif
    </div>
    @endif

    <div class="summary">
        <strong>Report Summary:</strong> 
        Showing {{ $dailyBusLists->count() }} bus list entries
        @if(isset($filters['date_from']) && isset($filters['date_to']))
            from {{ $filters['date_from'] }} to {{ $filters['date_to'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Vehicle</th>
                <th>Registration</th>
                <th>Sub Type</th>
                <th>Start Stoppage</th>
                <th>End Stoppage</th>
                <th>Time</th>
                <th>Driver</th>
                <th>Assistant</th>
                
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dailyBusLists as $busList)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $busList->list_date }}</td>
                <td>{{ $busList->vehicle->model_name ?? 'N/A' }}</td>
                <td>{{ $busList->vehicle->registration_number ?? 'N/A' }}</td>
                <td>{{ $busList->vehicleSubType->sub_type_name ?? 'N/A' }}</td>
                <td>{{ $busList->startStoppage->stoppage_name ?? 'N/A' }}</td>
                <td>{{ $busList->endStoppage->stoppage_name ?? 'N/A' }}</td>
                <td>{{ $busList->start_time }}</td>
                <td>{{ $busList->driver->full_name ?? 'N/A' }}</td>
                <td>{{ $busList->assistant->assistant_name ?? 'N/A' }}</td>
                
                <td>{{ $busList->remarks ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align: center; padding: 20px;">
                    No bus list entries found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the TMS System.</p>
        <p>© {{ date('Y') }} All rights reserved.</p>
    </div>
</body>
</html>