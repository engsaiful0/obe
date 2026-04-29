<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List Report - Print Preview</title>
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
            
        }
        
        .filter-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .filter-info strong {
            color: #333;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            text-align: center;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .table tr:hover {
            background-color: #f0f0f0;
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
            
            border-top: 1px solid #ddd;
            padding-top: 20px;
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
            
            .filter-info {
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
            <h1>TMS IIUC - Employee List Report</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
            <p>Total Records: {{ $employees->count() }}</p>
        </div>

        @if(!empty($filterInfo))
        <div class="filter-info">
            <strong>Applied Filters:</strong>
            @if(isset($filterInfo['designation']))
                Designation: {{ $filterInfo['designation'] }} |
            @endif
            @if(isset($filterInfo['employee_unique_id']))
                Employee ID: {{ $filterInfo['employee_unique_id'] }} |
            @endif
            @if(isset($filterInfo['year']))
                Year of Joining: {{ $filterInfo['year'] }}
            @endif
        </div>
        @endif

        @if($employees->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Designation</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Date of Join</th>
                        <th class="text-right">Basic Salary</th>
                        <th class="text-right">Gross Salary</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $index => $employee)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $employee->employee_unique_id }}</td>
                        <td>{{ $employee->employee_name }}</td>
                        <td>{{ $employee->designation->designation_name ?? 'N/A' }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->mobile }}</td>
                        <td>{{ ucfirst($employee->gender) }}</td>
                        <td>{{ $employee->date_of_join ? \Carbon\Carbon::parse($employee->date_of_join)->format('d M Y') : 'N/A' }}</td>
                        <td class="text-right">{{ $employee->basic_salary ? '৳' . number_format($employee->basic_salary, 2) : 'N/A' }}</td>
                        <td class="text-right">{{ $employee->gross_salary ? '৳' . number_format($employee->gross_salary, 2) : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #e9ecef; font-weight: bold;">
                        <td colspan="8" class="text-right">Total Employees:</td>
                        <td colspan="2" class="text-center">{{ $employees->count() }}</td>
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

