<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Driver Helper Assignment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
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
            margin-top: 20px;
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
        <h3>Driver Helper Assignment Report</h3>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Serial</th>
                <th style="width: 10%;">Bus Number</th>
                <th style="width: 10%;">Bus Type</th>
                <th style="width: 10%;">Bus Sub Type</th>
                <th style="width: 12%;">Driver Name</th>
                <th style="width: 10%;">Driver Mobile</th>
                <th style="width: 12%;">Helper Name</th>
                <th style="width: 10%;">Helper Mobile</th>
                <th style="width: 10%;">Assignment Date</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 13%;">Comment</th>
            </tr>
        </thead>
        <tbody>
            @php
                $serial = 1;
            @endphp
            @foreach($assignments as $assignment)
                <tr>
                    <td class="text-center">{{ $serial++ }}</td>
                    <td><strong>{{ $assignment->bus->bus_number ?? 'N/A' }}</strong></td>
                    <td>{{ $assignment->bus->busType->bus_type_name ?? 'N/A' }}</td>
                    <td>{{ $assignment->bus->busSubType->sub_type_name ?? 'N/A' }}</td>
                    <td>{{ $assignment->driver->full_name ?? 'N/A' }}</td>
                    <td>{{ $assignment->driver->contact_number ?? 'N/A' }}</td>
                    <td>{{ $assignment->busHelper->bus_helper_name ?? 'N/A' }}</td>
                    <td>{{ $assignment->busHelper->mobile ?? 'N/A' }}</td>
                    <td>{{ $assignment->assignment_date ? $assignment->assignment_date->format('d M, Y') : 'N/A' }}</td>
                    <td>{{ $assignment->status->status_name ?? 'N/A' }}</td>
                    <td>{{ $assignment->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Total Assignments: {{ count($assignments) }}</p>
    </div>
</body>
</html>

