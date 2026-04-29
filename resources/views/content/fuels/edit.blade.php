@extends('layouts/layoutMaster')

@section('title', 'Edit Fuel')

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
            <i class="ti ti-gas-station me-2"></i>Edit Fuel Record
        </h5>
        <a href="{{ route('fuels.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to List
        </a>
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

        <form id="fuelEditForm">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="bus_id" class="form-label">Bus Number <span class="text-danger">*</span></label>
                    <select id="bus_id" name="bus_id" class="form-select" required>
                        <option value="">Select Bus</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" {{ $fuel->bus_id == $bus->id ? 'selected' : '' }}>
                                {{ $bus->bus_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('bus_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="fuel_date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="text" 
                           id="fuel_date" 
                           name="fuel_date" 
                           class="form-control" 
                           value="{{ $fuel->fuel_date->format('Y-m-d') }}" 
                           required>
                    @error('fuel_date')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="fuel_time" class="form-label">Time <span class="text-danger">*</span></label>
                    <input type="time" 
                           id="fuel_time" 
                           name="fuel_time" 
                           class="form-control" 
                           value="{{ Carbon\Carbon::parse($fuel->fuel_time)->format('H:i') }}" 
                           required>
                    @error('fuel_time')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="concern_employee_id" class="form-label">Concern Employee</label>
                    <select id="concern_employee_id" name="concern_employee_id" class="form-select">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $fuel->concern_employee_id == $employee->id ? 'selected' : '' }}>
                                {{ $employee->employee_name }} ({{ $employee->employee_unique_id ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('concern_employee_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="fuel_amount" class="form-label">Fuel Amount <span class="text-danger">*</span></label>
                    <input type="number" 
                           step="0.01" 
                           min="0"
                           id="fuel_amount" 
                           name="fuel_amount" 
                           class="form-control" 
                           value="{{ $fuel->fuel_amount }}" 
                           required>
                    @error('fuel_amount')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="unit_id" class="form-label">Unit <span class="text-danger">*</span></label>
                    <select id="unit_id" name="unit_id" class="form-select" required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $fuel->unit_id == $unit->id || ( $unit->unit_name == 'Liter') ? 'selected' : '' }}>
                                {{ $unit->unit_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12">
                    <label for="comment" class="form-label">Comment</label>
                    <textarea id="comment" 
                              name="comment" 
                              class="form-control" 
                              rows="3">{{ $fuel->comment }}</textarea>
                    @error('comment')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('fuels.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                        <button type="submit" id="updateBtn" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Update
                            <span class="spinner-border spinner-border-sm ms-1 d-none" role="status" id="updateSpinner">
                                <span class="visually-hidden">Loading...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(function () {
        // Initialize datepicker
        $('#fuel_date').flatpickr({
            dateFormat: 'Y-m-d',
            maxDate: 'today'
        });

        // Initialize Select2
        $('#bus_id, #concern_employee_id, #unit_id').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        // Show alert function
        function showAlert(type, message) {
            const alertContainer = $('#alertContainer');
            const alertMessage = $('#alertMessage');
            const alertText = $('#alertText');
            const alertIcon = $('#alertIcon');

            alertMessage.removeClass('alert-success alert-danger alert-warning alert-info')
                       .addClass('alert-' + type);
            
            if (type === 'success') {
                alertIcon.removeClass().addClass('ti ti-check-circle me-2');
            } else {
                alertIcon.removeClass().addClass('ti ti-alert-circle me-2');
            }

            alertText.html(message);
            alertContainer.removeClass('d-none').addClass('d-block');
        }

        // Hide alert function
        function hideAlert() {
            $('#alertContainer').removeClass('d-block').addClass('d-none');
        }

        // Form submission
        $('#fuelEditForm').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const btn = $('#updateBtn');
            const spinner = $('#updateSpinner');
            const formData = form.serialize();

            // Validate required fields
            if (!$('#bus_id').val() || !$('#fuel_date').val() || !$('#fuel_time').val() || 
                !$('#fuel_amount').val() || !$('#unit_id').val()) {
                showAlert('danger', 'Please fill in all required fields.');
                return;
            }

            // Disable button and show spinner
            btn.prop('disabled', true);
            spinner.removeClass('d-none');
            hideAlert();

            // Clear previous validation errors
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();

            $.ajax({
                url: '{{ route("fuels.update", $fuel->id) }}',
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
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        }
                        // Redirect after 1.5 seconds
                        setTimeout(function() {
                            window.location.href = '{{ route("fuels.index") }}';
                        }, 1500);
                    } else {
                        showAlert('danger', response.message);
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message);
                        }
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
                            const fieldElement = form.find('[name="' + field + '"]');
                            if (fieldElement.length) {
                                fieldElement.addClass('is-invalid');
                                const errorDiv = $('<div class="invalid-feedback"></div>').text(errors[field][0]);
                                fieldElement.after(errorDiv);
                            }
                            errorMessages.push(errors[field][0]);
                        }

                        showAlert('danger', errorMessages.join('<br>'));
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMessages.join(', '));
                        }
                    } else {
                        const message = xhr.responseJSON?.message || 'An error occurred while updating.';
                        showAlert('danger', message);
                        if (typeof toastr !== 'undefined') {
                            toastr.error(message);
                        }
                    }
                }
            });
        });
    });
</script>
@endsection

