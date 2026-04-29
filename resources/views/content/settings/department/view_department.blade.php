@extends('layouts/layoutMaster')

@section('title', 'View Department')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Department Details</h5>
            <div>
                <a href="{{ route('app-settings-department.edit', $department->id) }}" class="btn btn-primary me-2">
                    <i class="ti ti-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('app-settings-department') }}" class="btn btn-label-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Department Name</label>
                    <p class="mb-0 fw-semibold">{{ $department->name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Created By</label>
                    <p class="mb-0 fw-semibold">
                        @if($department->user)
                            {{ $department->user->name ?? 'N/A' }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted">Description</label>
                    <p class="mb-0 fw-semibold">
                        @if($department->description)
                            {{ $department->description }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Created At</label>
                    <p class="mb-0 fw-semibold">
                        {{ $department->created_at ? \Carbon\Carbon::parse($department->created_at)->format('M d, Y h:i A') : 'N/A' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Updated At</label>
                    <p class="mb-0 fw-semibold">
                        {{ $department->updated_at ? \Carbon\Carbon::parse($department->updated_at)->format('M d, Y h:i A') : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

