@extends('layouts/layoutMaster')

@section('title', 'Bus Rewards')

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
        <h5 class="card-title mb-0">Vehicle Rewards</h5>
        <a href="{{ route('rewards.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Add Reward
        </a>
    </div>
    <div class="card-body">
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
                    <select name="reward_type_id" id="reward_type_id" class="select2 form-select">
                        <option value="">All Reward Types</option>
                        @foreach($rewardTypes as $rewardType)
                            <option value="{{ $rewardType->id }}" {{ request('reward_type_id') == $rewardType->id ? 'selected' : '' }}>{{ $rewardType->name }}</option>
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
                <select name="vehicle_id" id="vehicle_id" class="select2 form-select">
                        <option value="">All Buses</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" data-subtype="{{ $bus->busSubType->sub_type_name ?? '' }}" {{ old('vehicle_id') == $bus->id ? 'selected' : '' }}>
                                {{ $bus->bus_number }}
                              
                            </option>
                        @endforeach
                    </select>
                </div>
             
                <div class="col-md-2">
                    <input type="date" name="date_from" id="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
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
                    <p class="mt-2 text-muted">Loading rewards...</p>
                </div>
            </div>
            
            <!-- Table Content -->
            <div id="tableContent">
                @include('content.rewards.partials.table', ['rewards' => $rewards, 'pageTotal' => $pageTotal])
            </div>
        </div>
        
        <!-- Pagination Links -->
        <div class="mt-4" id="paginationContainer">
            {{ $rewards->links() }}
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
    
    // Load rewards with AJAX
    function loadRewards(page = 1) {
        // Cancel previous ajax request if exists
        if (ajaxRequest) {
            ajaxRequest.abort();
        }
        
        showLoading();
        
        let formData = {
            search: $('#search').val(),
            reward_type_id: $('#reward_type_id').val(),
            bus_sub_type_id: $('#bus_sub_type_id').val(),
            vehicle_id: $('#vehicle_id').val(),
            driver_id: $('#driver_id').val(),
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            page: page
        };
        
        ajaxRequest = $.ajax({
            url: '{{ route("rewards.index") }}',
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
                    alert('An error occurred while loading rewards. Please try again.');
                }
            }
        });
    }
    
    // Search with debounce
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadRewards(1);
        }, 500);
    });
    
    // Filter changes
    $('#reward_type_id, #bus_sub_type_id, #vehicle_id, #driver_id').on('change', function() {
        loadRewards(1);
    });
    
    // Date filters
    $('#date_from, #date_to').on('change', function() {
        loadRewards(1);
    });
    
    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#search').val('');
        $('#reward_type_id').val('').trigger('change');
        $('#bus_sub_type_id').val('').trigger('change');
        $('#vehicle_id').val('').trigger('change');
        $('#driver_id').val('').trigger('change');
        $('#date_from').val('');
        $('#date_to').val('');
        loadRewards(1);
    });
    
    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        if (page) {
            loadRewards(page);
            // Scroll to top of table
            $('html, body').animate({
                scrollTop: $('#tableContainer').offset().top - 100
            }, 300);
        }
    });
    
    // Handle delete form with AJAX and SweetAlert
    $(document).on('submit', 'form[action*="rewards"]', function(e) {
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
                            loadRewards($('.pagination .active span').text() || 1);
                            
                            // Show success message with SweetAlert
                            if (response.success) {
                                toastr.success(response.message || 'Reward has been deleted successfully.');
                            }
                        },
                        error: function(xhr) {   
                            toastr.error(xhr.responseJSON?.message || 'An error occurred while deleting the reward.');
                        }
                    });
                }
            });
        }
    });
});
</script>
@endsection
