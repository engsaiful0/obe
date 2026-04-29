<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>lubricant Records - PDF</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header { 
            margin-bottom: 15px; 
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px;
        }
        .filter-info {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            font-size: 9px;
        }
        .filter-info strong {
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }
        th {
            background-color: #333;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 15px;
            padding: 8px;
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            font-size: 9px;
        }
        .summary strong {
            font-weight: 600;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($appSettings)
            <h1>{{ $appSettings->app_name ?? 'Transport Management System' }}</h1>
            @if($appSettings->address)
                <p>{{ $appSettings->address }}</p>
            @endif
        @else
            <h1>Transport Management System</h1>
        @endif
        <h2 style="margin-top: 8px; font-size: 16px;">lubricant Records Report</h2>
    </div>

    <div class="filter-info">
        <strong>Report Period:</strong>
        @if($fromDate && $toDate)
            {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}
        @elseif($fromDate)
            From {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}
        @elseif($toDate)
            Until {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}
        @else
            All Records
        @endif
        | <strong>Total Records:</strong> {{ $lubricants->count() }}
    </div>

    @if($lubricants->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Bus Number</th>
                    <th>lubricant Amount</th>
                    <th>Unit</th>
                    <th>Concern Employee</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lubricants as $index => $lubricant)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $lubricant->lubricant_date->format('d M, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($lubricant->lubricant_time)->format('h:i A') }}</td>
                        <td><strong>{{ $lubricant->bus->bus_number ?? 'N/A' }}</strong></td>
                        <td class="text-right">{{ number_format($lubricant->lubricant_amount, 2) }}</td>
                        <td>{{ $lubricant->unit->unit_name ?? 'N/A' }}</td>
                        <td>{{ $lubricant->concernEmployee->employee_name ?? 'N/A' }}</td>
                        <td>{{ $lubricant->comment ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total:</strong></td>
                    <td class="text-right"><strong>{{ number_format($lubricants->sum('lubricant_amount'), 2) }}</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>

        <div class="summary">
            <strong>Summary:</strong><br>
            Total lubricant Records: {{ $lubricants->count() }}<br>
            Total lubricant Amount: {{ number_format($lubricants->sum('lubricant_amount'), 2) }} {{ $lubricants->first() && $lubricants->first()->unit ? $lubricants->first()->unit->unit_name : '' }}
        </div>
    @else
        <div style="padding: 15px; text-align: center; border: 1px solid #dee2e6; border-radius: 3px; background-color: #f8f9fa;">
            <p style="margin: 0; font-size: 11px;">No lubricant records found.</p>
        </div>
    @endif

    <div class="footer">
        Generated on: {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}
    </div>
</body>
</html>

