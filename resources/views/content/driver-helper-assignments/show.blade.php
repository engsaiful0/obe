@extends('layouts/layoutMaster')

@section('title', 'View Driver & Helper Assignment')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Assignment Details</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('driver-helper-assignments.edit', $driverHelperAssignment) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('driver-helper-assignments.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Bus Information -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-semibold mb-3">Bus Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Model Name:</th>
                                <td>{{ $driverHelperAssignment->bus->model_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Registration Number:</th>
                                <td>{{ $driverHelperAssignment->bus->registration_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Bus Number:</th>
                                <td>{{ $driverHelperAssignment->bus->bus_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Bus Type:</th>
                                <td>{{ $driverHelperAssignment->bus->busType->bus_type_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Bus Sub Type:</th>
                                <td>{{ $driverHelperAssignment->bus->busSubType->sub_type_name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Driver Information -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-semibold mb-3">Driver Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Full Name:</th>
                                <td>{{ $driverHelperAssignment->driver->full_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Driver ID:</th>
                                <td>{{ $driverHelperAssignment->driver->driver_unique_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Contact Number:</th>
                                <td>{{ $driverHelperAssignment->driver->contact_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>License Number:</th>
                                <td>{{ $driverHelperAssignment->driver->license_number ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Bus Helper Information -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-semibold mb-3">Bus Helper Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Name:</th>
                                <td>{{ $driverHelperAssignment->busHelper->bus_helper_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Helper ID:</th>
                                <td>{{ $driverHelperAssignment->busHelper->bus_helper_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Mobile:</th>
                                <td>{{ $driverHelperAssignment->busHelper->mobile ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Assignment Details -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-semibold mb-3">Assignment Details</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Assignment Date:</th>
                                <td>{{ $driverHelperAssignment->assignment_date ? $driverHelperAssignment->assignment_date->format('Y-m-d') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($driverHelperAssignment->status)
                                        <span class="badge bg-label-primary">{{ $driverHelperAssignment->status->status_name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ $driverHelperAssignment->created_at ? $driverHelperAssignment->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Updated At:</th>
                                <td>{{ $driverHelperAssignment->updated_at ? $driverHelperAssignment->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Notes -->
                    @if($driverHelperAssignment->notes)
                    <div class="col-12 mb-4">
                        <h6 class="fw-semibold mb-3">Notes</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $driverHelperAssignment->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

