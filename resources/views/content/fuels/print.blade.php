<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fuel Records - Print</title>
    <style>
        @media print {
            .no-print { 
                display: none !important; 
            }
            .page-break { 
                page-break-after: always; 
            }
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px;
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
        .print-actions .btn-secondary:hover {
            background-color: #545b62;
        }
        .header { 
            margin-bottom: 20px; 
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .filter-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .filter-info strong {
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
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
            margin-top: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .summary strong {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()">Print</button>
        <button class="btn-secondary" onclick="window.close()">Close</button>
    </div>

    <div class="header">
        @if($appSettings)
            <h1>{{ $appSettings->app_name ?? 'Transport Management System' }}</h1>
            @if($appSettings->address)
                <p>{{ $appSettings->address }}</p>
            @endif
        @else
            <h1>Transport Management System</h1>
        @endif
        <h2 style="margin-top: 10px; font-size: 18px;">Fuel Records Report</h2>
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
        | <strong>Total Records:</strong> {{ $fuels->count() }}
    </div>

    @if($fuels->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Bus Number</th>
                    <th>Fuel Amount</th>
                    <th>Unit</th>
                    <th>Concern Employee</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fuels as $index => $fuel)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $fuel->fuel_date->format('d M, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($fuel->fuel_time)->format('h:i A') }}</td>
                        <td><strong>{{ $fuel->bus->bus_number ?? 'N/A' }}</strong></td>
                        <td class="text-right">{{ number_format($fuel->fuel_amount, 2) }}</td>
                        <td>{{ $fuel->unit->unit_name ?? 'N/A' }}</td>
                        <td>{{ $fuel->concernEmployee->employee_name ?? 'N/A' }}</td>
                        <td>{{ $fuel->comment ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total:</strong></td>
                    <td class="text-right"><strong>{{ number_format($fuels->sum('fuel_amount'), 2) }}</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>

        <div class="summary">
            <strong>Summary:</strong><br>
            Total Fuel Records: {{ $fuels->count() }}<br>
            Total Fuel Amount: {{ number_format($fuels->sum('fuel_amount'), 2) }} {{ $fuels->first() && $fuels->first()->unit ? $fuels->first()->unit->unit_name : '' }}
        </div>
    @else
        <div style="padding: 20px; text-align: center; border: 1px solid #dee2e6; border-radius: 4px; background-color: #f8f9fa;">
            <p style="margin: 0; font-size: 14px;">No fuel records found.</p>
        </div>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #6c757d;">
        Generated on: {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}
    </div>
</body>
</html>

