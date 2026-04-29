@extends('layouts/layoutMaster')

@section('title', 'View Punishment')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Punishment Record Details</h5>
                <div>
                    <a href="{{ route('punishments.edit', $punishment) }}" class="btn btn-sm btn-warning">
                        <i class="ti ti-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('punishments.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="ti ti-info-circle me-2"></i>Basic Information
                        </h6>
                    </div>
                </div>
                
                <!-- Bus Information -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Bus Information</label>
                            <div class="card">
                                <div class="card-body">
                                    @if($punishment->bus)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-primary">{{ $punishment->bus->bus_number ?? 'N/A' }}</h6>
                                                <p class="text-muted mb-1">
                                                    <strong>Model:</strong> {{ $punishment->bus->model_name ?? 'N/A' }}
                                                    @if($punishment->bus->brand)
                                                        - {{ $punishment->bus->brand->brand_name }}
                                                    @endif
                                                </p>
                                                @if($punishment->bus->busType)
                                                    <p class="text-muted mb-1">
                                                        <strong>Type:</strong> {{ $punishment->bus->busType->bus_type_name ?? 'N/A' }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($punishment->bus->supplier)
                                                    <p class="text-muted mb-1">
                                                        <strong>Bus Owner:</strong> {{ $punishment->bus->supplier->supplier_name ?? 'N/A' }}
                                                    </p>
                                                    <p class="text-muted mb-0">
                                                        <strong>Owner Contact:</strong> {{ $punishment->bus->supplier->contact_number ?? 'N/A' }}
                                                    </p>
                                                @else
                                                    <p class="text-muted mb-0">No bus owner information available</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No bus assigned to this punishment</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver Information -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Driver Information</label>
                            <div class="card">
                                <div class="card-body">
                                    @if($punishment->driver)
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6 class="text-primary">{{ $punishment->driver->full_name ?? 'N/A' }}</h6>
                                                <p class="text-muted mb-1">
                                                    <strong>Driver ID:</strong> {{ $punishment->driver->driver_unique_id ?? 'N/A' }}
                                                </p>
                                                <p class="text-muted mb-1">
                                                    <strong>License:</strong> {{ $punishment->driver->license_number ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="text-muted mb-1">
                                                    <strong>Contact:</strong> {{ $punishment->driver->contact_number ?? 'N/A' }}
                                                </p>
                                                <p class="text-muted mb-1">
                                                    <strong>Alternative:</strong> {{ $punishment->driver->alternative_contact_number ?? 'N/A' }}
                                                </p>
                                                <p class="text-muted mb-0">
                                                    <strong>Experience:</strong> {{ $punishment->driver->driving_experience ?? 'N/A' }} years
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="text-muted mb-1">
                                                    <strong>License Expiry:</strong> 
                                                    @if($punishment->driver->license_expiry_date)
                                                        {{ \Carbon\Carbon::parse($punishment->driver->license_expiry_date)->format('M d, Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </p>
                                                <p class="text-muted mb-0">
                                                    <strong>Address:</strong> {{ $punishment->driver->present_address ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No driver assigned to this punishment</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Helper Information -->
                @if($punishment->busHelper)
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Helper Information</label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3">
                                                <h6 class="text-primary">{{ $punishment->busHelper->bus_helper_name ?? 'N/A' }}</h6>
                                                <p class="text-muted mb-1">
                                                    <strong>Helper ID:</strong> {{ $punishment->busHelper->bus_helper_id ?? 'N/A' }}
                                                </p>
                                                <p class="text-muted mb-1">
                                                    <strong>Contact:</strong> {{ $punishment->busHelper->mobile ?? 'N/A' }}
                                                </p>
                                                <p class="text-muted mb-1">
                                                    <strong>Experience:</strong> {{ $punishment->busHelper->years_of_experience ?? 'N/A' }} years
                                                </p>
                                                <p class="text-muted mb-0">
                                                    <strong>Salary:</strong> ৳{{ number_format($punishment->busHelper->gross_salary ?? 0, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Punishment Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="ti ti-alert-triangle me-2"></i>Punishment Details
                        </h6>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Punishment Date</label>
                        <p>{{ $punishment->punishment_date->format('F d, Y') }}</p>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Punishment Type</label>
                        <div>
                            @php
                                $typeClass = match($punishment->punishmentType->name) {
                                    'warning' => 'warning',
                                    'fine' => 'info',
                                    'suspension' => 'danger',
                                    'termination' => 'dark',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $typeClass }}">
                                {{ ucfirst($punishment->punishmentType->name) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Violation Type</label>
                        <p>{{ ucwords(str_replace('_', ' ', $punishment->violationType->name)) }}</p>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    @if($punishment->fine_amount)
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fine Amount</label>
                        <p class="h5 text-danger">৳{{ number_format($punishment->fine_amount, 2) }}</p>
                    </div>
                    @endif
                    
                    @if($punishment->suspension_days)
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Suspension Days</label>
                        <p class="h5 text-warning">{{ $punishment->suspension_days }} days</p>
                    </div>
                    @endif
                    
                    @if($punishment->witnessEmployee)
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Witness Employee</label>
                        <p>{{ $punishment->witnessEmployee->name ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Description</label>
                        <div class="card">
                            <div class="card-body">
                                <p class="mb-0">{{ $punishment->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($punishment->remarks)
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Remarks</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $punishment->remarks }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Document -->
                @if($punishment->document_path)
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="ti ti-paperclip me-2"></i>Supporting Document
                        </h6>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ asset('storage/app/public/' . $punishment->document_path) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="ti ti-file-text me-1"></i>View Document
                        </a>
                    </div>
                </div>
                @endif
                
                <!-- Record Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="ti ti-clock me-2"></i>Record Information
                        </h6>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Created By</label>
                        <p>{{ $punishment->user->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Created At</label>
                        <p>{{ $punishment->created_at->format('F d, Y h:i A') }}</p>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Last Updated</label>
                        <p>{{ $punishment->updated_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

