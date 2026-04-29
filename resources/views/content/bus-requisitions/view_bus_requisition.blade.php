@extends('layouts/layoutMaster')

@section('title', 'View Bus Requisition')

@section('page-style')
<style>
    .requisition-wrapper {
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .requisition-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .requisition-header-left h2 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .requisition-header-left p {
        margin: 0.5rem 0 0 0;
        opacity: 0.95;
        font-size: 0.95rem;
    }
    
    .status-badge {
        padding: 0.6rem 1.2rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
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
        transition: background-color 0.2s;
    }
    
    .info-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .info-table tr:last-child {
        border-bottom: none;
    }
    
    .info-table td {
        padding: 1rem 1.25rem;
        vertical-align: top;
    }
    
    .info-table td:first-child {
        width: 30%;
        font-weight: 600;
        color: #495057;
        background: #f8f9fa;
        border-right: 2px solid #e9ecef;
        font-size: 0.9rem;
    }
    
    .info-table td:last-child {
        width: 70%;
        color: #212529;
        font-size: 0.95rem;
    }
    
    .info-table .highlight-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #667eea;
    }
    
    .info-table .purpose-cell {
        padding: 1.25rem;
        line-height: 1.8;
        color: #495057;
        background: #f8f9fa;
    }
    
    .info-table .remarks-cell {
        padding: 1.25rem;
        line-height: 1.8;
        background: #fff9e6;
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    .info-table .status-row {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%) !important;
        border-top: 3px solid #667eea;
        border-bottom: 3px solid #667eea;
    }
    
    .info-table .status-row td:first-child {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%) !important;
        font-weight: 700;
        color: #667eea;
        font-size: 1rem;
    }
    
    .info-table .status-row td:last-child {
        font-weight: 700;
    }
    
    .info-table .status-cell {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
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
    
    .info-table a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }
    
    .info-table a:hover {
        text-decoration: underline;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    /* Print Styles */
    @media print {
        @page {
            size: A4;
            margin: 0.6cm;
        }
        
        * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .no-print {
            display: none !important;
        }
        
        body {
            background: white;
            font-size: 9px;
        }
        
        .requisition-wrapper {
            box-shadow: none;
            border: 1px solid #333;
        }
        
        .requisition-header {
            background: #667eea !important;
            color: white !important;
            padding: 0.6rem;
        }
        
        .requisition-header-left h2 {
            font-size: 14px;
        }
        
        .requisition-header-left p {
            font-size: 9px;
        }
        
        .status-badge {
            padding: 0.3rem 0.8rem;
            font-size: 7px;
        }
        
        .info-table {
            border: 1px solid #333;
        }
        
        .info-table tr {
            border-bottom: 1px solid #ddd;
        }
        
        .info-table tr:hover {
            background-color: transparent;
        }
        
        .info-table td {
            padding: 0.35rem 0.5rem;
            font-size: 8px;
        }
        
        .info-table td:first-child {
            width: 30%;
            font-size: 7px;
            background: #f8f9fa !important;
        }
        
        .info-table td:last-child {
            font-size: 8px;
        }
        
        .info-table .highlight-value {
            font-size: 10px;
        }
        
        .info-table .purpose-cell,
        .info-table .remarks-cell {
            padding: 0.5rem;
            font-size: 7px;
            line-height: 1.4;
        }
        
        .info-table .status-row {
            background: rgba(102, 126, 234, 0.1) !important;
            border-top: 2px solid #667eea !important;
            border-bottom: 2px solid #667eea !important;
        }
        
        .info-table .status-row td:first-child {
            background: rgba(102, 126, 234, 0.15) !important;
            font-size: 8px;
        }
        
        .info-table .status-cell {
            padding: 0.2rem 0.6rem;
            font-size: 7px;
        }
        
        .info-table a {
            color: #333 !important;
            text-decoration: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="mb-0">Bus Requisition Details</h4>
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="ti ti-printer me-1"></i> Print
            </button>
            <a href="{{ route('app-bus-requisitions.pdf', $busRequisition->id) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('app-bus-requisitions.edit', $busRequisition->id) }}" class="btn btn-warning">
                <i class="ti ti-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('app-bus-requisitions') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="requisition-wrapper">
        <!-- Header -->
        <div class="requisition-header">
            <div class="requisition-header-left">
                <h2>Requisition #{{ $busRequisition->id }}</h2>
                <p>Bus Requisition Form</p>
            </div>
            <div>
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
                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
            </div>
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
                <td>
                    <a href="tel:{{ $busRequisition->mobile_number }}">{{ $busRequisition->mobile_number }}</a>
                </td>
            </tr>
            
            <tr>
                <td>Email Address</td>
                <td>
                    <a href="mailto:{{ $busRequisition->email_address }}">{{ $busRequisition->email_address }}</a>
                </td>
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
    </div>
</div>
@endsection
