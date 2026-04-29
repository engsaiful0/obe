@extends('layouts/layoutMaster')

@section('title', 'Trip Details')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Trip Details</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('bus-trips.edit', $busTrip) }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-edit me-1"></i>Edit
                </a>
                <a href="{{ route('bus-trips.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Bus & Staff Information -->
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="ti ti-bus me-2"></i>Bus & Trip Information
                </h6>
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="ti ti-bus me-2"></i>Bus Information
                        </h6>
                        <div class="mb-2">
                            <strong>Registration:</strong> {{ $busTrip->bus->registration_number }}
                        </div>
                        <div class="mb-2">
                            <strong>Model:</strong> {{ $busTrip->bus->model_name }}
                        </div>
                        <div class="mb-2">
                            <strong>Type:</strong> 
                            <span class="badge bg-info">
                                {{ $busTrip->bus->busSubType->sub_type_name ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">
                            <i class="ti ti-user me-2"></i>Driver Details
                        </h6>
                        @if($busTrip->driver)
                            <div class="mb-2">
                                <strong>Name:</strong> {{ $busTrip->driver->full_name }}
                            </div>
                            <div class="mb-2">
                                <strong>ID:</strong> {{ $busTrip->driver->driver_unique_id ?? 'N/A' }}
                            </div>
                            <div class="mb-2">
                                <strong>Contact:</strong> {{ $busTrip->driver->contact_number ?? 'N/A' }}
                            </div>
                            <div class="mb-2">
                                <strong>License:</strong> {{ $busTrip->driver->license_number ?? 'N/A' }}
                            </div>
                        @else
                            <div class="text-muted">
                                <i class="ti ti-user-x me-1"></i>No driver assigned
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title text-warning mb-3">
                            <i class="ti ti-user-plus me-2"></i>Helper Details
                        </h6>
                        @if($busTrip->busHelper)
                            <div class="mb-2">
                                <strong>Name:</strong> {{ $busTrip->busHelper->bus_helper_name }}
                            </div>
                            <div class="mb-2">
                                <strong>ID:</strong> {{ $busTrip->busHelper->bus_helper_id ?? 'N/A' }}
                            </div>
                            <div class="mb-2">
                                <strong>Contact:</strong> {{ $busTrip->busHelper->mobile ?? 'N/A' }}
                            </div>
                            <div class="mb-2">
                                <strong>Experience:</strong> {{ $busTrip->busHelper->years_of_experience ?? 0 }} years
                            </div>
                        @else
                            <div class="text-muted">
                                <i class="ti ti-user-x me-1"></i>No helper assigned
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trip Information -->
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="ti ti-route me-2"></i>Trip Information
                </h6>
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title text-info mb-3">
                            <i class="ti ti-calendar me-2"></i>Date & Type
                        </h6>
                        <div class="mb-2">
                            <strong>Date:</strong> {{ $busTrip->trip_date->format('d M Y') }}
                        </div>
                        <div class="mb-2">
                            <strong>Trip Type:</strong> 
                            <span class="badge {{ $busTrip->trip_type == 'in' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($busTrip->trip_type) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="ti ti-map-pin me-2"></i>Start Point
                        </h6>
                        <div class="mb-2">
                            <strong>Stoppage:</strong> {{ $busTrip->startStoppage->stoppage_name }}
                        </div>
                        @if($busTrip->in_time)
                            <div class="mb-2">
                                <strong>In Time:</strong> {{ $busTrip->in_time->format('H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">
                            <i class="ti ti-map-pin me-2"></i>End Point
                        </h6>
                        <div class="mb-2">
                            <strong>Stoppage:</strong> {{ $busTrip->endStoppage->stoppage_name }}
                        </div>
                        @if($busTrip->out_time)
                            <div class="mb-2">
                                <strong>Out Time:</strong> {{ $busTrip->out_time->format('H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="card-title text-warning mb-3">
                            <i class="ti ti-route me-2"></i>Distance
                        </h6>
                        @if($busTrip->total_distance)
                            <div class="mb-2">
                                <strong>Distance:</strong> {{ $busTrip->total_distance }} KM
                            </div>
                        @else
                            <div class="text-muted">
                                <i class="ti ti-minus me-1"></i>No distance recorded
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Information -->
        @if($busTrip->remarks)
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="ti ti-note me-2"></i>Remarks
                </h6>
                <div class="card border">
                    <div class="card-body">
                        <p class="mb-0">{{ $busTrip->remarks }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Record Information -->
        <div class="row">
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="ti ti-info-circle me-2"></i>Record Information
                </h6>
                <div class="card border">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Recorded By:</strong> {{ $busTrip->user->name ?? 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Created:</strong> {{ $busTrip->created_at->format('d M Y H:i') }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Last Updated:</strong> {{ $busTrip->updated_at->format('d M Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
