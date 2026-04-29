@extends('layouts/layoutMaster')

@section('title', 'Bus Punishments')

@section('page-style')
<style>
    #tableContainer {
        position: relative;
        min-height: 400px;
    }
    
    .table-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 0.375rem;
    }
    
    .spinner-wrapper {
        text-align: center;
    }
    
    .spinner-wrapper .spinner-border {
        width: 3rem;
        height: 3rem;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Bus Punishments</h5>
        <a href="{{ route('punishments.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Add Punishment
        </a>
    </div>
    <div class="card-body" data-buses="{{ json_encode($buses) }}">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <!-- Search and Filter Form -->
        <form id="filterForm" class="mb-4">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="bus_type_id" id="bus_type_id" class="select2 form-select">
                        <option value="">All Bus Types</option>
                        @foreach($busTypes as $value)
                            <option value="{{ $value->id }}" {{ request('bus_type_id') == $value->id ? 'selected' : '' }}>{{ $value->bus_type_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="bus_sub_type_id" id="bus_sub_type_id" class="select2 form-select">
                        <option value="">All Bus Sub-Types</option>
                        @foreach($busSubTypes as $value)
                            <option value="{{ $value->id }}" {{ request('bus_sub_type_id') == $value->id ? 'selected' : '' }}>{{ $value->sub_type_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                <select name="bus_id" id="bus_id" class="select2 form-select">
                        <option value="">All Buses</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" {{ old('bus_id') == $bus->id ? 'selected' : '' }}>
                                {{ $bus->bus_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="driver_id" id="driver_id" class="select2 form-select">
                        <option value="">All Drivers</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="punishment_type_id" id="punishment_type_id" class="select2 form-select">
                        <option value="">All Punishment Types</option>
                        @foreach($punishmentTypes as $value)
                            <option value="{{ $value->id }}" {{ request('punishment_type_id') == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="violation_type_id" id="violation_type_id" class="select2 form-select">
                        <option value="">All Violation Types</option>
                        @foreach($violationTypes as $value)
                            <option value="{{ $value->id }}" {{ request('violation_type_id') == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" id="status" class="select2 form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="date" name="date_from" id="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1">
                    <input type="date" name="date_to" id="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="button" id="clearFilters" class="btn btn-secondary w-100" title="Clear Filters">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Table Container with Overlay -->
        <div id="tableContainer">
            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="table-overlay d-none">
                <div class="spinner-wrapper">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading punishment records...</p>
                                    </div>
            </div>
            
            <!-- Table Content -->
            <div id="tableContent">
                @include('content.punishments.partials.table', ['punishments' => $punishments])
            </div>
        </div>
        
        <!-- Pagination Links -->
        <div class="mt-4" id="paginationContainer">
            {{ $punishments->links() }}
            </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    let ajaxRequest = null;
    
    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%'
        });
    }
    
    // Show loading overlay
    function showLoading() {
        $('#loadingOverlay').removeClass('d-none');
    }
    
    // Hide loading overlay
    function hideLoading() {
        $('#loadingOverlay').addClass('d-none');
    }
    
    // Load punishments with AJAX
    function loadPunishments(page = 1) {
        // Cancel previous ajax request if exists
        if (ajaxRequest) {
            ajaxRequest.abort();
        }
        
        showLoading();
        
        let formData = {
            search: $('#search').val(),
            bus_type_id: $('#bus_type_id').val(),
            bus_sub_type_id: $('#bus_sub_type_id').val(),
            bus_id: $('#bus_id').val(),
            driver_id: $('#driver_id').val(),
            punishment_type_id: $('#punishment_type_id').val(),
            violation_type_id: $('#violation_type_id').val(),
            status: $('#status').val(),
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            page: page
        };
        
        ajaxRequest = $.ajax({
            url: '{{ route("punishments.index") }}',
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $('#tableContent').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                    
                    // Update URL
                    let params = new URLSearchParams(formData);
                    let newUrl = window.location.pathname + '?' + params.toString();
                    window.history.pushState({}, '', newUrl);
                }
                hideLoading();
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    hideLoading();
                    toastr.error('An error occurred while loading punishment records.');
                }
            }
        });
    }
    
    // Search with debounce
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadPunishments(1);
        }, 500);
    });
    
    // Filter changes
    $('#bus_id, #driver_id, #punishment_type_id, #violation_type_id, #status').on('change', function() {
        loadPunishments(1);
    });
    
    // Handle bus type and sub-type dependent dropdowns
    $('#bus_type_id, #bus_sub_type_id').on('change', function() {
        loadBusesByType();
        loadPunishments(1);
    });
    
    // Load buses based on bus type and sub-type
    function loadBusesByType() {
        let busTypeId = $('#bus_type_id').val();
        let busSubTypeId = $('#bus_sub_type_id').val();
        
        $.ajax({
            url: '{{ route("punishments.get-buses-by-type") }}',
            type: 'GET',
            data: {
                bus_type_id: busTypeId,
                bus_sub_type_id: busSubTypeId
            },
            success: function(response) {
                if (response.success) {
                    let busSelect = $('#bus_id');
                    busSelect.empty();
                    busSelect.append('<option value="">All Buses</option>');
                    
                    response.buses.forEach(function(bus) {
                        busSelect.append(`<option value="${bus.id}">${bus.registration_number}</option>`);
                    });
                    
                    // Reinitialize Select2 for the updated dropdown
                    if ($.fn.select2) {
                        busSelect.select2({
                            width: '100%'
                        });
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading buses:', xhr);
            }
        });
    }
    
    // Date filters
    $('#date_from, #date_to').on('change', function() {
        loadPunishments(1);
    });
    
    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#search').val('');
        $('#bus_type_id').val('').trigger('change');
        $('#bus_sub_type_id').val('').trigger('change');
        $('#bus_id').val('').trigger('change');
        $('#driver_id').val('').trigger('change');
        $('#punishment_type_id').val('').trigger('change');
        $('#violation_type_id').val('').trigger('change');
        $('#status').val('');
        $('#date_from').val('');
        $('#date_to').val('');
        
        // Reset bus dropdown to show all buses
        let busSelect = $('#bus_id');
        busSelect.empty();
        busSelect.append('<option value="">All Buses</option>');
        
        // Add all buses back to the dropdown
        let allBuses = JSON.parse($('.card-body').data('buses') || '[]');
        allBuses.forEach(function(bus) {
            busSelect.append('<option value="' + bus.id + '">' + bus.bus_number + '</option>');
        });
        
        // Reinitialize Select2
        if ($.fn.select2) {
            busSelect.select2({
                width: '100%'
            });
        }
        
        loadPunishments(1);
    });
    
    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        if (page) {
            loadPunishments(page);
            $('html, body').animate({
                scrollTop: $('#tableContainer').offset().top - 100
            }, 300);
        }
    });
    
    // Handle delete with SweetAlert
    $(document).on('submit', 'form[action*="punishments"]', function(e) {
        if ($(this).find('button[type="submit"]').hasClass('btn-danger')) {
            e.preventDefault();
            let form = $(this);
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            loadPunishments($('.pagination .active span').text() || 1);
                            
                            if (response.success) {
                                toastr.success(response.message || 'Punishment deleted successfully.');
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'An error occurred while deleting.');
                        }
                    });
                }
            });
        }
    });
});
</script>
@endsection

