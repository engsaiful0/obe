@extends('layouts/layoutMaster')

@section('title', 'Bus Trips')

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
        <h5 class="card-title mb-0">Bus Trip Records</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('bus-trips.monthly-billing') }}" class="btn btn-info">
                <i class="ti ti-report-money me-1"></i>Monthly Billing
            </a>
            <a href="{{ route('bus-trips.add-all-bus-trip') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>Record Trip
            </a>
        </div>
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
                        @foreach($busTypes as $busType)
                        <option value="{{ $busType->id }}" {{ request('bus_type_id') == $busType->id ? 'selected' : '' }}>
                            {{ $busType->bus_type_name}}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="bus_sub_type_id" id="bus_sub_type_id" class="select2 form-select">
                        <option value="">All Bus Sub-Types</option>
                        @foreach($busSubTypes as $busSubType)
                        <option value="{{ $busSubType->id }}" {{ request('bus_sub_type_id') == $busSubType->id ? 'selected' : '' }}>
                            {{ $busSubType->sub_type_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="bus_id" id="bus_id" class="select2 form-select">
                        <option value="">All Buses</option>
                        @foreach($buses as $bus)
                        <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                            {{ $bus->registration_number }}
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
                    <select name="bus_helper_id" id="bus_helper_id" class="select2 form-select">
                        <option value="">All Bus Helpers</option>
                        @foreach($busHelpers as $busHelper)
                        <option value="{{ $busHelper->id }}" {{ request('bus_helper_id') == $busHelper->id ? 'selected' : '' }}>
                            {{ $busHelper->bus_helper_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="trip_type" id="trip_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="arrival" {{ request('trip_type') == 'arrival' ? 'selected' : '' }}>Arrival</option>
                        <option value="departure" {{ request('trip_type') == 'departure' ? 'selected' : '' }}>Departure</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" id="date_from" class="form-control" placeholder="From" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" id="date_to" class="form-control" placeholder="To" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="button" id="clearFilters" class="btn btn-secondary w-100" title="Clear Filters">
                        <i class="ti ti-x">Clear Filters</i>
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
                    <p class="mt-2 text-muted">Loading Bus trip records...</p>
                </div>
            </div>

            <!-- Table Content -->
            <div id="tableContent">
                @include('content.bus-trips.partials.table', ['busTrips' => $busTrips])
            </div>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4" id="paginationContainer">
            {{ $busTrips->links() }}
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        // Vehicle Sub Type Change Event
        $('#vehicle_sub_type_id').on('change', function() {
            const subTypeId = $(this).val();
            const busSelect = $('#bus_id');

            if (subTypeId) {
                showSpinner();
                busSelect.prop('disabled', true).html('<option value="">Loading buses...</option>');

                $.ajax({
                    url: '{{ route("bus-trips.get-buses-names-by-subtype") }}',
                    type: 'GET',
                    data: {
                        sub_type_id: subTypeId
                    },
                    success: function(response) {
                        busSelect.empty().append('<option value="">Select Bus</option>');
                        if (response.success && response.buses && response.buses.length > 0) {
                            $.each(response.buses, function(index, bus) {
                                busSelect.append(`<option value="${bus.id}">${bus.model_name} (${bus.registration_number})</option>`);
                            });
                        } else {
                            busSelect.append('<option value="">No buses found</option>');
                        }
                        busSelect.prop('disabled', false);
                        hideSpinner();
                    },
                    error: function() {
                        toastr.error('Failed to load buses.');
                        busSelect.prop('disabled', false);
                        hideSpinner();
                    }
                });
            } else {
                busSelect.empty().append('<option value="">Select Bus</option>');
                @foreach($buses as $bus)
                busSelect.append('<option value="{{ $bus->id }}">{{ $bus->registration_number }}</option>');
                @endforeach
            }
        });

        // Spinner functions for the page
        function showSpinner() {
            $('#loadingSpinner').removeClass('d-none');
        }

        function hideSpinner() {
            $('#loadingSpinner').addClass('d-none');
        }
    });
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

        // Load attendances with AJAX
        function loadTrips(page = 1) {
            // Cancel previous ajax request if exists
            if (ajaxRequest) {
                ajaxRequest.abort();
            }

            showLoading();

            let formData = {
                search: $('#search').val(),
                bus_id: $('#bus_id').val(),
                driver_id: $('#driver_id').val(),
                bus_helper_id: $('#bus_helper_id').val(),
                bus_type_id: $('#bus_type_id').val(),
                bus_sub_type_id: $('#bus_sub_type_id').val(),
                trip_type: $('#trip_type').val(),
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val(),
                page: page
            };

            ajaxRequest = $.ajax({
                url: '{{ route("bus-trips.index") }}',
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
                        toastr.error('An error occurred while loading attendance records.');
                    }
                }
            });
        }

        // Search with debounce
        let searchTimeout;
        $('#search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadTrips(1);
            }, 500);
        });

        // Filter changes
        $('#bus_id, #driver_id, #bus_helper_id, #trip_type').on('change', function() {
            loadTrips(1);
        });

        // Handle bus type and sub-type dependent dropdowns
        $('#bus_type_id, #bus_sub_type_id').on('change', function() {
            loadBusesByType();
            loadTrips(1);
        });

        // Load buses based on bus type and sub-type
        function loadBusesByType() {
            let busTypeId = $('#bus_type_id').val();
            let busSubTypeId = $('#bus_sub_type_id').val();

            $.ajax({
                url: '{{ route("bus-trips.get-buses-by-type") }}',
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
            loadTrips(1);
        });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#search').val('');
            $('#bus_type_id').val('').trigger('change');
            $('#bus_sub_type_id').val('').trigger('change');
            $('#bus_id').val('').trigger('change');
            $('#driver_id').val('').trigger('change');
            $('#assistant_id').val('').trigger('change');
            $('#trip_type').val('');
            $('#date_from').val('');
            $('#date_to').val('');

            // Reset bus dropdown to show all buses
            let busSelect = $('#bus_id');
            busSelect.empty();
            busSelect.append('<option value="">All Buses</option>');

            // Add all buses back to the dropdown
            let allBuses = JSON.parse($('.card-body').data('buses') || '[]');
            allBuses.forEach(function(bus) {
                busSelect.append('<option value="' + bus.id + '">' + bus.registration_number + '</option>');
            });

            // Reinitialize Select2
            if ($.fn.select2) {
                busSelect.select2({
                    width: '100%'
                });
            }
            loadTrips(1);
        });

        // Pagination click handler
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            if (page) {
                loadTrips(page);
                $('html, body').animate({
                    scrollTop: $('#tableContainer').offset().top - 100
                }, 300);
            }
        });

        // Handle delete with AJAX, spinner, and toast
        $(document).on('click', '.delete-trip-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const tripId = $btn.data('trip-id');
            const deleteUrl = $btn.data('trip-url');
            const $row = $btn.closest('tr');

            // Show confirmation dialog
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
                    // Show spinner on button
                    const originalHtml = $btn.html();
                    $btn.prop('disabled', true);
                    $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Deleting...');

                    // Show loading overlay
                    showLoading();

                    // Make AJAX request
                    $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            _method: 'DELETE'
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            hideLoading();
                            
                            if (response.success) {
                                // Show success toast
                                toastr.success(response.message || 'Bus trip deleted successfully.');
                                
                                // Reload the trips table
                                const currentPage = $('.pagination .active span').text() || 1;
                                loadTrips(currentPage);
                            } else {
                                toastr.error(response.message || 'Failed to delete bus trip.');
                                $btn.prop('disabled', false);
                                $btn.html(originalHtml);
                            }
                        },
                        error: function(xhr) {
                            hideLoading();
                            $btn.prop('disabled', false);
                            $btn.html(originalHtml);
                            
                            if (xhr.status === 422 || xhr.status === 404) {
                                toastr.error(xhr.responseJSON?.message || 'Bus trip not found or cannot be deleted.');
                            } else if (xhr.status === 500) {
                                toastr.error('Server error. Please try again later.');
                            } else {
                                toastr.error(xhr.responseJSON?.message || 'An error occurred while deleting the bus trip.');
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection