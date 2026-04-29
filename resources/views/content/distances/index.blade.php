@extends('layouts/layoutMaster')

@section('title', 'Distance Management')

@section('page-style')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
        <h5 class="card-title">Distance Management</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('distances.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>Add Distance
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
            <div class="card-body" id="filterContent">
                <form id="filterForm" method="GET" action="{{ route('distances.index') }}">
                    <!-- Basic Search -->
                    <div class="filter-section">
                        <h6><i class="ti ti-search me-2"></i>Basic Search</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Search distances, routes, descriptions..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Distance Range (KM)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="distance_from" id="distance_from" 
                                               class="form-control" placeholder="From" step="0.01" min="0"
                                               value="{{ request('distance_from') }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="distance_to" id="distance_to" 
                                               class="form-control" placeholder="To" step="0.01" min="0"
                                               value="{{ request('distance_to') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Route Details -->
                    <div class="filter-section">
                        <h6><i class="ti ti-route me-2"></i>Route Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Stoppage</label>
                                <select name="start_stoppage_id" id="start_stoppage_id" class="form-select">
                                    <option value="">All Start Stoppages</option>
                                    @foreach($stoppages as $stoppage)
                                        <option value="{{ $stoppage->id }}" {{ request('start_stoppage_id') == $stoppage->id ? 'selected' : '' }}>{{ $stoppage->stoppage_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Stoppage</label>
                                <select name="end_stoppage_id" id="end_stoppage_id" class="form-select">
                                    <option value="">All End Stoppages</option>
                                    @foreach($stoppages as $stoppage)
                                        <option value="{{ $stoppage->id }}" {{ request('end_stoppage_id') == $stoppage->id ? 'selected' : '' }}>{{ $stoppage->stoppage_name }}</option>
                                    @endforeach
                                </select>
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
                    <p class="mt-2 text-muted">Loading distances...</p>
                </div>
            </div>
            
            <!-- Table Content -->
            <div id="tableContent">
                @include('content.distances.partials.table', ['distances' => $distances])
            </div>
        </div>
        
        <!-- Pagination Links -->
        <div class="mt-4" id="paginationContainer">
            {{ $distances->links() }}
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Add CSRF token to all AJAX requests
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    console.log('CSRF Token:', csrfToken);
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });
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
    
    // Load distances with AJAX
    function loadDistances(page = 1) {
        // Cancel previous ajax request if exists
        if (ajaxRequest) {
            ajaxRequest.abort();
        }
        
        showLoading();
        
        let formData = {
            search: $('#search').val(),
            start_stoppage_id: $('#start_stoppage_id').val(),
            end_stoppage_id: $('#end_stoppage_id').val(),
            distance_from: $('#distance_from').val(),
            distance_to: $('#distance_to').val(),
            page: page
        };
        
        ajaxRequest = $.ajax({
            url: '{{ route("distances.index") }}',
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
                    toastr.error('An error occurred while loading distances.');
                }
            }
        });
    }
    
    // Search with debounce
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadDistances(1);
        }, 500);
    });
    
    // Filter changes
    $('#start_stoppage_id, #end_stoppage_id').on('change', function() {
        loadDistances(1);
    });
    
    // Distance range filters
    $('#distance_from, #distance_to').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadDistances(1);
        }, 500);
    });
    
    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#filterForm')[0].reset();
        $('.form-select').val('').trigger('change');
        loadDistances(1);
    });
    
    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        if (page) {
            loadDistances(page);
            $('html, body').animate({
                scrollTop: $('#tableContainer').offset().top - 100
            }, 300);
        }
    });
    
    // Handle delete with SweetAlert
    $(document).on('click', '.delete-distance-btn', function(e) {
        e.preventDefault();
        
        const distanceId = $(this).data('distance-id');
        const distanceName = $(this).data('distance-name');
        const deleteUrl = $(this).data('delete-url');
        
        console.log('Delete clicked for distance ID:', distanceId);
        console.log('Distance name:', distanceName);
        console.log('Delete URL:', deleteUrl);
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the distance "${distanceName}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                console.log('Making AJAX request to:', deleteUrl);
                return $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(response => {
                    console.log('Delete response:', response);
                    return response;
                }).catch(error => {
                    console.error('Delete error details:', {
                        status: error.status,
                        statusText: error.statusText,
                        responseText: error.responseText,
                        responseJSON: error.responseJSON
                    });
                    let errorMessage = 'Unknown error';
                    if (error.responseJSON && error.responseJSON.message) {
                        errorMessage = error.responseJSON.message;
                    } else if (error.status === 404) {
                        errorMessage = 'Distance not found';
                    } else if (error.status === 500) {
                        errorMessage = 'Server error occurred';
                    } else if (error.status === 419) {
                        errorMessage = 'CSRF token mismatch. Please refresh the page.';
                    } else if (error.status === 0) {
                        errorMessage = 'Network error - please check your connection';
                    } else if (error.status === 422) {
                        errorMessage = 'Validation error';
                    }
                    Swal.showValidationMessage(`Request failed: ${errorMessage}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value && result.value.success) {
                    toastr.success('Distance deleted successfully!');
                    loadDistances($('.pagination .active span').text() || 1);
                } else {
                    // Fallback to form submission if AJAX fails
                    console.log('AJAX failed, trying form submission...');
                    const form = document.getElementById(`delete-form-${distanceId}`);
                    if (form) {
                        form.submit();
                    } else {
                        toastr.error('Failed to delete distance.');
                    }
                }
            }
        });
    });
    
    // Initialize filters on page load
    if (window.location.search) {
        loadDistances(1);
    }
});
</script>
@endsection
