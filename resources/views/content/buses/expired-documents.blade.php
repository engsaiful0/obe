@extends('layouts/layoutMaster')

@section('title', 'Buses with Expired Documents')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Buses with Expired Documents</h5>
        <a href="{{ route('buses.index') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Buses
        </a>
    </div>
    <div class="card-body">
        @if($busesWithExpiredDocs->count() > 0)
            <div class="alert alert-warning">
                <i class="ti ti-alert-triangle me-2"></i>
                <strong>Warning:</strong> {{ $busesWithExpiredDocs->count() }} bus(es) have expired documents that require immediate attention.
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Bus</th>
                            <th>Registration</th>
                            <th>Owner</th>
                            <th>Expired Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($busesWithExpiredDocs as $index => $bus)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($bus->bus_photo)
                                            <img src="{{ asset('storage/' . $bus->bus_photo) }}" 
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
                                    @if($bus->registration_expiry)
                                        <br>
                                        <small class="text-muted">
                                            Expires: {{ $bus->registration_expiry->format('M d, Y') }}
                                        </small>
                                    @endif
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
                                    @php
                                        $expiredDocs = $bus->getExpiredDocuments();
                                    @endphp
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($expiredDocs as $doc)
                                            <span class="badge bg-danger">{{ $doc }}</span>
                                        @endforeach
                                    </div>
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
                                                <i class="ti ti-edit me-2"></i>Update Documents
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
                <h5 class="mt-3 text-success">All Good!</h5>
                <p class="text-muted">No buses have expired documents.</p>
                <a href="{{ route('buses.index') }}" class="btn btn-primary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Buses
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

