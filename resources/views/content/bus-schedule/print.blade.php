<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bus Schedules - Print View</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #ffc107;
            font-weight: bold;
        }
        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
        }
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .no-print {
            display: none;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            Print This Page
        </button>
        <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>Bus Schedules Report</h1>
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
        <p>Total Records: {{ $schedules->count() }}</p>
    </div>

    @if($schedules->count() > 0)
        <div class="summary">
            <strong>Summary:</strong> This report contains {{ $schedules->count() }} bus schedule(s) with details of routes, vehicles, drivers, and assistants.
        </div>

        <table>
            <thead>
                <tr>
                    <th>Start Stoppage</th>
                    <th>End Stoppage</th>
                    <th>Route</th>
                    <th>Start Time</th>
                        <th>Vehicle</th>
                        <th>Vehicle Type</th>
                        <th>Vehicle Sub Type</th>
                        <th>Driver</th>
                    <th>Assistant</th>
                    <th>Vehicle User</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $schedule)
                    <tr>
                        <td>{{ $schedule->startStoppage->stoppage_name ?? 'N/A' }}</td>
                        <td>{{ $schedule->endStoppage->stoppage_name ?? 'N/A' }}</td>
                        <td>{{ $schedule->vehicleRoute->route_name ?? 'N/A' }}</td>
                        <td>
                            @if($schedule->tripTime)
                                {{ $schedule->tripTime->time_name }} - {{ \Carbon\Carbon::parse($schedule->tripTime->time_value)->format('H:i') }} {{ $schedule->tripTime->time_period }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($schedule->vehicle)
                                {{ $schedule->vehicle->model_name }}<br>
                                <small>{{ $schedule->vehicle->registration_number }}</small>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $schedule->vehicleType->vehicle_type_name ?? 'N/A' }}</td>
                        <td>{{ $schedule->vehicleSubType->sub_type_name ?? 'N/A' }}</td>
                        <td>{{ $schedule->driver->full_name ?? 'N/A' }}</td>
                        <td>{{ $schedule->assistant->assistant_name ?? 'N/A' }}</td>
                        <td>{{ $schedule->vehicleUser->vehicle_user_name ?? 'N/A' }}</td>
                        <td class="status-{{ $schedule->status }}">
                            {{ ucfirst($schedule->status) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="summary">
            <strong>No Data:</strong> No bus schedules found matching the selected criteria.
        </div>
    @endif
</body>
</html>
