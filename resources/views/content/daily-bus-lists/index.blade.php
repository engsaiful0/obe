@extends('layouts/layoutMaster')

@section('title', 'Daily Bus List')

@section('vendor-style')
    
    <link rel="stylesheet" href="{{ asset('assets/css/daily-bus-list-ajax.css') }}">
@endsection



@section('page-script')
    
    <script src="{{ asset('assets/js/daily-bus-list-ajax.js') }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Daily Bus List</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('daily-bus-lists.create') }}" class="btn btn-primary">
                        <i data-feather="plus"></i> Add New Entry
                    </a>
                    <button type="button" class="btn btn-success" id="exportPdfBtn">
                        <i data-feather="download"></i> Export PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="filterDate" name="date">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="filterDateFrom" name="date_from">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="filterDateTo" name="date_to">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bus Type</label>
                        <select class="form-select" id="filterBusType" name="bus_type">
                            <option value="">All Types</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Driver</label>
                        <select class="form-select" id="filterDriver" name="driver_id">
                            <option value="">All Drivers</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Assistant</label>
                        <select class="form-select" id="filterAssistant" name="assistant_id">
                            <option value="">All Assistants</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Vehicle</label>
                        <select class="form-select" id="filterVehicle" name="vehicle_id">
                            <option value="">All Vehicles</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" id="filterSearch" name="search" placeholder="Search...">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" id="applyFilters">
                            <i data-feather="search"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearFilters">
                            <i data-feather="x"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table" id="dailyBusListTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Vehicle</th>
                                <th>Start Stoppage</th>
                                <th>End Stoppage</th>
                                <th>Start Time</th>
                                <th>Driver</th>
                                <th>Assistant</th>
                                <th>Bus Type</th>
                                @if(auth()->user()->hasPermissionTo('daily-bus-list-view') || auth()->user()->hasPermissionTo('daily-bus-list-edit') || auth()->user()->hasPermissionTo('daily-bus-list-delete'))
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table;
    let filterOptions = {};

    // Initialize DataTable
    function initializeTable() {
        table = $('#dailyBusListTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("daily-bus-lists.data") }}',
                data: function(d) {
                    return {
                        date: $('#filterDate').val(),
                        date_from: $('#filterDateFrom').val(),
                        date_to: $('#filterDateTo').val(),
                        bus_type: $('#filterBusType').val(),
                        driver_id: $('#filterDriver').val(),
                        assistant_id: $('#filterAssistant').val(),
                        vehicle_id: $('#filterVehicle').val(),
                        search: $('#filterSearch').val()
                    };
                }
            },
            columns: [
                { data: 'list_date', name: 'list_date' },
                { 
                    data: 'vehicle', 
                    name: 'vehicle',
                    render: function(data) {
                        return data ? data.model_name + ' (' + data.registration_number + ')' : '-';
                    }
                },
                { 
                    data: 'start_stoppage', 
                    name: 'start_stoppage',
                    render: function(data) {
                        return data ? data.stoppage_name : '-';
                    }
                },
                { 
                    data: 'end_stoppage', 
                    name: 'end_stoppage',
                    render: function(data) {
                        return data ? data.stoppage_name : '-';
                    }
                },
                { 
                    data: 'trip_time', 
                    name: 'trip_time',
                    render: function(data) {
                        if (data) {
                            return '<span class="badge bg-primary">' + data.time_name + '</span><br><small class="text-muted">' + 
                                   new Date('1970-01-01T' + data.time_value).toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'}) + ' ' + data.time_period + '</small>';
                        }
                        return '<span class="text-muted">No Trip Time</span>';
                    }
                },
                { 
                    data: 'driver', 
                    name: 'driver',
                    render: function(data) {
                        return data ? data.full_name : '-';
                    }
                },
                { 
                    data: 'assistant', 
                    name: 'assistant',
                    render: function(data) {
                        return data ? data.assistant_name : '-';
                    }
                },
                { 
                    data: 'bus_type', 
                    name: 'bus_type',
                    render: function(data) {
                        const busTypes = {
                            'own': 'Own Bus',
                            'hired': 'Hired Bus',
                            'brtc': 'BRTC Bus'
                        };
                        return busTypes[data] || data;
                    }
                },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex gap-1">
                                <a href="/app/daily-bus-lists/${data}/edit" class="btn btn-sm btn-outline-primary">
                                    <i data-feather="edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEntry(${data})">
                                    <i data-feather="trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    }

    // Load filter options
    function loadFilterOptions() {
        $.ajax({
            url: '{{ route("daily-bus-lists.filter-options") }}',
            method: 'GET',
            success: function(response) {
                filterOptions = response;
                
                // Populate bus types
                const busTypeSelect = $('#filterBusType');
                busTypeSelect.empty().append('<option value="">All Types</option>');
                $.each(response.bus_types, function(key, value) {
                    busTypeSelect.append(`<option value="${key}">${value}</option>`);
                });

                // Populate drivers
                const driverSelect = $('#filterDriver');
                driverSelect.empty().append('<option value="">All Drivers</option>');
                $.each(response.drivers, function(index, driver) {
                    driverSelect.append(`<option value="${driver.id}">${driver.full_name} (${driver.driver_unique_id})</option>`);
                });

                // Populate assistants
                const assistantSelect = $('#filterAssistant');
                assistantSelect.empty().append('<option value="">All Assistants</option>');
                $.each(response.assistants, function(index, assistant) {
                    assistantSelect.append(`<option value="${assistant.id}">${assistant.assistant_name} (${assistant.assistant_id})</option>`);
                });

                // Populate vehicles
                const vehicleSelect = $('#filterVehicle');
                vehicleSelect.empty().append('<option value="">All Vehicles</option>');
                $.each(response.vehicles, function(index, vehicle) {
                    vehicleSelect.append(`<option value="${vehicle.id}">${vehicle.model_name} (${vehicle.registration_number})</option>`);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading filter options:', error);
            }
        });
    }

    // Apply filters
    $('#applyFilters').click(function() {
        showSpinner();
        table.ajax.reload(function() {
            hideSpinner();
        });
    });

    // Clear filters
    $('#clearFilters').click(function() {
        $('#filterDate').val('');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('#filterBusType').val('').trigger('change');
        $('#filterDriver').val('').trigger('change');
        $('#filterAssistant').val('').trigger('change');
        $('#filterVehicle').val('').trigger('change');
        $('#filterSearch').val('');
        showSpinner();
        table.ajax.reload(function() {
            hideSpinner();
        });
    });

    // Export PDF
    $('#exportPdfBtn').click(function() {
        const filters = {
            date: $('#filterDate').val(),
            date_from: $('#filterDateFrom').val(),
            date_to: $('#filterDateTo').val(),
            bus_type: $('#filterBusType').val(),
            driver_id: $('#filterDriver').val(),
            assistant_id: $('#filterAssistant').val(),
            vehicle_id: $('#filterVehicle').val(),
            search: $('#filterSearch').val()
        };

        const queryString = $.param(filters);
        window.open('{{ route("daily-bus-lists.export-pdf") }}?' + queryString, '_blank');
    });

    // Delete entry
    window.deleteEntry = function(id) {
        if (confirm('Are you sure you want to delete this entry?')) {
            showSpinner();
            $.ajax({
                url: `/app/daily-bus-lists/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideSpinner();
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideSpinner();
                    toastr.error('Error deleting entry');
                }
            });
        }
    };

    // Show/hide spinner
    function showSpinner() {
        $('#loadingSpinner').removeClass('d-none');
    }

    function hideSpinner() {
        $('#loadingSpinner').addClass('d-none');
    }

    // Initialize
    loadFilterOptions();
    initializeTable();
});
</script>
@endpush
