<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Driver Trip Report - Print</title>
    <style>
        @media print {
            .no-print { 
                display: none !important; 
            }
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 9px;
            line-height: 1.4;
            margin: 20px;
        }
        .print-actions {
            margin-bottom: 20px;
            text-align: right;
        }
        .print-actions button {
            padding: 8px 16px;
            margin-left: 10px;
            cursor: pointer;
            border: 1px solid #007bff;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            font-size: 14px;
        }
        .print-actions button:hover {
            background-color: #0056b3;
        }
        .print-actions .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .header { 
            margin-bottom: 20px; 
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
            font-weight: bold;
        }
        .header h3 {
            margin: 10px 0 5px 0;
            font-size: 18px;
        }
        .header p {
            margin: 3px 0;
            color: #666;
            font-size: 11px;
        }
        .driver-info {
            margin-bottom: 15px;
            font-size: 11px;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        .table th, .table td { 
            border: 1px solid #000; 
            padding: 5px; 
            text-align: left;
            font-size: 8px;
        }
        .table th { 
            background-color: #343a40; 
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()" class="btn-secondary">Close</button>
    </div>

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

        <table class="table">
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
        
        <div class="summary">
            <p><strong>Total Days:</strong> {{ count($reportData) }} | <strong>Total Trips:</strong> {{ collect($reportData)->sum('total_trips') }}</p>
        </div>
    @else
        <div style="padding: 20px; text-align: center; border: 1px solid #ddd; background-color: #fff3cd; color: #856404;">
            <strong>No trips found.</strong>
        </div>
    @endif
</body>
</html>


