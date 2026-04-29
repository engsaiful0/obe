@extends('layouts/layoutMaster')

@section('title', 'Bus Details')

@section('content')
<div class="row">
    <!-- Bus Information Card -->
    <div class="col-lg-12">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Bus Details</h5>
                <div class="d-flex gap-2">
                    <button type="button" id="printBusDetailsBtn" class="btn btn-info btn-sm">
                        <i class="ti ti-printer me-1"></i>Print
                    </button>
                    <a href="{{ route('buses.edit', $bus) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('buses.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive mb-4" id="busDetailsPrintArea">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold w-25">Picture</td>
                                <td>
                                    @if($bus->bus_photo)
                                    <img src="{{ asset('storage/app/public/' . $bus->bus_photo) }}" alt="{{ $bus->display_name }}" class="img-fluid rounded" style="max-height: 120px;">
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td class="fw-semibold w-25">Bus Number</td>
                                <td>{{ $bus->bus_number ?? 'N/A' }}</td>
                                <td class="fw-semibold">Bus Type</td>
                                <td>{{ $bus->busType->bus_type_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                             
                                <td class="fw-semibold">Oil Required Per KM</td>
                                <td>{{ $bus->required_oil_per_km ?? 'N/A' }}</td>
                           
                                <td class="fw-semibold">Fixed Price</td>
                                <td>{{ $bus->fixed_price ?? 'N/A' }}</td>
                                <td class="fw-semibold">Rate Per KM</td>
                                <td>{{ $bus->rate_per_km ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Registration Number</td>
                                <td>{{ $bus->registration_number ?? 'N/A' }}</td>
                                <td class="fw-semibold">Registration Date</td>
                                <td>{{ $bus->registration_date?->format('M d, Y') ?? 'N/A' }}</td>
                           
                                <td class="fw-semibold">Registration Expiry</td>
                                <td>{{ $bus->registration_expiry?->format('M d, Y') ?? 'N/A' }}</td>
                              
                            </tr>
                            <tr>
                                <td class="fw-semibold">Model Name</td>
                                <td>{{ $bus->model_name ?? 'N/A' }}</td>
                                <td class="fw-semibold">Brand</td>
                                <td>{{ $bus->brand->brand_name ?? 'N/A' }}</td>
                                <td class="fw-semibold">Color</td>
                                <td>{{ $bus->color->color_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Fuel Type</td>
                                <td>{{ $bus->fuelType->fuel_type_name ?? 'N/A' }}</td>
                                <td class="fw-semibold">Chassis Number</td>
                                <td>{{ $bus->chassis_number ?? 'N/A' }}</td>
                       
                                <td class="fw-semibold">Engine Number</td>
                                <td>{{ $bus->engine_number ?? 'N/A' }}</td>
                               
                            </tr>
                            <tr>
                                <td class="fw-semibold">Year of Manufacture</td>
                                <td>{{ $bus->yearOfManufacture->year_name ?? 'N/A' }}</td>
                                <td class="fw-semibold">Insurance Number</td>
                                <td>{{ $bus->insurance_number ?? 'N/A' }}</td>
                                <td class="fw-semibold">Insurance Company</td>
                                <td>{{ $bus->insurance_company ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Insurance Expiry</td>
                                <td>
                                    @if($bus->insurance_expiry)
                                    <span class="{{ $bus->isInsuranceExpired() ? 'text-danger' : 'text-success' }}">
                                        {{ $bus->insurance_expiry->format('M d, Y') }}
                                        @if($bus->isInsuranceExpired())
                                        <i class="ti ti-alert-triangle ms-1"></i>
                                        @endif
                                    </span>
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td class="fw-semibold">Fitness Certificate Number</td>
                                <td>{{ $bus->fitness_certificate_number ?? 'N/A' }}</td>
                       
                                <td class="fw-semibold">Fitness Expiry</td>
                                <td>{{ $bus->fitness_expiry?->format('M d, Y') ?? 'N/A' }}</td>
                              
                            </tr>
                            <tr>
                                <td class="fw-semibold">Permit Number</td>
                                <td>{{ $bus->permit_number ?? 'N/A' }}</td>
                                <td class="fw-semibold">Permit Expiry</td>
                                <td>{{ $bus->permit_expiry?->format('M d, Y') ?? 'N/A' }}</td>
                                <td class="fw-semibold">Purchase Date</td>
                                <td>{{ $bus->purchase_date?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Assigned Route</td>
                                <td>{{ $bus->assigned_route ?? 'N/A' }}</td>
                                <td class="fw-semibold">Current Mileage</td>
                                <td>{{ $bus->current_mileage ? number_format($bus->current_mileage) . ' km' : 'N/A' }}</td>
                        
                                <td class="fw-semibold">Engine Capacity</td>
                                <td>{{ $bus->engine_capacity ? $bus->engine_capacity . ' CC' : 'N/A' }}</td>
                              
                            </tr>
                            <tr>
                                <td class="fw-semibold">Transmission Type</td>
                                <td>{{ $bus->transmission_type ? ucfirst($bus->transmission_type) : 'N/A' }}</td>
                                <td class="fw-semibold">Seating Capacity</td>
                                <td>{{ $bus->seating_capacity ?? 'N/A' }}</td>
                                <td class="fw-semibold">Gross Weight</td>
                                <td>{{ $bus->gross_weight ? $bus->gross_weight . ' kg' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Bus Length</td>
                                <td>{{ $bus->bus_length ? $bus->bus_length . ' m' : 'N/A' }}</td>
                                <td class="fw-semibold">Bus Width</td>
                                <td>{{ $bus->bus_width ? $bus->bus_width . ' m' : 'N/A' }}</td>
                          
                                <td class="fw-semibold">Bus Height</td>
                                <td>{{ $bus->bus_height ? $bus->bus_height . ' m' : 'N/A' }}</td>
                              
                            </tr>
                            <tr>
                                <td class="fw-semibold">Status</td>
                                <td>
                                    @php
                                    $statusClass = match($bus->status->status_name) {
                                    'Active' => 'success',
                                    'Inactive' => 'secondary',
                                    'Under Maintenance' => 'warning',
                                    default => 'secondary'
                                    };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $bus->status->status_name)) }}
                                    </span>
                                </td>
                                <td class="fw-semibold">Last Service Date</td>
                                <td>{{ $bus->last_service_date?->format('M d, Y') ?? 'N/A' }}</td>
                                <td class="fw-semibold">Next Service Due</td>
                                <td>
                                    @if($bus->next_service_due)
                                    <span class="{{ $bus->isServiceDue() ? 'text-danger' : 'text-success' }}">
                                        {{ $bus->next_service_due->format('M d, Y') }}
                                        @if($bus->isServiceDue())
                                        <i class="ti ti-alert-triangle ms-1"></i>
                                        @endif
                                    </span>
                                    @else
                                    N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Owner Information</td>
                                <td>{{ $bus->supplier->supplier_name ?? 'N/A' }}</td>
                                <td>Driver Information</td>
                                <td>{{ $bus->driver->full_name ?? 'N/A' }}</td>
                                <td>Helper Information</td>
                                <td>{{ $bus->busHelper->bus_helper_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Technical Specifications</td>
                                <td>{{ $bus->engine_capacity ? $bus->engine_capacity . ' CC' : 'N/A' }}</td>
                                <td>Transmission Type</td>
                                <td>{{ $bus->transmission_type ? ucfirst($bus->transmission_type) : 'N/A' }}</td>
                                <td>Seating Capacity</td>
                                <td>{{ $bus->seating_capacity ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Operational Details</td>
                                <td>{{ $bus->purchase_date?->format('M d, Y') ?? 'N/A' }}</td>
                                <td>Assigned Route</td>
                                <td>{{ $bus->assigned_route ?? 'N/A' }}</td>
                                <td>Status</td>
                                <td>{{ $bus->status->status_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Current Mileage</td>
                                <td>{{ $bus->current_mileage ? number_format($bus->current_mileage) . ' km' : 'N/A' }}</td>
                                <td>Last Service Date</td>
                                <td>{{ $bus->last_service_date?->format('M d, Y') ?? 'N/A' }}</td>
                                <td>Next Service Due</td>
                                <td>{{ $bus->next_service_due?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Documents</td>
                                <td>{{ $bus->registration_document ? 'Yes' : 'No' }}</td>
                                <td>Insurance Document</td>
                                <td>{{ $bus->insurance_document ? 'Yes' : 'No' }}</td>
                                <td>Fitness Certificate</td>
                                <td>{{ $bus->fitness_certificate ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <td>Attachments</td>
                                <td>{{ $bus->bus_photo ? 'Yes' : 'No' }}</td>
                                <td>Registration Document</td>
                                <td>{{ $bus->registration_document ? 'Yes' : 'No' }}</td>
                                <td>Insurance Document</td>
                                <td>{{ $bus->insurance_document ? 'Yes' : 'No' }}</td>
                            </tr>
                         
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.getElementById('printBusDetailsBtn');
    const printArea = document.getElementById('busDetailsPrintArea');

    if (!printBtn || !printArea) {
        return;
    }

    printBtn.addEventListener('click', function() {
        const printWindow = window.open('', '_blank', 'width=1100,height=750');
        if (!printWindow) {
            alert('Please allow popups to open print preview.');
            return;
        }

        const previewHtml = `
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bus Details Print Preview</title>
    <style>
        @@page { size: auto; margin: 12mm; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            color: #000;
            background: #fff;
        }
        .print-wrapper {
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        td, th {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }
        img {
            max-height: 110px;
        }
    </style>
</head>
<body>
    <div class="print-wrapper">${printArea.innerHTML}</div>
</body>
</html>`;

        printWindow.document.open();
        printWindow.document.write(previewHtml);
        printWindow.document.close();
        printWindow.focus();

        setTimeout(function() {
            printWindow.print();
        }, 300);
    });
});
</script>
@endsection
