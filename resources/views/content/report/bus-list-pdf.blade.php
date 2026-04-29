<!DOCTYPE html>
<html lang="bn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Bus List Report</title>
    <style>
        @charset "UTF-8";
        * {
            font-family: {{ isset($banglaFont) ? "'{$banglaFont}'" : "'DejaVu Sans'" }}, sans-serif;
            unicode-bidi: embed;
        }
        body {
            font-family: {{ isset($banglaFont) ? "'{$banglaFont}'" : "'DejaVu Sans'" }}, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            direction: ltr;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        .header p {
            margin: 3px 0;
            color: #666;
            font-size: 11px;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            font-size: 9px;
        }
        .filters h3 {
            margin: 0 0 5px 0;
            font-size: 11px;
        }
        .filters p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .summary {
            background-color: #e9ecef;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            font-size: 9px;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
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
            color: #000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bus List Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>Total Buses: {{ $buses->count() }}</p>
    </div>

    @if(!empty($filters) && array_filter($filters))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(isset($filters['bus_sub_type_id']) && $filters['bus_sub_type_id'] != 'all')
            @php
                $subType = \App\Models\BusSubType::find($filters['bus_sub_type_id']);
            @endphp
            @if($subType)
                <p><strong>Bus Sub Type:</strong> {{ $subType->sub_type_name }}</p>
            @endif
        @endif
        @if(isset($filters['status_id']) && $filters['status_id'] != 'all')
            @php
                $status = \App\Models\Status::find($filters['status_id']);
            @endphp
            @if($status)
                <p><strong>Status:</strong> {{ ucfirst($status->status_name) }}</p>
            @endif
        @endif
        @if(isset($filters['search']) && $filters['search'])
            <p><strong>Search:</strong> {{ $filters['search'] }}</p>
        @endif
    </div>
    @endif

    <div class="summary">
        <strong>Report Summary:</strong> 
        Showing {{ $buses->count() }} bus(es)
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Bus Number</th>
                <th>Model Name</th>
                <th>Bus Type</th>
                <th>Bus Sub Type</th>
                <th>Brand</th>
                <th>Registration</th>
                <th>Chassis</th>
                <th>Engine</th>
                <th>Year</th>
                <th>Color</th>
                <th>Fuel Type</th>
                <th>Seating</th>
                <th>Driver</th>
                <th>Helper</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Fixed Price</th>
                <th>Rate/KM</th>
                <th>Mileage</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($buses as $bus)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $bus->bus_number ?? 'N/A' }}</strong></td>
                <td>{{ $bus->model_name ?? 'N/A' }}</td>
                <td>{{ $bus->busType->bus_type_name ?? 'N/A' }}</td>
                <td>{{ $bus->busSubType->sub_type_name ?? 'N/A' }}</td>
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
                    @if($bus->status && $bus->status->status_name == 'active')
                        <span class="badge badge-success">Active</span>
                    @elseif($bus->status && $bus->status->status_name == 'inactive')
                        <span class="badge badge-secondary">Inactive</span>
                    @elseif($bus->status && $bus->status->status_name == 'under_maintenance')
                        <span class="badge badge-warning">Under Maintenance</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $bus->status->status_name ?? 'N/A')) }}</span>
                    @endif
                </td>
                <td>{{ $bus->fixed_price ? number_format($bus->fixed_price, 2) : 'N/A' }}</td>
                <td>{{ $bus->rate_per_km ? number_format($bus->rate_per_km, 2) : 'N/A' }}</td>
                <td>{{ $bus->current_mileage ? number_format($bus->current_mileage, 2) : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="20" style="text-align: center; padding: 20px;">
                    No buses found.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="19" style="text-align: right;">Total Buses:</th>
                <th>{{ $buses->count() }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the TMS System.</p>
        <p>© {{ date('Y') }} All rights reserved.</p>
    </div>
</body>
</html>

