@extends('layouts/layoutMaster')

@section('title', 'Assign Driver and Helper All')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ti ti-users-group me-2"></i>Assign Driver and Helper All
        </h5>
    </div>
    
    <div class="card-body">
        <!-- Alert Messages -->
        <div id="alertContainer" class="d-none mb-3">
            <div class="alert alert-dismissible fade show" role="alert" id="alertMessage">
                <i class="me-2" id="alertIcon"></i>
                <span id="alertText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>

        @if(count($buses) > 0)
            <form id="assignAllForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 15%;">Bus Number</th>
                                <th style="width: 20%;">Driver <span class="text-danger">*</span></th>
                                <th style="width: 20%;">Helper <span class="text-danger">*</span></th>
                                <th style="width: 15%;">Assignment Date <span class="text-danger">*</span></th>
                                <th style="width: 20%;">Comment</th>
                                <th style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($buses as $index => $bus)
                                @php
                                    $assignment = $bus->driverHelperAssignment;
                                    $currentDriverId = $assignment ? $assignment->driver_id : ($bus->driver_id ?? null);
                                    $currentHelperId = $assignment ? $assignment->bus_helper_id : ($bus->bus_helper_id ?? null);
                                    $currentDate = $assignment ? $assignment->assignment_date->format('Y-m-d') : date('Y-m-d');
                                    $currentNotes = $assignment ? $assignment->notes : '';
                                    $currentStatusId = $assignment ? $assignment->status_id : ($statuses->first() ? $statuses->first()->id : null);
                                @endphp
                                <tr data-bus-id="{{ $bus->id }}">
                                    <td>
                                        <strong>{{ $bus->bus_number }}</strong>
                                        <input type="hidden" name="assignments[{{ $index }}][bus_id]" value="{{ $bus->id }}">
                                    </td>
                                    <td>
                                        <select class="form-select driver-select" 
                                                name="assignments[{{ $index }}][driver_id]" 
                                                data-bus-id="{{ $bus->id }}"
                                                required>
                                            <option value="">Select Driver</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" 
                                                    {{ $currentDriverId == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->full_name }} ({{ $driver->driver_unique_id ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </td>
                                    <td>
                                        <select class="form-select helper-select" 
                                                name="assignments[{{ $index }}][bus_helper_id]" 
                                                data-bus-id="{{ $bus->id }}"
                                                required>
                                            <option value="">Select Helper</option>
                                            @foreach($busHelpers as $helper)
                                                <option value="{{ $helper->id }}" 
                                                    {{ $currentHelperId == $helper->id ? 'selected' : '' }}>
                                                    {{ $helper->bus_helper_name }} ({{ $helper->bus_helper_id ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               class="form-control assignment-date" 
                                               name="assignments[{{ $index }}][assignment_date]" 
                                               value="{{ $currentDate }}"
                                               data-bus-id="{{ $bus->id }}"
                                               required>
                                        <input type="hidden" name="assignments[{{ $index }}][status_id]" value="{{ $currentStatusId }}">
                                        <div class="invalid-feedback"></div>
                                    </td>
                                    <td>
                                        <textarea class="form-control notes" 
                                                  name="assignments[{{ $index }}][notes]" 
                                                  rows="2" 
                                                  data-bus-id="{{ $bus->id }}"
                                                  placeholder="Optional comment...">{{ $currentNotes }}</textarea>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary save-row-btn" 
                                                data-bus-id="{{ $bus->id }}">
                                            <i class="ti ti-device-floppy me-1"></i>Save
                                            <span class="spinner-border spinner-border-sm ms-1 d-none" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                                <i class="ti ti-refresh me-1"></i>Reset
                            </button>
                            <button type="button" class="btn btn-success" id="saveAllBtn">
                                <i class="ti ti-device-floppy me-1"></i>Save All
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="saveAllSpinner">
                                    <span class="visually-hidden">Loading...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-warning">
                <i class="ti ti-alert-circle me-2"></i>
                No own buses found. Please add own buses first.
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        // Initialize datepickers
        $('.assignment-date').flatpickr({
            dateFormat: 'Y-m-d',
            maxDate: 'today'
        });

        // Initialize Select2 for dropdowns
        $('.driver-select, .helper-select').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        // Show alert
        function showAlert(type, message) {
            $('#alertContainer').removeClass('d-none').addClass('d-block');
            $('#alertMessage').removeClass().addClass(`alert alert-${type} alert-dismissible fade show`);
            $('#alertText').text(message);
            $('#alertIcon').removeClass().addClass(type === 'success' ? 'ti ti-check-circle me-2' : 'ti ti-alert-circle me-2');
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        }

        // Hide alert
        function hideAlert() {
            $('#alertContainer').removeClass('d-block').addClass('d-none');
        }

        // Save single row
        $('.save-row-btn').on('click', function() {
            const btn = $(this);
            const busId = btn.data('bus-id');
            const row = btn.closest('tr');
            const spinner = btn.find('.spinner-border');
            
            // Get form data for this row
            const formData = {
                bus_id: row.find('input[name*="[bus_id]"]').val(),
                driver_id: row.find('.driver-select').val(),
                bus_helper_id: row.find('.helper-select').val(),
                assignment_date: row.find('.assignment-date').val(),
                notes: row.find('.notes').val(),
                status_id: row.find('input[name*="[status_id]"]').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Validate
            if (!formData.driver_id || !formData.bus_helper_id || !formData.assignment_date) {
                showAlert('danger', 'Please fill in all required fields (Driver, Helper, and Assignment Date).');
                return;
            }

            // Disable button and show spinner
            btn.prop('disabled', true);
            spinner.removeClass('d-none');
            hideAlert();

            // Clear previous validation errors
            row.find('.is-invalid').removeClass('is-invalid');
            row.find('.invalid-feedback').text('');

            $.ajax({
                url: '{{ route("buses.save-driver-helper-assignment") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    if (response.success) {
                        showAlert('success', response.message);
                        toastr.success(response.message);
                    } else {
                        showAlert('danger', response.message);
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorMessages = [];

                        // Display field-specific errors
                        for (let field in errors) {
                            const fieldElement = row.find('[name*="[' + field + ']"]');
                            if (fieldElement.length) {
                                fieldElement.addClass('is-invalid');
                                fieldElement.siblings('.invalid-feedback').text(errors[field][0]);
                            }
                            errorMessages.push(errors[field][0]);
                        }

                        const errorMessage = xhr.responseJSON?.message || errorMessages.join('<br>');
                        showAlert('danger', errorMessage);
                        toastr.error(errorMessage);
                    } else {
                        const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                        showAlert('danger', errorMessage);
                        toastr.error(errorMessage);
                    }
                }
            });
        });

        // Save all rows
        $('#saveAllBtn').on('click', function() {
            const btn = $(this);
            const spinner = $('#saveAllSpinner');
            
            // Collect all form data
            const assignments = [];
            let hasError = false;

            $('tbody tr').each(function(index) {
                const row = $(this);
                const assignment = {
                    bus_id: row.find('input[name*="[bus_id]"]').val(),
                    driver_id: row.find('.driver-select').val(),
                    bus_helper_id: row.find('.helper-select').val(),
                    assignment_date: row.find('.assignment-date').val(),
                    notes: row.find('.notes').val(),
                    status_id: row.find('input[name*="[status_id]"]').val()
                };

                // Validate required fields
                if (!assignment.driver_id || !assignment.bus_helper_id || !assignment.assignment_date) {
                    row.find('.driver-select, .helper-select, .assignment-date').addClass('is-invalid');
                    hasError = true;
                } else {
                    row.find('.is-invalid').removeClass('is-invalid');
                }

                assignments.push(assignment);
            });

            if (hasError) {
                showAlert('danger', 'Please fill in all required fields (Driver, Helper, and Assignment Date) for all rows.');
                toastr.error('Please fill in all required fields for all rows.');
                return;
            }

            // Disable button and show spinner
            btn.prop('disabled', true);
            spinner.removeClass('d-none');
            hideAlert();

            // Clear previous validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            $.ajax({
                url: '{{ route("buses.save-all-driver-helper-assignments") }}',
                type: 'POST',
                data: {
                    assignments: assignments,
                    _token: $('meta[name="csrf-token"]').attr('content')
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
                        showAlert('success', response.message);
                        toastr.success(response.message);
                    } else {
                        showAlert('danger', response.message);
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorMessages = [];

                        // Display field-specific errors
                        for (let field in errors) {
                            $('[name*="[' + field + ']"]').addClass('is-invalid');
                            errorMessages.push(errors[field][0]);
                        }

                        const errorMessage = xhr.responseJSON?.message || errorMessages.join('<br>');
                        showAlert('danger', errorMessage);
                        toastr.error(errorMessage);
                    } else {
                        const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                        showAlert('danger', errorMessage);
                        toastr.error(errorMessage);
                    }
                }
            });
        });

        // Remove invalid class on input/change
        $(document).on('input change', '.driver-select, .helper-select, .assignment-date, .notes', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('');
        });
    });
</script>
@endsection

