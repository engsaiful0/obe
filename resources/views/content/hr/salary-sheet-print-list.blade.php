<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Sheet - Print Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        
        .print-controls button {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .print-controls button:hover {
            background: #0056b3;
        }
        
        .print-controls button.close-btn {
            background: #6c757d;
        }
        
        .print-controls button.close-btn:hover {
            background: #5a6268;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        
        .print-header h1 {
            font-size: 28px;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .print-header p {
            font-size: 14px;
            color: #666;
        }
        
        .period-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }
        
        .period-info strong {
            color: #333;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .print-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }
        
        .warning-message {
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #856404;
        }
        
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            
            .print-controls {
                display: none !important;
            }
            
            .container {
                box-shadow: none !important;
                padding: 20px !important;
                max-width: 100% !important;
            }
            
            .table {
                font-size: 10px !important;
            }
            
            .table th,
            .table td {
                padding: 6px !important;
                border: 1px solid #333 !important;
            }
            
            .period-info {
                background-color: #f0f0f0 !important;
            }
            
            @page {
                margin: 1cm;
                size: A4 landscape;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button onclick="window.print()">
            🖨️ Print
        </button>
        <button class="close-btn" onclick="window.close()">
            ✕ Close
        </button>
    </div>

    <div class="container">
        <div class="print-header">
            <h1>TMS IIUC - Salary Sheet</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
        </div>

        <div class="period-info">
            <strong>Period:</strong> {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
        </div>

        @if(!$setting)
        <div class="warning-message">
            <strong>⚠️ Warning:</strong> No salary configuration found for {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}. Please create it first.
        </div>
        @endif

        @if($employees->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th class="text-center">Present Days</th>
                        <th class="text-right">Basic Salary</th>
                        <th class="text-right">Daily Rate</th>
                        <th class="text-right">Base Salary</th>
                        <th class="text-right">Overtime</th>
                        <th class="text-right">Deductions</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $index => $employee)
                        @php($calc = $employee->salary_calculation)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $employee->employee_name ?? ($employee->full_name ?? 'Employee #'.$employee->id) }}</td>
                            <td class="text-center">{{ $employee->present_days }}</td>
                            <td class="text-right">৳{{ number_format($calc['basic_salary'] ?? ($employee->basic_salary ?? 0), 2) }}</td>
                            <td class="text-right">৳{{ number_format($calc['daily_rate'] ?? 0, 2) }}</td>
                            <td class="text-right">৳{{ number_format($calc['base_salary'] ?? 0, 2) }}</td>
                            <td class="text-right">৳{{ number_format($calc['overtime_amount'] ?? 0, 2) }}</td>
                            <td class="text-right">৳{{ number_format($calc['deductions'] ?? 0, 2) }}</td>
                            <td class="text-right"><strong>৳{{ number_format($calc['total_salary'] ?? 0, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($totals['total_basic'], 2) }}</strong></td>
                        <td></td>
                        <td class="text-right"><strong>৳{{ number_format($totals['total_base_salary'], 2) }}</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($totals['total_overtime'], 2) }}</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($totals['total_deductions'], 2) }}</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($totals['total_salary'], 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No employees found to print.</p>
        </div>
        @endif

        <div class="print-footer">
            <p>This is a computer-generated document.</p>
            <p>Thank you for using TMS IIUC</p>
        </div>
    </div>

    <script>
        // Optional: Auto-print after page loads (commented out)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>


