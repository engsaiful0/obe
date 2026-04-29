@extends('layouts/layoutMaster')

@section('title', 'Bus Management')

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

    .filter-card {
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .filter-section {
        border-bottom: 1px solid #f0f0f0;
        padding: 1rem;
    }

    .filter-section:last-child {
        border-bottom: none;
    }

    .filter-section h6 {
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Bus Management</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('buses.expired-documents') }}" class="btn btn-warning">
                <i class="ti ti-alert-triangle me-1"></i>Expired Documents
            </a>
            <a href="{{ route('buses.service-due') }}" class="btn btn-info">
                <i class="ti ti-tools me-1"></i>Service Due
            </a>
            <a href="{{ route('buses.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>Add Bus
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

        <!-- Advanced Filter Form -->
        <div class="card filter-card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="ti ti-filter me-2"></i>Advanced Filters
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleFilters">
                        <i class="ti ti-chevron-down me-1"></i>Toggle Filters
                    </button>
                </div>
            </div>
            <div class="card-body" id="filterContent" style="display: none;">
                <form id="filterForm" method="GET" action="{{ route('buses.index') }}">
                    <!-- Basic Search -->
                    <div class="filter-section">
                        <h6><i class="ti ti-search me-2"></i>Basic Search</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" id="search" class="form-control"
                                    placeholder="Search buses, registration, chassis..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status_id" id="status_id" class="form-select">
                                    <option value="">All Status</option>
                                    @foreach($statusOptions as $status) 
                                    <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->status_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bus Type</label>
                                <select name="bus_type_id" id="bus_type_id" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($busTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('bus_type_id') == $type->id ? 'selected' : '' }}>{{ $type->bus_type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bus Sub-Type</label>
                                <select name="bus_sub_type_id" id="bus_sub_type_id" class="form-select">
                                    <option value="">All Sub-Types</option>
                                    @foreach($busSubTypes as $subType)
                                    <option value="{{ $subType->id }}" {{ request('bus_sub_type_id') == $subType->id ? 'selected' : '' }}>{{ $subType->sub_type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Bus Details -->
                    <div class="filter-section">
                        <h6><i class="ti ti-car me-2"></i>Bus Details</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Brand</label>
                                <select name="brand_id" id="brand_id" class="form-select">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->brand_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Color</label>
                                <select name="color_id" id="color_id" class="form-select">
                                    <option value="">All Colors</option>
                                    @foreach($colors as $color)
                                    <option value="{{ $color->id }}" {{ request('color_id') == $color->id ? 'selected' : '' }}>{{ $color->color_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Year of Manufacture</label>
                                <select name="year_of_manufacture_id" id="year_of_manufacture_id" class="form-select">
                                    <option value="">All Years</option>
                                    @foreach($years as $year)
                                    <option value="{{ $year->id }}" {{ request('year_of_manufacture_id') == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fuel Type</label>
                                <select name="fuel_type_id" id="fuel_type_id" class="form-select">
                                    <option value="">All Fuel Types</option>
                                    @foreach($fuelTypes as $fuelType)
                                    <option value="{{ $fuelType->id }}" {{ request('fuel_type_id') == $fuelType->id ? 'selected' : '' }}>{{ $fuelType->fuel_type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Assignment -->
                    <div class="filter-section">
                        <h6><i class="ti ti-users me-2"></i>Staff Assignment</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Owner/Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="form-select">
                                    <option value="">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Driver</label>
                                <select name="driver_id" id="driver_id" class="form-select">
                                    <option value="">All Drivers</option>
                                    @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>{{ $driver->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bus Helper</label>
                                <select name="bus_helper_id" id="bus_helper_id" class="form-select">
                                    <option value="">All Bus Helpers</option>
                                    @foreach($busHelpers as $busHelper)
                                    <option value="{{ $busHelper->id }}" {{ request('bus_helper_id') == $busHelper->id ? 'selected' : '' }}>{{ $busHelper->bus_helper_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Document Status -->
                    <div class="filter-section">
                        <h6><i class="ti ti-file-text me-2"></i>Document Status</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Document Status</label>
                                <select name="document_status" id="document_status" class="form-select">
                                    <option value="">All Documents</option>
                                    <option value="valid" {{ request('document_status') == 'valid' ? 'selected' : '' }}>Valid Documents</option>
                                    <option value="expired" {{ request('document_status') == 'expired' ? 'selected' : '' }}>Expired Documents</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Registration Expiry</label>
                                <select name="registration_expiry" id="registration_expiry" class="form-select">
                                    <option value="">All</option>
                                    <option value="expired" {{ request('registration_expiry') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="expiring_soon" {{ request('registration_expiry') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (30 days)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Insurance Expiry</label>
                                <select name="insurance_expiry" id="insurance_expiry" class="form-select">
                                    <option value="">All</option>
                                    <option value="expired" {{ request('insurance_expiry') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="expiring_soon" {{ request('insurance_expiry') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (30 days)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fitness Expiry</label>
                                <select name="fitness_expiry" id="fitness_expiry" class="form-select">
                                    <option value="">All</option>
                                    <option value="expired" {{ request('fitness_expiry') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="expiring_soon" {{ request('fitness_expiry') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (30 days)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="filter-section">
                        <h6><i class="ti ti-calendar me-2"></i>Date Range</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Registration Date From</label>
                                <input type="date" name="registration_date_from" id="registration_date_from"
                                    class="form-control" value="{{ request('registration_date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Registration Date To</label>
                                <input type="date" name="registration_date_to" id="registration_date_to"
                                    class="form-control" value="{{ request('registration_date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Purchase Date From</label>
                                <input type="date" name="purchase_date_from" id="purchase_date_from"
                                    class="form-control" value="{{ request('purchase_date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Purchase Date To</label>
                                <input type="date" name="purchase_date_to" id="purchase_date_to"
                                    class="form-control" value="{{ request('purchase_date_to') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="filter-section">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" id="applyFilters">
                                        <i class="ti ti-filter me-1"></i>Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                                        <i class="ti ti-refresh me-1"></i>Clear All
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="exportFilters">
                                        <i class="ti ti-download me-1"></i>Export Results
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Container with Overlay -->
        <div id="tableContainer">
            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="table-overlay d-none">
                <div class="spinner-wrapper">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading buses...</p>
                </div>
            </div>

            <!-- Table Content -->
            <div id="tableContent">
                @include('content.buses.partials.table', ['buses' => $buses])
            </div>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4" id="paginationContainer">
            {{ $buses->links() }}
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        let ajaxRequest = null;
        let searchTimeout;

        // Initialize Select2 for better UX
        if ($.fn.select2) {
            $('.form-select').select2({
                width: '100%',
                placeholder: 'Select an option'
            });
        }

        // Toggle filter visibility
        $('#toggleFilters').on('click', function() {
            const content = $('#filterContent');
            const icon = $(this).find('i');

            if (content.is(':visible')) {
                content.slideUp();
                icon.removeClass('ti-chevron-up').addClass('ti-chevron-down');
            } else {
                content.slideDown();
                icon.removeClass('ti-chevron-down').addClass('ti-chevron-up');
            }
        });

        // Show loading overlay
        function showLoading() {
            $('#loadingOverlay').removeClass('d-none');
        }

        // Hide loading overlay
        function hideLoading() {
            $('#loadingOverlay').addClass('d-none');
        }

        // Load buses with AJAX
        function loadBusesData(page = 1) {
            // Cancel previous ajax request if exists
            if (ajaxRequest) {
                ajaxRequest.abort();
            }

            showLoading();

            let formData = {
                search: $('#search').val(),
                status_id: $('#status_id').val(),
                bus_type_id: $('#bus_type_id').val(),
                bus_sub_type_id: $('#bus_sub_type_id').val(),
                brand_id: $('#brand_id').val(),
                color_id: $('#color_id').val(),
                year_of_manufacture_id: $('#year_of_manufacture_id').val(),
                fuel_type_id: $('#fuel_type_id').val(),
                supplier_id: $('#supplier_id').val(),
                driver_id: $('#driver_id').val(),
                assistant_id: $('#assistant_id').val(),
                document_status: $('#document_status').val(),
                registration_expiry: $('#registration_expiry').val(),
                insurance_expiry: $('#insurance_expiry').val(),
                fitness_expiry: $('#fitness_expiry').val(),
                registration_date_from: $('#registration_date_from').val(),
                registration_date_to: $('#registration_date_to').val(),
                purchase_date_from: $('#purchase_date_from').val(),
                purchase_date_to: $('#purchase_date_to').val(),
                page: page
            };

            ajaxRequest = $.ajax({
                url: '{{ route("buses.index") }}',
                type: 'GET',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        $('#tableContent').html(response.html);
                        $('#paginationContainer').html(response.pagination);

                        // Update URL without page reload
                        let params = new URLSearchParams(formData);
                        let newUrl = window.location.pathname + '?' + params.toString();
                        window.history.pushState({}, '', newUrl);
                    }
                    hideLoading();
                },
                error: function(xhr) {
                    if (xhr.statusText !== 'abort') {
                        hideLoading();
                        toastr.error('An error occurred while loading buses.');
                    }
                }
            });
        }

        // Search with debounce
        $('#search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadBusesData(1);
            }, 500);
        });

        // Filter changes
        $('#status_id, #bus_type_id, #bus_sub_type_id, #brand_id, #color_id, #year_of_manufacture_id, #fuel_type_id, #supplier_id, #driver_id, #assistant_id, #document_status, #registration_expiry, #insurance_expiry, #fitness_expiry').on('change', function() {
            loadBusesData(1);
        });

        // Date filters
        $('#registration_date_from, #registration_date_to, #purchase_date_from, #purchase_date_to').on('change', function() {
            loadBusesData(1);
        });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#filterForm')[0].reset();
            $('.form-select').val('').trigger('change');
            loadBusesData(1);
        });

        // Export filters
        $('#exportFilters').on('click', function() {
            let formData = $('#filterForm').serialize();
            window.open('{{ route("buses.export") }}?' + formData, '_blank');
        });

        // Pagination click handler
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            if (page) {
                loadBusesData(page);
                $('html, body').animate({
                    scrollTop: $('#tableContainer').offset().top - 100
                }, 300);
            }
        });

        // Handle delete with SweetAlert
        $(document).on('submit', 'form[action*="buses"]', function(e) {
            if ($(this).find('button[type="submit"]').hasClass('text-danger')) {
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
                                loadBusesData($('.pagination .active span').text() || 1);

                                if (response.success) {
                                    toastr.success(response.message || 'Bus deleted successfully.');
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

        // Initialize filters on page load
        if (window.location.search) {
            loadBusesData(1);
        }
    });
</script>
@endsection