<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus List Report - Print Preview</title>
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
            font-size: 10px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
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
        
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-primary {
            background-color: #007bff;
            color: white;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #333;
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
                font-size: 8px !important;
            }
            
            .table th,
            .table td {
                padding: 4px !important;
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
            <h1>TMS IIUC - Bus List Report</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
            <p>Total Records: {{ $buses->count() }}</p>
        </div>

        @if(!empty($filterInfo))
        <div class="filter-info">
            <strong>Applied Filters:</strong>
            @if(isset($filterInfo['bus_sub_type']))
                Bus Sub Type: {{ $filterInfo['bus_sub_type'] }} |
            @endif
            @if(isset($filterInfo['status']))
                Status: {{ $filterInfo['status'] }} |
            @endif
            @if(isset($filterInfo['search']))
                Search: {{ $filterInfo['search'] }}
            @endif
        </div>
        @endif

        @if($buses->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Bus Number</th>
                        <th>Model Name</th>
                        <th>Bus Type</th>
                        <th>Bus Sub Type</th>
                        <th>Brand</th>
                        <th>Registration Number</th>
                        <th>Chassis Number</th>
                        <th>Engine Number</th>
                        <th>Year</th>
                        <th>Color</th>
                        <th>Fuel Type</th>
                        <th>Seating Capacity</th>
                        <th>Driver</th>
                        <th>Bus Helper</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Fixed Price</th>
                        <th>Rate Per KM</th>
                        <th>Current Mileage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($buses as $index => $bus)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $bus->bus_number ?? 'N/A' }}</strong></td>
                        <td>{{ $bus->model_name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-info">{{ $bus->busType->bus_type_name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $bus->busSubType->sub_type_name ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $bus->brand->brand_name ?? 'N/A' }}</td>
                        <td><strong>{{ $bus->registration_number ?? 'N/A' }}</strong></td>
                        <td>{{ $bus->chassis_number ?? 'N/A' }}</td>
                        <td>{{ $bus->engine_number ?? 'N/A' }}</td>
                        <td>{{ $bus->yearOfManufacture->year_name ?? 'N/A' }}</td>
                        <td>{{ $bus->color->color_name ?? 'N/A' }}</td>
                        <td>{{ $bus->fuelType->fuel_type_name ?? 'N/A' }}</td>
                        <td>{{ $bus->seating_capacity ?? 'N/A' }}</td>
                        <td>{{ $bus->driver->full_name ?? 'N/A' }}</td>
                        <td>{{ $bus->busHelper->bus_helper_name ?? 'N/A' }}</td>
                        <td>{{ $bus->supplier->supplier_name ?? 'N/A' }}</td>
                        <td>
                            @if($bus->statusOptions && $bus->statusOptions->status_name == 'active')
                                <span class="badge badge-success">Active</span>
                            @elseif($bus->statusOptions && $bus->statusOptions->status_name == 'inactive')
                                <span class="badge badge-secondary">Inactive</span>
                            @elseif($bus->statusOptions && $bus->statusOptions->status_name == 'under_maintenance')
                                <span class="badge badge-warning">Under Maintenance</span>
                            @else
                                <span class="badge badge-secondary">{{ $bus->statusOptions ? ucfirst(str_replace('_', ' ', $bus->statusOptions->status_name)) : 'N/A' }}</span>
                            @endif
                        </td>
                        <td class="text-right">{{ $bus->fixed_price ? '৳' . number_format($bus->fixed_price, 2) : 'N/A' }}</td>
                        <td class="text-right">{{ $bus->rate_per_km ? '৳' . number_format($bus->rate_per_km, 2) : 'N/A' }}</td>
                        <td class="text-right">{{ $bus->current_mileage ? number_format($bus->current_mileage, 2) : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="19" class="text-right"><strong>Total Buses:</strong></td>
                        <td class="text-center"><strong>{{ $buses->count() }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No buses found to print.</p>
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


