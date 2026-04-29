@extends('layouts/layoutMaster')

@section('title', 'Bus Requisition Management')

@section('page-script')
<script>
    window.busRequisitionUrls = {
        destroy: '{{ url('app/bus-requisitions') }}/',
        updateStatus: '{{ route('app-bus-requisitions.update-status', ':id') }}'
    };
</script>
<script src="{{ asset('assets/js/bus-requisition-index.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Filter Bus Requisitions</h5>
            <div>
                <a href="{{ route('app-bus-requisitions.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Add New Requisition
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('app-bus-requisitions') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Purpose, Sender, Mobile, Email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="department_id">Department</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="date_from">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="date_to">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="required_bus_date_from">Required Bus Date From</label>
                        <input type="date" class="form-control" id="required_bus_date_from" name="required_bus_date_from" 
                               value="{{ request('required_bus_date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="required_bus_date_to">Required Bus Date To</label>
                        <input type="date" class="form-control" id="required_bus_date_to" name="required_bus_date_to" 
                               value="{{ request('required_bus_date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" id="apply-filters-btn">
                            <i class="ti ti-filter me-1"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="clear-filters-btn">
                            <i class="ti ti-x me-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bus Requisitions Table Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Bus Requisitions</h5>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="bus-requisitions-spinner" class="text-center d-none py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <!-- Table Container -->
            <div id="bus-requisitions-table-container">
                @include('content.bus-requisitions.partials.table', ['busRequisitions' => $busRequisitions])
            </div>
        </div>
    </div>
</div>
@endsection


