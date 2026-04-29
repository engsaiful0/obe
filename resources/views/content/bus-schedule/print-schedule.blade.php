<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bus Schedule - Print View</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
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
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #333;
        }
        .info-value {
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
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
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-primary {
            background-color: #007bff;
            color: white;
        }
        .no-print {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            @page {
                margin: 1cm;
            }
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px;">
            <i class="ti ti-printer"></i> Print This Page
        </button>
        <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px; font-size: 14px;">
            Close
        </button>
    </div>

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
                <span class="badge badge-primary">{{ $busSchedule->status->status_name ?? 'N/A' }}</span>
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

    <h3 style="margin-top: 30px; margin-bottom: 15px; color: #333;">Schedule Entries</h3>
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
                <td colspan="5" class="text-center">No entries found.</td>
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

