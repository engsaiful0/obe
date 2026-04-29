<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daywise Trip Report - {{ $bus->bus_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
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
        .bus-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .bus-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .bus-info td {
            padding: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        tfoot {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $appSetting->institute_name ?? 'Transport Management System' }}</h2>
        <h3>Daywise Trip Report</h3>
    </div>

    <div class="bus-info">
        <table>
            <tr>
                <td><strong>Bus Number:</strong> {{ $bus->bus_number }}</td>
                <td><strong>Model:</strong> {{ $bus->model_name }}</td>
                <td><strong>Bus Type:</strong> {{ $bus->busSubType->sub_type_name ?? 'N/A' }}</td>
                <td><strong>Period:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Serial</th>
                <th>Date</th>
                <th>Trip No.</th>
                <th>Type</th>
                <th>From Stoppage</th>
                <th>To Stoppage</th>
                <th>Distance (KM)</th>
                <th>In Time</th>
                <th>Out Time</th>
                <th>Passengers</th>
                <th>Driver</th>
                <th>Bus Helper</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDistance = 0;
                $totalPassengers = 0;
            @endphp
            @foreach($trips as $trip)
            @php
                // Calculate distance from stoppages
                $distance = 0;
                if ($trip->trip_type === 'in') {
                    $distance = $trip->startStoppage->distance ?? 0;
                } else {
                    $distance = $trip->endStoppage->distance ?? 0;
                }
                $totalDistance += $distance;
                $totalPassengers += $trip->passengers ?? 0;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $trip->trip_date->format('d-m-Y') }}</td>
                <td class="text-center">{{ $trip->trip_number ?? '-' }}</td>
                <td class="text-center">{{ strtoupper($trip->trip_type) }}</td>
                <td>{{ $trip->startStoppage->stoppage_name ?? 'N/A' }}</td>
                <td>{{ $trip->endStoppage->stoppage_name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($distance, 2) }}</td>
                <td class="text-center">
                    @if($trip->in_time)
                        {{ \Carbon\Carbon::parse($trip->in_time)->format('h:i A') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    @if($trip->out_time)
                        {{ \Carbon\Carbon::parse($trip->out_time)->format('h:i A') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $trip->passengers ?? 0 }}</td>
                <td>{{ $trip->driver->full_name ?? 'N/A' }}</td>
                <td>{{ $trip->busHelper->bus_helper_name ?? 'N/A' }}</td>
                <td>{{ $trip->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($totalDistance, 2) }}</strong></td>
                <td colspan="2"></td>
                <td class="text-center"><strong>{{ $totalPassengers }}</strong></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        <p>{{ $appSetting->institute_name ?? 'Transport Management System' }}</p>
    </div>
</body>
</html>

