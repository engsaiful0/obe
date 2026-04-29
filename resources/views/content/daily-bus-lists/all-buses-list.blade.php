@extends('layouts/layoutMaster')

@section('title', 'All Buses List')

@section('page-script')

<script>
    window.allBusesListUrls = {
        getFilteredData: '{{ route("daily-bus-lists.get-filtered-data") }}',
        exportPdf: '{{ route("daily-bus-lists.export-pdf") }}',
        exportExcel: '{{ route("daily-bus-lists.export-excel") }}'
    };

    // Configure toastr
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
</script>
<script src="{{ asset('assets/js/all-buses-list-ajax.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Buses List</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" id="exportPdfBtn">
                        <i data-feather="download"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-info" id="exportExcelBtn">
                        <i data-feather="file-text"></i> Export Excel
                    </button>

                </div>
            </div>
            <div class="card-body">
                <!-- Alert Messages -->
                <div id="alertContainer" class="mb-3"></div>

                <!-- Filters -->
                <div class="row mb-3">

                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="filterDateFrom" name="date_from">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="filterDateTo" name="date_to">
                    </div>


                    <div class="col-md-2">
                        <label class="form-label">Sub Type</label>
                        <select class="form-select select2" id="filterVehicleSubType" name="vehicle_sub_type_id">
                            <option value="">All Sub Types</option>
                            @foreach ($vehicleSubTypes as $subType)
                            <option value="{{ $subType->id }}">{{ $subType->sub_type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Vehicle</label>
                        <select class="form-select select2" id="filterVehicle" name="vehicle_id">
                            <option value="">All Vehicles</option>
                            @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->model_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" id="filterSearch" name="search" placeholder="Search...">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" id="applyFiltersBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="filterSpinner" role="status"></span>
                            <span id="filterText">
                                <i data-feather="search"></i> Apply Filters
                            </span>
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearFiltersBtn">
                            <i data-feather="x"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Results Summary -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info" id="resultsSummary">
                            <strong>Total Records:</strong> {{ $dailyBusLists->total() }} |
                            <strong>Showing:</strong> {{ $dailyBusLists->firstItem() ?? 0 }} to {{ $dailyBusLists->lastItem() ?? 0 }} of {{ $dailyBusLists->total() }}
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="d-none">
                    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive" id="dataTableContainer">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Vehicle</th>
                                <th>Sub Type</th>
                                <th>Start Stoppage</th>
                                <th>End Stoppage</th>
                                <th>Trip Time</th>
                                <th>Driver</th>
                                <th>Assistant</th>

                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            @forelse ($dailyBusLists as $busList)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $busList->list_date }}</td>
                                <td>
                                    {{ $busList->vehicle->model_name ?? 'N/A' }}
                                    <br><small class="text-muted">{{ $busList->vehicle->registration_number ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $busList->vehicleSubType->sub_type_name ?? 'N/A' }}</td>
                                <td>{{ $busList->startStoppage->stoppage_name ?? 'N/A' }}</td>
                                <td>{{ $busList->endStoppage->stoppage_name ?? 'N/A' }}</td>
                                <td>
                                    @if($busList->tripTime)
                                        <span class="badge bg-primary">{{ $busList->tripTime->time_name }}</span><br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($busList->tripTime->time_value)->format('H:i') }} {{ $busList->tripTime->time_period }}</small>
                                    @else
                                        <span class="text-muted">No Trip Time</span>
                                    @endif
                                </td>
                                <td>{{ $busList->driver->full_name ?? 'N/A' }}</td>
                                <td>{{ $busList->assistant->assistant_name ?? 'N/A' }}</td>

                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('daily-bus-lists.show', $busList->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i title="View" class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('daily-bus-lists.edit', $busList->id) }}" class="btn btn-sm btn-outline-warning">
                                            <i title="Edit" class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('daily-bus-lists.destroy', $busList->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="id" value="{{ $busList->id }}">
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                <i title="Delete" class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <div class="text-muted">
                                        <i data-feather="inbox" class="mb-2" style="width: 48px; height: 48px;"></i>
                                        <br>No bus list entries found.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3" id="paginationContainer">
                    <div>
                        {{ $dailyBusLists->links() }}
                    </div>
                    <div class="text-muted">
                        Showing {{ $dailyBusLists->firstItem() ?? 0 }} to {{ $dailyBusLists->lastItem() ?? 0 }} of {{ $dailyBusLists->total() }} results
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection