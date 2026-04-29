<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Requisition - {{ $busRequisition->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
            padding: 10px;
        }
        
        .requisition-wrapper {
            background: white;
            border: 1px solid #ddd;
        }
        
        .requisition-header {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: center;
        }
        
        .requisition-header h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .requisition-header p {
            font-size: 9px;
            margin-bottom: 6px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        
        .status-pending {
            background: #ffc107;
            color: #000;
        }
        
        .status-approved {
            background: #28a745;
            color: white;
        }
        
        .status-rejected {
            background: #dc3545;
            color: white;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .info-table tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-table tr:last-child {
            border-bottom: none;
        }
        
        .info-table td {
            padding: 0.4rem 0.5rem;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 30%;
            font-weight: bold;
            color: #495057;
            background: #f8f9fa;
            border-right: 1px solid #e9ecef;
            font-size: 8px;
        }
        
        .info-table td:last-child {
            width: 70%;
            color: #212529;
            font-size: 8px;
        }
        
        .info-table .highlight-value {
            font-size: 11px;
            font-weight: bold;
            color: #667eea;
        }
        
        .info-table .purpose-cell {
            padding: 0.6rem;
            line-height: 1.5;
            color: #495057;
            background: #f8f9fa;
            font-size: 8px;
        }
        
        .info-table .remarks-cell {
            padding: 0.6rem;
            line-height: 1.5;
            background: #fff9e6;
            color: #856404;
            border-left: 3px solid #ffc107;
            font-size: 8px;
        }
        
        .info-table .status-row {
            background: rgba(102, 126, 234, 0.15) !important;
            border-top: 2px solid #667eea;
            border-bottom: 2px solid #667eea;
        }
        
        .info-table .status-row td:first-child {
            background: rgba(102, 126, 234, 0.2) !important;
            font-weight: bold;
            color: #667eea;
            font-size: 9px;
        }
        
        .info-table .status-row td:last-child {
            font-weight: bold;
        }
        
        .info-table .status-cell {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-table .status-cell.status-pending {
            background: #ffc107;
            color: #000;
        }
        
        .info-table .status-cell.status-approved {
            background: #28a745;
            color: white;
        }
        
        .info-table .status-cell.status-rejected {
            background: #dc3545;
            color: white;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="requisition-wrapper">
        <!-- Header -->
        <div class="requisition-header">
            <h2>Requisition #{{ $busRequisition->id }}</h2>
            <p>Bus Requisition Form</p>
            @php
                $statusClass = 'status-pending';
                $statusText = 'Pending';
                if ($busRequisition->status == 'approved') {
                    $statusClass = 'status-approved';
                    $statusText = 'Approved';
                } elseif ($busRequisition->status == 'rejected') {
                    $statusClass = 'status-rejected';
                    $statusText = 'Rejected';
                }
            @endphp
            <div class="status-badge {{ $statusClass }}">{{ $statusText }}</div>
        </div>

        <!-- Information Table -->
        <table class="info-table table table-striped">
            <tr class="status-row">
                <td>Status</td>
                <td>
                    @php
                        $statusClass = 'status-pending';
                        $statusText = 'Pending';
                        if ($busRequisition->status == 'approved') {
                            $statusClass = 'status-approved';
                            $statusText = 'Approved';
                        } elseif ($busRequisition->status == 'rejected') {
                            $statusClass = 'status-rejected';
                            $statusText = 'Rejected';
                        }
                    @endphp
                    <span class="status-cell {{ $statusClass }}">{{ $statusText }}</span>
                </td>
            </tr>
            
            <tr>
                <td>Date</td>
                <td>{{ $busRequisition->date ? $busRequisition->date->format('F d, Y') : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Department</td>
                <td>{{ $busRequisition->department->name ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Required Bus Date</td>
                <td>{{ $busRequisition->required_bus_date ? $busRequisition->required_bus_date->format('F d, Y') : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Required Time</td>
                <td>
                    @if($busRequisition->required_time)
                        @php
                            try {
                                $time = \Carbon\Carbon::createFromFormat('H:i:s', $busRequisition->required_time)->format('h:i A');
                            } catch (\Exception $e) {
                                $time = \Carbon\Carbon::createFromFormat('H:i', $busRequisition->required_time)->format('h:i A');
                            }
                        @endphp
                        {{ $time }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            
            <tr>
                <td>Number of Buses</td>
                <td><span class="highlight-value">{{ $busRequisition->number_of_buses }}</span></td>
            </tr>
            
            <tr>
                <td>Total Passengers</td>
                <td><span class="highlight-value">{{ $busRequisition->total_passengers }}</span></td>
            </tr>
            
            <tr>
                <td>Purpose of Requisition</td>
                <td class="purpose-cell">{{ $busRequisition->purpose ?? 'No purpose specified' }}</td>
            </tr>
            
            <tr>
                <td>Requisition Sender Name</td>
                <td>{{ $busRequisition->requisition_sender_name }}</td>
            </tr>
            
            <tr>
                <td>Mobile Number</td>
                <td>{{ $busRequisition->mobile_number }}</td>
            </tr>
            
            <tr>
                <td>Email Address</td>
                <td>{{ $busRequisition->email_address }}</td>
            </tr>
            
            @if($busRequisition->remarks)
            <tr>
                <td>Remarks</td>
                <td class="remarks-cell">{{ $busRequisition->remarks }}</td>
            </tr>
            @endif
            
            @if($busRequisition->user)
            <tr>
                <td>Created By</td>
                <td>{{ $busRequisition->user->name ?? 'N/A' }}</td>
            </tr>
            @endif
            
            <tr>
                <td>Created At</td>
                <td>{{ $busRequisition->created_at ? $busRequisition->created_at->format('F d, Y h:i A') : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Last Updated</td>
                <td>{{ $busRequisition->updated_at ? $busRequisition->updated_at->format('F d, Y h:i A') : 'N/A' }}</td>
            </tr>
        </table>
        
        <div class="footer">
            <p>Generated on {{ now()->format('F d, Y h:i A') }} | Bus Requisition Management System</p>
        </div>
    </div>
</body>
</html>
