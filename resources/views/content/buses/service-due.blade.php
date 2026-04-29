@extends('layouts/layoutMaster')

@section('title', 'Buses Due for Service')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Buses Due for Service</h5>
        <a href="{{ route('buses.index') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Buses
        </a>
    </div>
    <div class="card-body">
        @if($busesDueForService->count() > 0)
            <div class="alert alert-info">
                <i class="ti ti-tools me-2"></i>
                <strong>Service Alert:</strong> {{ $busesDueForService->count() }} bus(es) are due for service.
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Bus</th>
                            <th>Registration</th>
                            <th>Owner</th>
                            <th>Last Service</th>
                            <th>Next Service Due</th>
                            <th>Current Mileage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($busesDueForService as $index => $bus)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($bus->bus_photo)
                                            <img src="{{ asset('storage/app/public/' . $bus->bus_photo) }}" 
                                                 alt="{{ $bus->display_name }}" 
                                                 class="rounded me-3" 
                                                 width="40" 
                                                 height="30"
                                                 style="object-fit: cover;">
                                        @else
                                            <div class="avatar avatar-sm bg-secondary rounded me-3 d-flex align-items-center justify-content-center">
                                                <i class="ti ti-car text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $bus->display_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $bus->busType->bus_type_name ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $bus->registration_number }}</strong>
                                </td>
                                <td>
                                    @if($bus->supplier)
                                        <div>
                                            <strong>{{ $bus->supplier->supplier_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $bus->supplier->mobile }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">No Owner</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bus->last_service_date)
                                        <span class="text-muted">{{ $bus->last_service_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-muted">Never serviced</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bus->next_service_due)
                                        <span class="text-danger fw-bold">
                                            {{ $bus->next_service_due->format('M d, Y') }}
                                            <i class="ti ti-alert-triangle ms-1"></i>
                                        </span>
                                    @else
                                        <span class="text-muted">Not scheduled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bus->current_mileage)
                                        <span class="text-muted">{{ number_format($bus->current_mileage) }} km</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('buses.show', $bus) }}">
                                                <i class="ti ti-eye me-2"></i>View Details
                                            </a></li>
                                            <li><a class="dropdown-item" href="{{ route('buses.edit', $bus) }}">
                                                <i class="ti ti-edit me-2"></i>Update Service Info
                                            </a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="ti ti-check-circle text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-success">All Caught Up!</h5>
                <p class="text-muted">No buses are due for service at this time.</p>
                <a href="{{ route('buses.index') }}" class="btn btn-primary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Buses
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

