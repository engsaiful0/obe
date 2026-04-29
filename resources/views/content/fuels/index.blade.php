@extends('layouts/layoutMaster')

@section('title', 'Fuel Records')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
    $(function () {
        // Initialize datepickers
        $('#from_date, #to_date').flatpickr({
            dateFormat: 'Y-m-d',
            maxDate: 'today'
        });

        // Initialize Select2
        $('#bus_id, #employee_id').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        // Auto-submit on change
        $('#bus_id, #employee_id, #from_date, #to_date').on('change', function() {
            $('#filter-form').submit();
        });

        // Handle delete with SweetAlert
        $(document).on('click', '.delete-fuel-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const fuelId = btn.data('fuel-id');
            const fuelDate = btn.data('fuel-date');
            const busNumber = btn.data('bus-number');
            const spinner = btn.find('.spinner-border');
            const row = btn.closest('tr');

            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete fuel record for:<br><strong>Bus: ${busNumber}</strong><br><strong>Date: ${fuelDate}</strong><br><br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show spinner
                    btn.prop('disabled', true);
                    spinner.removeClass('d-none');

                    $.ajax({
                        url: '{{ url("app/fuels") }}/' + fuelId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            btn.prop('disabled', false);
                            spinner.addClass('d-none');
                            
                            if (response.success) {
                                // Show success toast
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(response.message || 'Fuel record deleted successfully.');
                                }
                                
                                // Remove row with fade out animation
                                row.fadeOut(300, function() {
                                    $(this).remove();
                                    
                                    // Check if table is empty
                                    if ($('tbody tr').length === 0) {
                                        location.reload();
                                    }
                                });
                            } else {
                                // Show error message
                                if (typeof toastr !== 'undefined') {
                                    toastr.error(response.message || 'Failed to delete fuel record.');
                                }
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false);
                            spinner.addClass('d-none');
                            
                            let errorMessage = 'An error occurred while deleting.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.error(errorMessage);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ti ti-gas-station me-2"></i>Fuel Records
        </h5>
        <div class="d-flex gap-2">
            @if($fuels->count() > 0)
                <a href="{{ route('fuels.print', request()->query()) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="ti ti-printer me-1"></i>Print
                </a>
                <a href="{{ route('fuels.pdf', request()->query()) }}" class="btn btn-sm btn-outline-danger">
                    <i class="ti ti-file-pdf me-1"></i>PDF
                </a>
            @endif
            <a href="{{ route('fuels.create') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus me-1"></i>Add Fuel
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Form -->
        <form id="filter-form" method="GET" action="{{ route('fuels.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="text" 
                           name="from_date" 
                           id="from_date" 
                           class="form-control" 
                           value="{{ request('from_date') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="text" 
                           name="to_date" 
                           id="to_date" 
                           class="form-control" 
                           value="{{ request('to_date') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="bus_id" class="form-label">Bus</label>
                    <select id="bus_id" name="bus_id" class="form-select">
                        <option value="">All Buses</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                                {{ $bus->bus_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="employee_id" class="form-label">Employee</label>
                    <select id="employee_id" name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->employee_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
        
        <!-- Fuel Records Table -->
        @if($fuels->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Bus Number</th>
                            <th>Fuel Amount</th>
                            <th>Unit</th>
                            <th>Concern Employee</th>
                            <th>Comment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fuels as $fuel)
                            <tr>
                                <td>{{ $loop->iteration + ($fuels->currentPage() - 1) * $fuels->perPage() }}</td>
                                <td>{{ $fuel->fuel_date->format('d M, Y') }}</td>
                                <td>{{ Carbon\Carbon::parse($fuel->fuel_time)->format('h:i A') }}</td>
                                <td><strong>{{ $fuel->bus->bus_number ?? 'N/A' }}</strong></td>
                                <td>{{ number_format($fuel->fuel_amount, 2) }}</td>
                                <td>{{ $fuel->unit->unit_name ?? 'N/A' }}</td>
                                <td>{{ $fuel->concernEmployee->employee_name ?? 'N/A' }}</td>
                                <td>{{ $fuel->comment ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('fuels.edit', $fuel->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-fuel-btn" 
                                                data-fuel-id="{{ $fuel->id }}"
                                                data-fuel-date="{{ $fuel->fuel_date->format('d M, Y') }}"
                                                data-bus-number="{{ $fuel->bus->bus_number ?? 'N/A' }}">
                                            <i class="ti ti-trash"></i>
                                            <span class="spinner-border spinner-border-sm ms-1 d-none" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $fuels->links() }}
            </div>
        @else
            <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i>
                No fuel records found.
            </div>
        @endif
    </div>
</div>
@endsection

