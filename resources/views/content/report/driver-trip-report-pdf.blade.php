<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Driver Trip Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
        }
        .header p {
            margin: 3px 0;
            font-size: 9px;
        }
        .driver-info {
            margin-bottom: 10px;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 7px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 15px;
            font-size: 8px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($appSettings && $appSettings->institute_name)
            <h2>{{ $appSettings->institute_name }}</h2>
        @endif
        <h3>Driver Trip Report</h3>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}</p>
    </div>
    
    @if(count($reportData) > 0 && $driver)
        <div class="driver-info">
            <p><strong>Driver:</strong> {{ $driver->full_name }} ({{ $driver->driver_unique_id ?? 'N/A' }})</p>
            <p><strong>Date Range:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Total Trip</th>
                    <th>Bus Number</th>
                    <th>Helper</th>
                    @for($i = 1; $i <= $maxTripsPerDay; $i++)
                        <th>{{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} Trip</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $dayData)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($dayData['date'])->format('d M, Y') }}</td>
                        <td>{{ $dayData['day'] }}</td>
                        <td class="text-center"><strong>{{ $dayData['total_trips'] }}</strong></td>
                        <td><strong>{{ $dayData['bus_number'] }}</strong></td>
                        <td>{{ $dayData['helper'] }}</td>
                        @for($i = 0; $i < $maxTripsPerDay; $i++)
                            <td>
                                @if(isset($dayData['trips'][$i]))
                                    @php
                                        $trip = $dayData['trips'][$i];
                                    @endphp
                                    {{ $trip['route'] }} ({{ $trip['trip_type'] }} - {{ $trip['time'] }})
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="footer">
            <p>Total Days: {{ count($reportData) }} | Total Trips: {{ collect($reportData)->sum('total_trips') }}</p>
        </div>
    @endif
</body>
</html>


