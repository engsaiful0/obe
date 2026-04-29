@extends('layouts/layoutMaster')

@section('title', 'Bus Schedules')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/css/bus-schedule-form.css') }}">
@endsection

@section('page-script')
<script>
    // Configure toastr before any scripts run
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }
    
    window.busScheduleUrls = {
        index: '{{ route('bus-schedules.schedule-index') }}',
        create: '{{ route('bus-schedules.create-schedule') }}',
        edit: '{{ url('app/bus-schedules/schedule/:id/edit') }}',
        view: '{{ url('app/bus-schedules/schedule/:id/view') }}',
        update: '{{ url('app/bus-schedules/schedule/:id') }}',
        destroy: '{{ url('app/bus-schedules/schedule/:id') }}'
    };
</script>
<script src="{{ asset('assets/js/bus-schedule-index.js') }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Bus Schedules</h4>
                <div>
                    <a href="{{ route('bus-schedules.create-schedule') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Add Schedule
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Keyword</label>
                                <select class="form-select filter-select" name="keyword_id" id="keyword_id">
                                    <option value="">All</option>
                                    @foreach($keywords as $keyword)
                                        <option value="{{ $keyword->id }}" {{ request('keyword_id') == $keyword->id ? 'selected' : '' }}>
                                            {{ $keyword->keyword_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select filter-select" name="status_id" id="status_id">
                                    <option value="">All</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                            {{ $status->status_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bus User</label>
                                <select class="form-select filter-select" name="bus_user_id" id="bus_user_id">
                                    <option value="">All</option>
                                    @foreach($busUsers as $user)
                                        <option value="{{ $user->id }}" {{ request('bus_user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->bus_user_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Effective From</label>
                                <input type="date" class="form-control filter-input" name="effective_from" id="effective_from" value="{{ request('effective_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control filter-input" name="search" id="search" placeholder="Search..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary" id="applyFilters">
                                        <span class="spinner-border spinner-border-sm d-none me-2" id="filterSpinner"></span>
                                        <i class="ti ti-search me-1" id="filterIcon"></i> 
                                        <span id="filterText">Filter</span>
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="clearFilters">
                                        <i class="ti ti-x me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Schedule Table -->
                <div id="scheduleTableContainer">
                    @include('content.bus-schedule.partials.schedule-table', ['schedules' => $schedules])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

