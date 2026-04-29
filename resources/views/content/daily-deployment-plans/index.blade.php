@extends('layouts/layoutMaster')

@section('title', 'Daily Deployment Plans')

@section('page-script')
<script>
    window.deploymentPlanIndexUrl = '{{ route("deployment-plans.view-daily-deployment-plan") }}';
</script>
<script src="{{ asset('assets/js/deployment-plan-ajax.js') }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Daily Deployment Plans</h4>
                <div class="d-flex gap-2">
                    @permission('daily-deployment-plan-add')
                    <a href="{{ route('deployment-plans.create-daily-deployment-plan') }}" class="btn btn-primary">
                        <i data-feather="plus"></i> Add New Plan
                    </a>
                    @endpermission
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ti ti-filter me-2"></i>Filters
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" id="filterDateFrom" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" id="filterDateTo" name="date_to" value="{{ request('date_to') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Trip Time</label>
                                <select class="form-select" id="filterTripTime" name="trip_time_id">
                                    <option value="">All Trip Times</option>
                                    @foreach($tripTimes as $tripTime)
                                    <option value="{{ $tripTime->id }}" {{ request('trip_time_id') == $tripTime->id ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($tripTime->time_value)->format('h:i') }} {{ $tripTime->time_period }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Bus User</label>
                                <select class="form-select" id="filterBusUser" name="bus_user_id">
                                    <option value="">All Bus Users</option>
                                    @foreach($busUsers as $busUser)
                                    <option value="{{ $busUser->id }}" {{ request('bus_user_id') == $busUser->id ? 'selected' : '' }}>
                                        {{ $busUser->bus_user_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Deployment Type</label>
                                <select class="form-select" id="deployment_type_id" name="deployment_type_id">
                                    <option value="">All Deployment Types</option>
                                    @foreach($deploymentTypes as $deploymentType)
                                    <option value="{{ $deploymentType->id }}" {{ request('deployment_type_id') == $deploymentType->id ? 'selected' : '' }}>
                                        {{ $deploymentType->deployment_type_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Trip Type</label>
                                <select class="form-select" id="trip_type" name="trip_type">
                                    <option value="">All Trip Types</option>
                                    <option value="arrival" {{ request('trip_type') == 'arrival' ? 'selected' : '' }}>Arrival</option>
                                    <option value="departure" {{ request('trip_type') == 'departure' ? 'selected' : '' }}>Departure</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                                        <i class="ti ti-search me-1"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="clearFiltersBtn">
                                        <i class="ti ti-x me-1"></i> Clear All
                                    </button>
                                    <small class="text-muted ms-2">
                                        <i class="ti ti-info-circle me-1"></i>Filters apply automatically when changed
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="text-center d-none mb-4" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                    <div>
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading deployment plans...</p>
                    </div>
                </div>

                <!-- Data Table Container -->
                <div id="dataTableContainer">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Trip Time</th>
                                    <th>Deployment Type</th>
                                    <th>Trip Type</th>
                                    <th>Bus User</th>
                                    <th>Stoppages</th>
                                    <th>Created By</th>
                                    @permission('daily-deployment-plan-view')
                                    <th>View</th>
                                    @endpermission
                                    @permission('daily-deployment-plan-edit')
                                    <th>Edit</th>
                                    @endpermission
                                    @permission('daily-deployment-plan-delete')
                                    <th>Delete</th>
                                    @endpermission
                                </tr>
                            </thead>
                            <tbody id="dataTableBody">
                                @include('content.daily-deployment-plans.partials.table', ['plans' => $plans])
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="paginationContainer" class="mt-3">
                        {{ $plans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection