<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bus Schedule - PDF Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
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
            font-size: 20px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 10px;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .info-row {
            margin-bottom: 8px;
            display: table;
            width: 100%;
        }
        .info-label {
            font-weight: bold;
            display: table-cell;
            width: 150px;
            color: #333;
        }
        .info-value {
            display: table-cell;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: auto;
        }
        thead {
            display: table-header-group;
        }
        tbody {
            display: table-row-group;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            background-color: #007bff;
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 9px;
        }
        @page {
            margin: 1.5cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bus Schedule Details</h1>
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Schedule Category:</div>
            <div class="info-value">{{ $busSchedule->keyword->keyword_name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">
                <span class="badge">{{ $busSchedule->status->status_name ?? 'N/A' }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Bus User:</div>
            <div class="info-value">{{ $busSchedule->busUser->bus_user_name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Effective From:</div>
            <div class="info-value">{{ $busSchedule->effective_from ? \Carbon\Carbon::parse($busSchedule->effective_from)->format('F d, Y') : 'N/A' }}</div>
        </div>
        @if($busSchedule->description)
        <div class="info-row">
            <div class="info-label">Remarks:</div>
            <div class="info-value">{{ $busSchedule->description }}</div>
        </div>
        @endif
    </div>

    <h3 style="margin-top: 25px; margin-bottom: 12px; color: #333; font-size: 14px;">Schedule Entries</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Start Time</th>
                <th>Starting Point</th>
                <th>Bus Route</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($busSchedule->entries as $entry)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    @if($entry->start_time)
                        @php
                            $time = \Carbon\Carbon::parse($entry->start_time);
                            $hour24 = (int)$time->format('G');
                            if ($hour24 == 0) {
                                $hour12 = 12;
                                $amPm = 'AM';
                            } elseif ($hour24 < 12) {
                                $hour12 = $hour24;
                                $amPm = 'AM';
                            } elseif ($hour24 == 12) {
                                $hour12 = 12;
                                $amPm = 'PM';
                            } else {
                                $hour12 = $hour24 - 12;
                                $amPm = 'PM';
                            }
                        @endphp
                        {{ $hour12 }}:{{ $time->format('i') }} {{ $amPm }}
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $entry->startingPoint->stoppage_name ?? 'N/A' }}</td>
                <td>{{ $entry->busRoute->route_name ?? 'N/A' }}</td>
                <td>{{ $entry->description ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">No entries found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This document was generated on {{ date('F d, Y H:i:s') }}</p>
        <p>Total Entries: {{ $busSchedule->entries->count() }}</p>
    </div>
</body>
</html>

