<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Report - PDF</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 15px;
            font-size: 8px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 20px;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 9px;
            color: #666;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 7px;
        }
        
        .table th,
        .table td {
            border: 1px solid #333;
            padding: 3px;
            text-align: center;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }
        
        .table tbody td:first-child {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: left;
        }
        
        .table tfoot th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .table tfoot th:first-child {
            background-color: #e9ecef;
        }
        
        .badge {
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
            background-color: #28a745;
            color: white;
        }
        
        .text-muted {
            color: #6c757d;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TMS IIUC - Trip Report</h1>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
        @if(isset($fromDate) && isset($toDate))
        <p>Report Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
        <p>Total Drivers: {{ isset($drivers) ? $drivers->count() : 0 }} | Total Days: {{ isset($dates) ? count($dates) : 0 }}</p>
        @endif
    </div>

    @if(isset($drivers) && isset($dates) && isset($pivotData))
    <table class="table">
        <thead>
            <tr>
                <th rowspan="2" class="align-middle" style="min-width: 150px;">
                    <strong>Driver Name</strong>
                </th>
                @foreach($dates as $date)
                <th style="min-width: 60px;">
                    {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                </th>
                @endforeach
                <th class="bg-primary text-white" style="min-width: 60px;">
                    <strong>Total</strong>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($drivers as $driver)
            <tr>
                <td>
                    <strong>{{ $driver->full_name }}</strong>
                    @if($driver->driver_unique_id)
                        <br><small class="text-muted">ID: {{ $driver->driver_unique_id }}</small>
                    @endif
                </td>
                @php
                    $driverTotal = 0;
                @endphp
                @foreach($dates as $date)
                    @php
                        $tripCount = $pivotData[$driver->id][$date] ?? 0;
                        $driverTotal += $tripCount;
                    @endphp
                    <td>
                        @if($tripCount > 0)
                            <span class="badge">{{ $tripCount }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                @endforeach
                <td class="bg-light">
                    <strong>{{ $driverTotal }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($dates) + 2 }}" class="text-center py-4">
                    <div class="text-muted">
                        No trips found for the selected date range.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-light">
                <th>
                    <strong>Date Total</strong>
                </th>
                @foreach($dates as $date)
                    @php
                        $dateTotal = 0;
                        foreach($drivers as $driver) {
                            $dateTotal += $pivotData[$driver->id][$date] ?? 0;
                        }
                    @endphp
                    <th>
                        <strong>{{ $dateTotal }}</strong>
                    </th>
                @endforeach
                <th class="bg-primary text-white">
                    @php
                        $grandTotal = 0;
                        foreach($drivers as $driver) {
                            foreach($dates as $date) {
                                $grandTotal += $pivotData[$driver->id][$date] ?? 0;
                            }
                        }
                    @endphp
                    <strong>{{ $grandTotal }}</strong>
                </th>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="text-center py-5">
        <p class="text-muted">No data available.</p>
    </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated document.</p>
        <p>Thank you for using TMS IIUC</p>
    </div>
</body>
</html>


