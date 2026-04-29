@extends('layouts/layoutMaster')

@section('title', 'Edit Bus Helper')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/css/bus-helper-form-ajax.css') }}" />
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Edit Bus Helper - {{ $busHelper->bus_helper_name }}</h5>
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

        <form action="{{ route('bus-helpers.update', $busHelper) }}" method="POST" enctype="multipart/form-data" id="busHelperForm">
            @csrf
            @method('PUT')
            
            <!-- Personal Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Personal Information</h6>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="bus_helper_name" class="form-label">Bus Helper Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('bus_helper_name') is-invalid @enderror" 
                           id="bus_helper_name" name="bus_helper_name" value="{{ old('bus_helper_name', $busHelper->bus_helper_name) }}" required>
                    @error('bus_helper_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="father_name" class="form-label">Father's Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('father_name') is-invalid @enderror" 
                           id="father_name" name="father_name" value="{{ old('father_name', $busHelper->father_name) }}" required>
                    @error('father_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="mother_name" class="form-label">Mother's Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('mother_name') is-invalid @enderror" 
                           id="mother_name" name="mother_name" value="{{ old('mother_name', $busHelper->mother_name) }}" required>
                    @error('mother_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('mobile') is-invalid @enderror" 
                           id="mobile" name="mobile" value="{{ old('mobile', $busHelper->mobile) }}" required>
                    @error('mobile')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="gender_id" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select class="form-select @error('gender_id') is-invalid @enderror" id="gender_id" name="gender_id" required>
                        <option value="">Select Gender</option>
                        @foreach($genders as $gender)
                            <option value="{{ $gender->id }}" {{ old('gender_id', $busHelper->gender_id) == $gender->id ? 'selected' : '' }}>
                                {{ $gender->gender_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('gender_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="marital_status_id" class="form-label">Marital Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('marital_status_id') is-invalid @enderror" id="marital_status_id" name="marital_status_id" required>
                        <option value="">Select Marital Status</option>
                        @foreach($maritalStatuses as $status)
                            <option value="{{ $status->id }}" {{ old('marital_status_id', $busHelper->marital_status_id) == $status->id ? 'selected' : '' }}>
                                {{ $status->marital_status_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('marital_status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="religion_id" class="form-label">Religion <span class="text-danger">*</span></label>
                    <select class="form-select @error('religion_id') is-invalid @enderror" id="religion_id" name="religion_id" required>
                        <option value="">Select Religion</option>
                        @foreach($religions as $religion)
                            <option value="{{ $religion->id }}" {{ old('religion_id', $busHelper->religion_id) == $religion->id ? 'selected' : '' }}>
                                {{ $religion->religion_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('religion_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="nid_number" class="form-label">NID Number</label>
                    <input type="text" class="form-control @error('nid_number') is-invalid @enderror" 
                           id="nid_number" name="nid_number" value="{{ old('nid_number', $busHelper->nid_number) }}">
                    @error('nid_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Address Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Address Information</h6>
                </div>
                <div class="col-6 mb-3">
                    <label for="present_address" class="form-label">Present Address</label>
                    <textarea class="form-control @error('present_address') is-invalid @enderror" 
                              id="present_address" name="present_address" rows="3" required>{{ old('present_address', $busHelper->present_address) }}</textarea>
                    @error('present_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 mb-3">
                    <label for="permanent_address" class="form-label">Permanent Address </label>
                    <textarea class="form-control @error('permanent_address') is-invalid @enderror" 
                              id="permanent_address" name="permanent_address" rows="3" required>{{ old('permanent_address', $busHelper->permanent_address) }}</textarea>
                    @error('permanent_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Academic & Experience Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Academic & Experience Information</h6>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="academic_qualification" class="form-label">Academic Qualification</label>
                    <input type="text" class="form-control @error('academic_qualification') is-invalid @enderror" 
                           id="academic_qualification" name="academic_qualification" value="{{ old('academic_qualification', $busHelper->academic_qualification) }}">
                    @error('academic_qualification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="years_of_experience" class="form-label">Years of Experience</label>
                    <input type="number" class="form-control @error('years_of_experience') is-invalid @enderror" 
                           id="years_of_experience" name="years_of_experience" value="{{ old('years_of_experience', $busHelper->years_of_experience) }}" min="0">
                    @error('years_of_experience')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Employment Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Employment Information</h6>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="employee_type_id" class="form-label">Employee Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('employee_type_id') is-invalid @enderror" id="employee_type_id" name="employee_type_id" required>
                        <option value="">Select Employee Type</option>
                        @foreach($employeeTypes as $type)
                            <option value="{{ $type->id }}" {{ old('employee_type_id', $busHelper->employee_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->employee_type_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status_id') is-invalid @enderror" id="status_id" name="status_id" required>
                        @foreach($statusOptions as $value)
                            <option value="{{ $value->id }}" {{ old('status_id', $busHelper->status_id) == $value->id ? 'selected' : '' }}>
                                {{ $value->status_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Salary Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Salary Information</h6>
                </div>
                <div class="col-md-3 mb-3" id="basic_salary_control">
                    <label for="basic_salary" class="form-label">Basic Salary <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('basic_salary') is-invalid @enderror" 
                           id="basic_salary" name="basic_salary" value="{{ old('basic_salary', $busHelper->basic_salary) }}" min="0" step="0.01" required>
                    @error('basic_salary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="house_rent_control">
                    <label for="house_rent" class="form-label">House Rent </label>
                    <input type="number" class="form-control @error('house_rent') is-invalid @enderror" 
                           id="house_rent" name="house_rent" value="{{ old('house_rent', $busHelper->house_rent) }}" min="0" step="0.01">
                    @error('house_rent')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="medical_allowance_control">
                    <label for="medical_allowance" class="form-label">Medical Allowance </label>
                    <input type="number" class="form-control @error('medical_allowance') is-invalid @enderror" 
                           id="medical_allowance" name="medical_allowance" value="{{ old('medical_allowance', $busHelper->medical_allowance) }}" min="0" step="0.01">
                    @error('medical_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="daily_salary_control" style="display: none;">
                    <label for="daily_salary" class="form-label">Daily Salary <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('daily_salary') is-invalid @enderror" 
                           id="daily_salary" name="daily_salary" value="{{ old('daily_salary', $busHelper->daily_salary ?? '') }}" min="0" step="0.01">
                    @error('daily_salary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                  <div class="col-md-3 mb-3" id="food_allowance_control">
                    <label for="food_allowance" class="form-label">Food Allowance</label>
                    <input type="number" class="form-control @error('food_allowance') is-invalid @enderror" id="food_allowance" name="food_allowance" value="{{ old('food_allowance', $busHelper->food_allowance) }}" min="0" step="0.01">
                    @error('food_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="other_allowance_control">
                    <label for="other_allowance" class="form-label">Other Allowance </label>
                    <input type="number" class="form-control @error('other_allowance') is-invalid @enderror" 
                           id="other_allowance" name="other_allowance" value="{{ old('other_allowance', $busHelper->other_allowance) }}" min="0" step="0.01">
                    @error('other_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="gross_salary_control">
                    <label for="gross_salary" class="form-label">Gross Salary (Auto-calculated) <span class="text-danger">*</span></label>
                    <input readonly type="number" class="form-control" id="gross_salary" name="gross_salary" required>
                </div>
            </div>

            <!-- Current Files Display -->
            @if($busHelper->picture || $busHelper->nid_copy)
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Current Files</h6>
                </div>
                @if($busHelper->picture)
                <div class="col-md-3 mb-3">
                    <label class="form-label">Current Profile Picture</label>
                    <div>
                        <img src="{{ asset('storage/' . $busHelper->picture) }}" alt="Current Picture" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                    </div>
                </div>
                @endif
                @if($busHelper->nid_copy)
                <div class="col-md-3 mb-3">
                    <label class="form-label">Current NID Copy</label>
                    <div>
                        <a href="{{ asset('storage/' . $busHelper->nid_copy) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-file-text me-1"></i>View NID Copy
                        </a>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- File Uploads -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-semibold mb-3">Update Files (Optional)</h6>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="picture" class="form-label">New Profile Picture</label>
                    <input type="file" class="form-control @error('picture') is-invalid @enderror" 
                           id="picture" name="picture" accept="image/*">
                    @error('picture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="nid_copy" class="form-label">New NID Copy</label>
                    <input type="file" class="form-control @error('nid_copy') is-invalid @enderror" 
                           id="nid_copy" name="nid_copy" accept=".pdf,.jpg,.jpeg,.png">
                    @error('nid_copy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('bus-helpers.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="ti ti-check me-1"></i>Update Bus Helper
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
        // Calculate gross salary based on employee type
         // Calculate gross salary based on employee type
            function calculateGrossSalary() {
                const empTypeText = $('#employee_type_id option:selected').text().trim().toLowerCase();
                if (empTypeText === 'daily') {
                    const dailySalary = parseFloat($('#daily_salary').val()) || 0;
                } else if (empTypeText === 'Contractual') {
                    const basic_salary = parseFloat($('#basic_salary').val()) || 0;
                    const foodAllowance = parseFloat($('#food_allowance').val()) || 0;
                    $('#gross_salary').val((basic_salary + foodAllowance).toFixed(2));
                } else {
                    const basicSalary = parseFloat($('#basic_salary').val()) || 0;
                    const houseRent = parseFloat($('#house_rent').val()) || 0;
                    const medicalAllowance = parseFloat($('#medical_allowance').val()) || 0;
                    const otherAllowance = parseFloat($('#other_allowance').val()) || 0;
                    $('#gross_salary').val((basicSalary + houseRent + medicalAllowance + otherAllowance).toFixed(
                    2));
                }
            }

            // Toggle which salary fields are visible & required depending on employee type
            function toggleSalaryFields() {
                const empTypeText = $('#employee_type_id option:selected').text().trim().toLowerCase();
                const isDaily = empTypeText === 'daily';
                const isContractual = empTypeText === 'contractual';

                // Controls
                const basicControl = $('#basic_salary_control');
                const houseControl = $('#house_rent_control');
                const medicalControl = $('#medical_allowance_control');
                const otherControl = $('#other_allowance_control');
                const dailyControl = $('#daily_salary_control');
                const foodControl = $('#food_allowance_control');
                const grossControl = $('#gross_salary_control');

                if (isDaily) {
                    // Show daily-specific controls
                    dailyControl.show();
                    foodControl.hide();
                    // Hide monthly/basic controls
                    basicControl.hide();
                    houseControl.hide();
                    medicalControl.hide();
                    otherControl.hide();
                    grossControl.hide();

                    // Set required attributes
                    $('#daily_salary').prop('required', true);
                    $('#food_allowance').prop('required', true);

                    $('#basic_salary').prop('required', false).val('');
                    $('#house_rent').prop('required', false).val('');
                    $('#medical_allowance').prop('required', false).val('');
                    $('#other_allowance').prop('required', false).val('');
                } else if (isContractual) {
                    
                    // Show contractual controls
                    basicControl.show();
                    foodControl.show();
                    grossControl.show();

                    // Hide daily-specific controls
                    dailyControl.hide();
                    houseControl.hide();
                    medicalControl.hide();
                    otherControl.hide();

                    // Set required attributes
                    $('#basic_salary').prop('required', true);
                    $('#food_allowance').prop('required', true);

                    $('#daily_salary').prop('required', false).val('');
                } else {
                    // Show basic/monthly controls
                    basicControl.show();
                    houseControl.show();
                    medicalControl.show();
                    otherControl.show();
                    // Hide daily-specific controls
                    dailyControl.hide();
                    foodControl.hide();

                    // Set required attributes
                    $('#basic_salary').prop('required', true);

                    $('#daily_salary').prop('required', false).val('');
                    $('#food_allowance').prop('required', false).val('');
                }

                // Always ensure gross salary is visible (it's present in the form)
                calculateGrossSalary();
            }

        // Initial calculation and toggle on page load
        toggleSalaryFields();
        calculateGrossSalary();

        // Bind events for inputs to recalc gross
        $('#basic_salary, #house_rent, #medical_allowance, #other_allowance, #daily_salary, #food_allowance').on('input', calculateGrossSalary);

        // When employee type changes
        $('#employee_type_id').on('change', function() {
            toggleSalaryFields();
        });

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
        $('#busHelperForm').on('submit', function(e) {
            e.preventDefault();
            hideAlert();
            
            // Clear previous validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            
            toggleFormState(true);

            let formData = new FormData(this);
            // Add _method for PUT request
            formData.append('_method', 'PUT');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    toggleFormState(false);
                    if (response.success) {
                        toastr.success(response.message || 'Bus Helper updated successfully!');
                        setTimeout(() => {
                            window.location.href = '{{ route("bus-helpers.index") }}';
                        }, 1500);
                    } else {
                        toastr.error(response.message || 'Failed to update bus helper.');
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
        $('#busHelperForm input, #busHelperForm select, #busHelperForm textarea').on('input change', function() {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        });
    });
</script>
@endsection
