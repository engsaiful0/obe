@extends('layouts/layoutMaster')

@section('title', 'Driver & Helper Assignment')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Driver & Helper Assignment Management</h5>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">Total: {{ $assignments->total() }}</span>
            <a href="{{ route('driver-helper-assignments.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>Add Assignment
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <form method="GET" action="{{ route('driver-helper-assignments.index') }}" id="filterForm" class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by bus, driver, or helper..." value="{{ request('search') }}">
                    </div>
                    
                    <!-- Bus Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Bus</label>
                        <select name="bus_id" id="bus_id" class="form-select">
                            <option value="">All Buses</option>
                            @foreach($buses as $bus)
                                <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                                    {{ $bus->bus_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Driver Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" id="driver_id" class="form-select">
                            <option value="">All Drivers</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Bus Helper Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Bus Helper</label>
                        <select name="bus_helper_id" id="bus_helper_id" class="form-select">
                            <option value="">All Helpers</option>
                            @foreach($busHelpers as $helper)
                                <option value="{{ $helper->id }}" {{ request('bus_helper_id') == $helper->id ? 'selected' : '' }}>
                                    {{ $helper->bus_helper_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status_id" id="status_id" class="form-select">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->status_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Filter Buttons -->
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100">
                            <i class="ti ti-refresh me-1"></i>Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="row mb-3">
            <div class="col-12">
                <p class="text-muted mb-0">
                    Showing {{ $assignments->firstItem() ?? 0 }} to {{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }} results
                </p>
            </div>
        </div>

        <!-- Assignments Table Container -->
        <div id="tableContainer">
            @include('content.driver-helper-assignments.partials.table', ['assignments' => $assignments])
        </div>

        <!-- Pagination Container -->
        <div id="paginationContainer" class="mt-4">
            {{ $assignments->links() }}
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Filter changes - trigger on change
    $('#bus_id, #driver_id, #bus_helper_id, #status_id').on('change', function() {
        $('#filterForm').submit();
    });

    // Search with debounce
    let searchTimeout = null;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterForm').submit();
        }, 500);
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#filterForm')[0].reset();
        $('#filterForm').submit();
    });

    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            window.location.href = url;
        }
    });
});
</script>
@endsection

