@extends('layouts/layoutMaster')

@section('title', 'Bus Helper Management')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Bus Helper Management</h5>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">Total: {{ $busHelpers->total() }}</span>
            <a href="{{ route('bus-helpers.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>Add Bus Helper
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

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <form method="GET" action="{{ route('bus-helpers.index') }}" id="filterForm" class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search bus helpers..." value="{{ request('search') }}">
                    </div>
                    
                    <!-- Gender Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="gender_filter" id="gender_filter" class="form-select">
                            <option value="">All Genders</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender->id }}" {{ request('gender_filter') == $gender->id ? 'selected' : '' }}>
                                    {{ $gender->gender_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Employee Type Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Employee Type</label>
                        <select name="employee_type_filter" id="employee_type_filter" class="form-select">
                            <option value="">All Types</option>
                            @foreach($employeeTypes as $type)
                                <option value="{{ $type->id }}" {{ request('employee_type_filter') == $type->id ? 'selected' : '' }}>
                                    {{ $type->employee_type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Experience Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Experience</label>
                        <select name="experience_filter" id="experience_filter" class="form-select">
                            <option value="">All Experience</option>
                            <option value="beginner" {{ request('experience_filter') == 'beginner' ? 'selected' : '' }}>Beginner (≤1 year)</option>
                            <option value="intermediate" {{ request('experience_filter') == 'intermediate' ? 'selected' : '' }}>Intermediate (2-3 years)</option>
                            <option value="experienced" {{ request('experience_filter') == 'experienced' ? 'selected' : '' }}>Experienced (4-5 years)</option>
                            <option value="senior" {{ request('experience_filter') == 'senior' ? 'selected' : '' }}>Senior (>5 years)</option>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status_filter" id="status_filter" class="form-select">
                            <option value="">All Status</option>
                            @foreach($statusOptions as $value )
                            <option value="{{ $value->id }}" {{ request('status_filter') == $value->id ? 'selected' : '' }}>{{ $value->status_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Salary Range -->
                    <div class="col-md-3">
                        <label class="form-label">Salary Range</label>
                        <div class="input-group">
                            <input type="number" name="min_salary" id="min_salary" class="form-control" placeholder="Min" value="{{ request('min_salary') }}">
                            <span class="input-group-text">-</span>
                            <input type="number" name="max_salary" id="max_salary" class="form-control" placeholder="Max" value="{{ request('max_salary') }}">
                        </div>
                    </div>
                    
                    <!-- Filter Buttons -->
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button type="button" id="clearFilters" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0" id="resultsSummary">
                            Showing {{ $busHelpers->firstItem() ?? 0 }} to {{ $busHelpers->lastItem() ?? 0 }} of {{ $busHelpers->total() }} results
                        </p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-sort-descending me-1"></i>Sort by
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item sort-link" href="#" data-sort="bus_helper_name" data-direction="asc">
                                <i class="ti ti-sort-ascending me-1"></i>Name (A-Z)
                            </a></li>
                            <li><a class="dropdown-item sort-link" href="#" data-sort="bus_helper_name" data-direction="desc">
                                <i class="ti ti-sort-descending me-1"></i>Name (Z-A)
                            </a></li>
                            <li><a class="dropdown-item sort-link" href="#" data-sort="bus_helper_id" data-direction="asc">
                                <i class="ti ti-id me-1"></i>ID (Low to High)
                            </a></li>
                            <li><a class="dropdown-item sort-link" href="#" data-sort="years_of_experience" data-direction="desc">
                                <i class="ti ti-trending-up me-1"></i>Experience (High to Low)
                            </a></li>
                            <li><a class="dropdown-item sort-link" href="#" data-sort="gross_salary" data-direction="desc">
                                <i class="ti ti-currency-taka me-1"></i>Salary (High to Low)
                            </a></li>
                            <li><a class="dropdown-item sort-link" href="#" data-sort="created_at" data-direction="desc">
                                <i class="ti ti-clock me-1"></i>Recently Added
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="text-center py-5 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
                                        </div>
            <p class="text-muted mt-2">Loading bus helpers...</p>
                                    </div>

        <!-- Bus Helpers Table Container -->
        <div id="tableContainer">
            @include('content.bus-helpers.partials.table', ['busHelpers' => $busHelpers])
        </div>

        <!-- Pagination Container -->
        <div id="paginationContainer">
            @include('content.bus-helpers.partials.pagination', ['busHelpers' => $busHelpers])
            </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
$(document).ready(function() {
    let ajaxRequest = null;
    let searchTimeout = null;
    let currentPage = 1;

    // Show loading spinner
    function showLoading() {
        $('#loadingSpinner').removeClass('d-none');
        $('#tableContainer').addClass('opacity-50');
        $('#paginationContainer').addClass('opacity-50');
    }

    // Hide loading spinner
    function hideLoading() {
        $('#loadingSpinner').addClass('d-none');
        $('#tableContainer').removeClass('opacity-50');
        $('#paginationContainer').removeClass('opacity-50');
    }

    // Load bus helpers data via AJAX
    function loadBusHelpers(page = 1) {
        // Cancel previous request if exists
        if (ajaxRequest) {
            ajaxRequest.abort();
        }

        currentPage = page;
        showLoading();

        const formData = $('#filterForm').serialize() + '&page=' + page;

        ajaxRequest = $.ajax({
            url: '{{ route("bus-helpers.index") }}',
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $('#tableContainer').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                    
                    // Update results summary
                    $('#resultsSummary').text(
                        `Showing ${response.from || 0} to ${response.to || 0} of ${response.total || 0} results`
                    );

                    // Update URL without page reload
                    let params = new URLSearchParams(formData);
                    let newUrl = window.location.pathname + '?' + params.toString();
                    window.history.pushState({}, '', newUrl);

                    // Re-initialize event listeners for action buttons
                    initializeActionButtons();
                }
                hideLoading();
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    hideLoading();
                    toastr.error('An error occurred while loading bus helpers.');
                }
            }
        });
    }

    // Initialize action buttons (delete)
    function initializeActionButtons() {
        // Event listeners for delete buttons
        $(document).off('click', '.btn-delete-bus-helper').on('click', '.btn-delete-bus-helper', function() {
            const busHelperId = $(this).data('bus-helper-id');
            deleteBusHelper(busHelperId);
        });
    }

    // Search with debounce
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadBusHelpers(1);
        }, 500);
    });

    // Filter changes - trigger on change
    $('#gender_filter, #employee_type_filter, #status_filter, #experience_filter').on('change', function() {
        loadBusHelpers(1);
    });

    // Salary range filters
    $('#min_salary, #max_salary').on('change', function() {
        loadBusHelpers(1);
    });

    // Sort links
    $('.sort-link').on('click', function(e) {
        e.preventDefault();
        const sort = $(this).data('sort');
        const direction = $(this).data('direction');
        
        // Add hidden inputs for sort
        $('#filterForm').append(`<input type="hidden" name="sort" value="${sort}">`);
        $('#filterForm').append(`<input type="hidden" name="direction" value="${direction}">`);
        
        loadBusHelpers(1);
        
        // Remove the hidden inputs after a short delay
        setTimeout(function() {
            $('#filterForm input[name="sort"], #filterForm input[name="direction"]').remove();
        }, 100);
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#filterForm')[0].reset();
        loadBusHelpers(1);
    });

    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            const page = new URL(url).searchParams.get('page') || 1;
            loadBusHelpers(page);
        }
    });

    // Initialize action buttons on page load
    initializeActionButtons();

    function deleteBusHelper(busHelperId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete this bus helper. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="ti ti-trash me-1"></i>Yes, delete it!',
        cancelButtonText: '<i class="ti ti-x me-1"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Build the URL - use the base URL from the page
            const baseUrl = '{{ url("/app/bus-helpers") }}';
            const deleteUrl = `${baseUrl}/${busHelperId}`;
            
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(data) {
                    if (data.success) {
                       
                        toastr.success(data.message || 'Bus helper deleted successfully');
                        loadBusHelpers(currentPage);
                    } else {
                        toastr.error(data.message || 'Error deleting bus helper');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error deleting bus helper';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 405) {
                        errorMessage = 'Method not allowed. Please refresh the page and try again.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Bus helper not found.';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    toastr.error(errorMessage);
                }
            });
        }
    });
}
});
</script>
@endsection