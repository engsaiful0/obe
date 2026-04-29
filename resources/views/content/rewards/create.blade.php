@extends('layouts/layoutMaster')

@section('title', 'Add Reward')

@section('page-style')
<style>
    .form-control:disabled,
    .form-select:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
    }
    
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Add New Reward Record</h5>
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

        <form action="{{ route('rewards.store') }}" method="POST" enctype="multipart/form-data" id="rewardForm">
            @csrf

            <!-- Basic Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-info-circle me-2"></i>Basic Information
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="bus_sub_type_id" class="form-label">Bus Sub-Type <span class="text-danger">*</span></label>
                    <select name="bus_sub_type_id" id="bus_sub_type_id" class="select2 form-select @error('bus_sub_type_id') is-invalid @enderror" required>
                        <option value="">Select Bus Sub-Type</option>
                        @foreach($busSubTypes as $subType)
                        <option value="{{ $subType->id }}" data-name="{{ $subType->sub_type_name }}" {{ old('bus_sub_type_id') == $subType->id ? 'selected' : '' }}>
                            {{ $subType->sub_type_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="bus_id" class="form-label">Bus <span class="text-danger">*</span></label>
                    <select name="bus_id" id="bus_id" class="select2 form-select @error('bus_id') is-invalid @enderror" required>
                        <option value="">Select Bus Sub-Type First</option>
                    </select>
                </div>

            </div>

            <!-- Reward Details -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-gift me-2"></i>Reward Details
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="reward_amount" class="form-label">Reward Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" min="0" name="reward_amount" id="reward_amount"
                            class="form-control @error('reward_amount') is-invalid @enderror"
                            value="{{ old('reward_amount') }}" placeholder="0.00" required>
                    </div>
                    @error('reward_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="reward_date" class="form-label">Reward Date <span class="text-danger">*</span></label>
                    <input type="date" name="reward_date" id="reward_date" class="form-control @error('reward_date') is-invalid @enderror"
                        value="{{ old('reward_date', date('Y-m-d')) }}" required>
                    @error('reward_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="reward_type_id" class="form-label">Reward Type</label>
                    <select name="reward_type_id" id="reward_type_id" class="select2 form-select @error('reward_type_id') is-invalid @enderror">
                        <option value="">Select Reward Type (Optional)</option>
                        @foreach($rewardTypes as $rewardType)
                        <option value="{{ $rewardType->id }}" {{ old('reward_type_id') == $rewardType->id ? 'selected' : '' }}>
                            {{ $rewardType->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('reward_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" rows="4"
                        class="form-control @error('reason') is-invalid @enderror"
                        required>{{ old('reason') }}</textarea>
                    @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="3"
                        class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks') }}</textarea>
                    @error('remarks')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="witness_employee_id" class="form-label">Witness By</label>
                    <select name="witness_employee_id" id="witness_employee_id" class="select2 form-select">
                        <option value="">Select Witness Employee</option>
                        @foreach($witness_employees as $witness_employee)
                        <option value="{{ $witness_employee->id }}" {{ old('witness_employee_id') == $witness_employee->id ? 'selected' : '' }}>
                            {{ $witness_employee->employee_name }} - {{ $witness_employee->employee_unique_id }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Document Upload -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-paperclip me-2"></i>Supporting Document
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="document" class="form-label">Document</label>
                    <input type="file" name="document" id="document" class="form-control @error('document') is-invalid @enderror"
                        accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Max size: 5MB, Formats: PDF, JPEG, PNG, JPG</div>
                    @error('document')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('rewards.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="ti ti-check me-1"></i>Create Reward
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
        // Initialize Select2
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: $('.card-body')
            });
        }

        // Initialize Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#reward_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                defaultDate: 'today'
            });
        }

        // --- Utility Functions ---
        function showAlert(type, message) {
            $('#alertContainer').removeClass('d-none').addClass('d-block');
            $('#alertMessage').removeClass().addClass(`alert alert-${type} alert-dismissible fade show`);
            $('#alertText').html(message);
            $('#alertIcon').removeClass().addClass(type === 'success' ? 'ti ti-check-circle' : 'ti ti-alert-circle');
            $('.card')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function hideAlert() {
            $('#alertContainer').removeClass('d-block').addClass('d-none');
        }

        function setLoading(loading) {
            const submitBtn = $('#submitBtn');
            const cancelBtn = $('a.btn-secondary');
            
            if (loading) {
                // Disable form inputs
           
                submitBtn.prop('disabled', true);
                cancelBtn.addClass('disabled').css('pointer-events', 'none');
                
                // Show spinner inside button
                submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...');
            } else {
                // Enable form inputs
          
                submitBtn.prop('disabled', false);
                cancelBtn.removeClass('disabled').css('pointer-events', '');
                
                // Restore button text
                submitBtn.html('<i class="ti ti-check me-1"></i>Create Reward');
            }
        }

        function clearValidation() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        }

        function addFieldError(fieldId, message) {
            const $field = $('#' + fieldId);
            $field.addClass('is-invalid');
            if ($field.next('.invalid-feedback').length === 0) {
                $field.after('<div class="invalid-feedback d-block">' + message + '</div>');
            }
        }

        function validateForm() {
            clearValidation();
            let isValid = true;
            let errors = [];

            if (!$('#bus_sub_type_id').val()) {
                addFieldError('bus_sub_type_id', 'Please select a bus sub-type');
                errors.push('Bus sub-type is required');
                isValid = false;
            }

            if (!$('#bus_id').val()) {
                addFieldError('bus_id', 'Please select a bus');
                errors.push('Bus is required');
                isValid = false;
            }

            const rewardAmount = $('#reward_amount').val();
            if (!rewardAmount) {
                addFieldError('reward_amount', 'Reward amount is required');
                errors.push('Reward amount is required');
                isValid = false;
            } else if (parseFloat(rewardAmount) <= 0) {
                addFieldError('reward_amount', 'Reward amount must be greater than 0');
                errors.push('Reward amount must be greater than 0');
                isValid = false;
            }

            if (!$('#reward_date').val()) {
                addFieldError('reward_date', 'Reward date is required');
                errors.push('Reward date is required');
                isValid = false;
            }

            const reason = $('#reason').val().trim();
            if (!reason) {
                addFieldError('reason', 'Reason is required');
                errors.push('Reason is required');
                isValid = false;
            } else if (reason.length < 10) {
                addFieldError('reason', 'Reason must be at least 10 characters');
                errors.push('Reason must be at least 10 characters');
                isValid = false;
            }

            const documentFile = $('#document')[0].files[0];
            if (documentFile) {
                const fileSize = documentFile.size / 1024 / 1024;
                const fileExt = documentFile.name.split('.').pop().toLowerCase();
                const allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!allowedExt.includes(fileExt)) {
                    addFieldError('document', 'Invalid document format');
                    errors.push('Invalid document format');
                    isValid = false;
                } else if (fileSize > 5) {
                    addFieldError('document', 'Document must not exceed 5MB');
                    errors.push('Document too large');
                    isValid = false;
                }
            }

            if (!isValid) {
                showAlert('danger', errors.join('<br>'));
            }

            return isValid;
        }

        // Load buses when bus sub type changes
        $('#bus_sub_type_id').on('change', function() {
            const subTypeId = $(this).val();
            const busSelect = $('#bus_id');

            if (subTypeId) {
                busSelect.prop('disabled', true).html('<option value="">Loading buses...</option>');
                
                // Destroy Select2 if initialized
                if (busSelect.hasClass('select2-hidden-accessible')) {
                    busSelect.select2('destroy');
                }

                $.ajax({
                    url: '{{ route("buses.get-buses-by-subtype") }}',
                    type: 'GET',
                    data: {
                        bus_sub_type_id: subTypeId
                    },
                    success: function(response) {
                        busSelect.empty().append('<option value="">Select Bus</option>');
                        if (response.success && response.buses && response.buses.length > 0) {
                            $.each(response.buses, function(index, bus) {
                                const displayText = bus.bus_number || (bus.model_name + ' (' + bus.registration_number + ')');
                                busSelect.append(`<option value="${bus.id}">${displayText}</option>`);
                            });
                        } else {
                            busSelect.append('<option value="">No buses found</option>');
                        }
                        busSelect.prop('disabled', false);
                        
                        // Reinitialize Select2
                        if ($.fn.select2) {
                            busSelect.select2({
                                dropdownParent: $('.card-body')
                            });
                        }
                        busSelect.trigger('change');
                    },
                    error: function(xhr) {
                        console.error('Error loading buses:', xhr);
                        toastr.error('Failed to load buses. Please try again.');
                        busSelect.empty().append('<option value="">Select Bus Sub-Type First</option>').prop('disabled', false);
                        
                        // Reinitialize Select2
                        if ($.fn.select2) {
                            busSelect.select2({
                                dropdownParent: $('.card-body')
                            });
                        }
                    }
                });
            } else {
                busSelect.empty().append('<option value="">Select Bus Sub-Type First</option>').prop('disabled', false);
                
                // Reinitialize Select2
                if (busSelect.hasClass('select2-hidden-accessible')) {
                    busSelect.select2('destroy');
                }
                if ($.fn.select2) {
                    busSelect.select2({
                        dropdownParent: $('.card-body')
                    });
                }
            }
        });

        // Load buses on page load if bus_sub_type_id is already selected (e.g., from old input)
        var initialSubTypeId = $('#bus_sub_type_id').val();
        if (initialSubTypeId) {
            $('#bus_sub_type_id').trigger('change');
        }

        // --- Form Submission ---
        $('#rewardForm').on('submit', function(e) {
            e.preventDefault();
            hideAlert();
            clearValidation();

            // Validate form before submission
            if (!validateForm()) {
                return false;
            }

            // Collect form data manually to ensure Select2 values are included
            let formData = new FormData();
            
            // Add CSRF token
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            // Add all input fields
            $('#rewardForm input[type="text"], #rewardForm input[type="number"], #rewardForm input[type="date"], #rewardForm input[type="file"]').each(function() {
                const $input = $(this);
                const name = $input.attr('name');
                if (name && name !== '_token') {
                    if ($input.attr('type') === 'file') {
                        const files = $input[0].files;
                        if (files.length > 0) {
                            formData.append(name, files[0]);
                        }
                    } else {
                        formData.append(name, $input.val());
                    }
                }
            });
            
            // Add all select fields (explicitly get values for Select2)
            $('#rewardForm select').each(function() {
                const $select = $(this);
                const name = $select.attr('name');
                if (name) {
                    const value = $select.val();
                    if (value !== null && value !== undefined && value !== '') {
                        formData.append(name, value);
                    }
                }
            });
            
            // Add all textarea fields
            $('#rewardForm textarea').each(function() {
                const $textarea = $(this);
                const name = $textarea.attr('name');
                if (name) {
                    formData.append(name, $textarea.val());
                }
            });
            
            // Explicitly ensure bus_sub_type_id is included (critical field)
            const busSubTypeId = $('#bus_sub_type_id').val();
            if (busSubTypeId) {
                formData.set('bus_sub_type_id', busSubTypeId);
            } else {
                // If still empty, this is a validation error
                addFieldError('bus_sub_type_id', 'Please select a bus sub-type');
                showAlert('danger', 'Please select a bus sub-type before submitting.');
                return false;
            }
            
            // Debug: Log form data (uncomment for debugging)
            // for (let [key, val] of formData.entries()) {
            //     console.log(key, val);
            // }
            
            // Show loading spinner
            setLoading(true);

            // AJAX request to save reward
            $.ajax({
                url: '{{ route("rewards.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    setLoading(false);
                    
                    if (response.success) {
                        // Show success message
                        showAlert('success', response.message || 'Reward created successfully!');
                        
                        // Show toastr notification
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Reward created successfully!');
                        }
                        
                        // Redirect after delay
                        setTimeout(() => {
                            window.location.href = '{{ route("rewards.index") }}';
                        }, 2000);
                    } else {
                        // Show error message
                        showAlert('danger', response.message || 'Failed to create reward.');
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'Failed to create reward.');
                        }
                    }
                },
                error: function(xhr) {
                    setLoading(false);
                    
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        let errorMessages = [];
                        
                        $.each(errors, function(field, messages) {
                            addFieldError(field, messages[0]);
                            errorMessages.push(messages[0]);
                        });
                        
                        showAlert('danger', errorMessages.join('<br>'));
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Please fix the validation errors.');
                        }
                    } else if (xhr.status === 500) {
                        // Server error
                        const errorMsg = xhr.responseJSON?.message || 'An error occurred while saving. Please try again.';
                        showAlert('danger', errorMsg);
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMsg);
                        }
                    } else {
                        // Other errors
                        const errorMsg = xhr.responseJSON?.message || 'An unexpected error occurred. Please try again.';
                        showAlert('danger', errorMsg);
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMsg);
                        }
                    }
                    
                    // Scroll to top to show error
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500);
                }
            });
        });
    });

    // --- Real-time Validation ---
    $(document).on('input change', '#rewardForm input, #rewardForm select, #rewardForm textarea', function() {
        $(this).removeClass('is-invalid').next('.invalid-feedback').remove();
    });
</script>
@endsection