@extends('layouts/layoutMaster')

@section('title', 'View Reward')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Reward Record Details</h5>
                <div>
                    <a href="{{ route('rewards.edit', $reward) }}" class="btn btn-sm btn-warning">
                        <i class="ti ti-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('rewards.index') }}" class="btn btn-sm btn-secondary">
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
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Bus</label>
                            <div class="card">
                                <div class="card-body">
                                    <h6>{{ $reward->bus->bus_number ?? 'N/A' }}</h6>
                                    <p class="text-muted mb-0">
                                        {{ $reward->bus->model_name ?? '' }} 
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reward Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="ti ti-gift me-2"></i>Reward Details
                        </h6>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Reward Date</label>
                        <p>{{ $reward->reward_date->format('F d, Y') }}</p>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Reward Amount</label>
                        <p class="h4 text-success">৳{{ number_format($reward->reward_amount, 2) }}</p>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Reward Type</label>
                        <div>
                            @if($reward->reward_type)
                                <span class="badge bg-info">
                                    {{ ucwords(str_replace('_', ' ', $reward->reward_type)) }}
                                </span>
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status</label>
                        <div>
                            @php
                                $statusClass = match($reward->status) {
                                    'pending' => 'warning',
                                    'approved' => 'info',
                                    'paid' => 'success',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst($reward->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Reason</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $reward->reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($reward->remarks)
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Remarks</label>
                        <div class="card">
                            <div class="card-body">
                                <p class="mb-0">{{ $reward->remarks }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Payment Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="ti ti-wallet me-2"></i>Payment Information
                        </h6>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    @if($reward->approvedBy)
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Approved By</label>
                        <p>{{ $reward->approvedBy->name }}</p>
                    </div>
                    @endif
                    
                    @if($reward->paid_date)
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Paid Date</label>
                        <p>{{ $reward->paid_date->format('F d, Y') }}</p>
                    </div>
                    @endif
                </div>
                
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
                        <p>{{ $reward->user->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Created At</label>
                        <p>{{ $reward->created_at->format('F d, Y h:i A') }}</p>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Last Updated</label>
                        <p>{{ $reward->updated_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

