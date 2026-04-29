@extends('layouts/layoutMaster')

@section('title', 'Add Driver & Helper Assignment')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Add New Driver & Helper Assignment</h5>
    </div>
    <div class="card-body">
        <!-- Alert Messages -->
        <div id="alertContainer" class="d-none">
            <div class="alert alert-dismissible fade show" role="alert" id="alertMessage">
                <i class="me-2" id="alertIcon"></i>
                <span id="alertText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>

        <form action="{{ route('driver-helper-assignments.store') }}" method="POST" id="assignmentForm">
            @csrf
            <!-- Bus Selection -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Bus Information</h6>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bus_id" class="form-label">Select Own Bus <span class="text-danger">*</span></label>
                    <select class="form-select @error('bus_id') is-invalid @enderror" id="bus_id" name="bus_id" required>
                        <option value="">Select Bus</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" {{ old('bus_id') == $bus->id ? 'selected' : '' }}>
                               {{ $bus->bus_number }} - {{ $bus->busSubType->sub_type_name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('bus_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Only own buses are available for assignment</small>
                </div>
            </div>

            <!-- Driver & Helper Selection -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Assignment Details</h6>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="driver_id" class="form-label">Driver <span class="text-danger">*</span></label>
                    <select class="form-select select2 @error('driver_id') is-invalid @enderror" id="driver_id" name="driver_id" required>
                        <option value="">Select Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->full_name }} ({{ $driver->driver_unique_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bus_helper_id" class="form-label">Bus Helper <span class="text-danger">*</span></label>
                    <select class="form-select select2 @error('bus_helper_id') is-invalid @enderror" id="bus_helper_id" name="bus_helper_id" required>
                        <option value="">Select Bus Helper</option>
                        @foreach($busHelpers as $helper)
                            <option value="{{ $helper->id }}" {{ old('bus_helper_id') == $helper->id ? 'selected' : '' }}>
                                {{ $helper->bus_helper_name }} ({{ $helper->bus_helper_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('bus_helper_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Status & Date -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select select2 @error('status_id') is-invalid @enderror" id="status_id" name="status_id" required>
                        <option value="">Select Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->status_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Status related to driver-helper-assignment</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="assignment_date" class="form-label">Assignment Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('assignment_date') is-invalid @enderror" 
                           id="assignment_date" name="assignment_date" value="{{ old('assignment_date', date('Y-m-d')) }}" required>
                    @error('assignment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Notes -->
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" name="notes" rows="3" placeholder="Optional notes about this assignment...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('driver-helper-assignments.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="ti ti-check me-1"></i>Create Assignment
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="loadingSpinner">
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
    $(document).ready(function() {
        // Show alert
        function showAlert(type, message) {
            $('#alertContainer').removeClass('d-none').addClass('d-block');
            $('#alertMessage').removeClass().addClass(`alert alert-${type} alert-dismissible fade show`);
            $('#alertText').text(message);
            $('#alertIcon').removeClass().addClass(type === 'success' ? 'ti ti-check-circle me-2' : 'ti ti-alert-circle me-2');
            $('.card')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // Hide alert
        function hideAlert() {
            $('#alertContainer').removeClass('d-block').addClass('d-none');
        }

        // Toggle form state and spinner
        function toggleFormState(disabled) {
            const submitBtn = $('#submitBtn');
            const spinner = $('#loadingSpinner');

            submitBtn.prop('disabled', disabled);
            if (disabled) {
                spinner.removeClass('d-none');
                submitBtn.find('i').hide();
            } else {
                spinner.addClass('d-none');
                submitBtn.find('i').show();
            }
        }

        // AJAX form submit
        $('#assignmentForm').on('submit', function(e) {
            e.preventDefault();
            hideAlert();

            // Clear previous validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            toggleFormState(true);

            let formData = $(this).serialize();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    toggleFormState(false);
                    if (response.success) {
                        toastr.success(response.message || 'Assignment created successfully!');
                        setTimeout(() => {
                            window.location.href = '{{ route("driver-helper-assignments.index") }}';
                        }, 1500);
                    } else {
                        toastr.error(response.message || 'Failed to create assignment.');
                    }
                },
                error: function(xhr) {
                    toggleFormState(false);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorMessages = [];

                        // Display field-specific errors
                        for (let field in errors) {
                            const fieldElement = $('#' + field);
                            if (fieldElement.length) {
                                fieldElement.addClass('is-invalid');
                                fieldElement.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                            }
                            errorMessages.push(errors[field][0]);
                        }

                        // Show alert with all errors
                        if (errorMessages.length > 0) {
                            toastr.error(errorMessages.join('<br>'));
                        } else {
                            toastr.error('Validation failed. Please check the form.');
                        }
                    } else {
                        const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                        toastr.error(errorMessage);
                        console.error('AJAX Error:', xhr);
                    }
                }
            });
        });

        // Remove invalid class on input/change
        $('#assignmentForm input, #assignmentForm select, #assignmentForm textarea').on('input change', function() {
            $(this).removeClass('is-invalid');
        });
    });
</script>
@endsection

