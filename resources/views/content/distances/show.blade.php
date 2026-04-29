@extends('layouts/layoutMaster')

@section('title', 'Distance Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Distance Details</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('distances.edit', $distance) }}" class="btn btn-primary">
                <i class="ti ti-edit me-1"></i>Edit
            </a>
            <a href="{{ route('distances.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-route me-2"></i>Distance Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium">Route Name</label>
                                <p class="mb-0">{{ $distance->route_name }}</p>
                            </div>
                            
                            @if($distance->distance_name)
                            <div class="col-12">
                                <label class="form-label fw-medium">Distance Name</label>
                                <p class="mb-0">{{ $distance->distance_name }}</p>
                            </div>
                            @endif
                            
                            <div class="col-12">
                                <label class="form-label fw-medium">Distance</label>
                                <p class="mb-0">
                                    <span class="badge bg-primary fs-6">{{ $distance->formatted_distance }}</span>
                                </p>
                            </div>
                            
                            
                            @if($distance->description)
                            <div class="col-12">
                                <label class="form-label fw-medium">Description</label>
                                <p class="mb-0">{{ $distance->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Route Details -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-map-pin me-2"></i>Route Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium">Start Stoppage</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-success rounded-circle me-2">
                                        <i class="ti ti-map-pin text-white"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-medium">{{ $distance->startStoppage->stoppage_name }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-center my-2">
                                    <div class="d-flex align-items-center">
                                        <div class="border-top border-2 border-primary" style="width: 30px;"></div>
                                        <i class="ti ti-arrow-right text-primary mx-2"></i>
                                        <div class="border-top border-2 border-primary" style="width: 30px;"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-medium">End Stoppage</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-danger rounded-circle me-2">
                                        <i class="ti ti-map-pin text-white"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-medium">{{ $distance->endStoppage->stoppage_name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Record Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-info-circle me-2"></i>Record Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Created By</label>
                                <p class="mb-0">{{ $distance->user->name }}</p>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Created At</label>
                                <p class="mb-0">{{ $distance->created_at->format('M d, Y H:i A') }}</p>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Last Updated</label>
                                <p class="mb-0">{{ $distance->updated_at->format('M d, Y H:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
