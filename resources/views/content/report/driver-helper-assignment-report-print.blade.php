<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Driver Helper Assignment Report - Print</title>
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
            font-size: 10px;
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
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        .table th, .table td { 
            border: 1px solid #000; 
            padding: 6px; 
            text-align: left;
            font-size: 9px;
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
        .text-end { 
            text-align: right; 
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .summary h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 12px;
        }
        .summary p {
            margin: 5px 0;
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
        <h3>Driver Helper Assignment Report</h3>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}</p>
    </div>

    @if(count($assignments) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>Bus Number</th>
                    <th>Bus Type</th>
                    <th>Bus Sub Type</th>
                    <th>Driver Name</th>
                    <th>Driver Mobile</th>
                    <th>Helper Name</th>
                    <th>Helper Mobile</th>
                    <th>Assignment Date</th>
                    <th>Status</th>
                    <th>Comment</th>
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
        
        <div class="summary">
            <h4>Report Summary</h4>
            <p><strong>Total Assignments:</strong> {{ count($assignments) }}</p>
        </div>
    @else
        <div style="padding: 20px; text-align: center; border: 1px solid #ddd; background-color: #fff3cd; color: #856404;">
            <strong>No assignments found.</strong>
        </div>
    @endif
</body>
</html>

