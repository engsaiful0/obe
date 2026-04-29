@extends('layouts/layoutMaster')

@section('title', 'Add Bus Helper')

@section('page-style')
    <link rel="stylesheet" href="{{ asset('assets/css/bus-helper-form-ajax.css') }}" />
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Add New Bus Helper</h5>
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

            <form action="{{ route('bus-helpers.store') }}" method="POST" enctype="multipart/form-data" id="busHelperForm">
                @csrf
                <!-- Personal Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-semibold mb-3">Personal Information</h6>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="assistant_name" class="form-label">Bus Helper Name <span
                                class="text-danger">*</span></label>
                        <input type="text" autofocus class="form-control" id="bus_helper_name" name="bus_helper_name"
                            value="{{ old('bus_helper_name') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="bus_helper_unique_id" class="form-label">Bus Helper ID</label>
                        <input type="text" id="bus_helper_unique_id" name="bus_helper_unique_id" class="form-control"
                            placeholder="Enter ID" />
                        <input type="hidden" id="serial" name="serial" value="{{ $nextSerial }}" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="father_name" class="form-label">Father's Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="father_name" name="father_name"
                            value="{{ old('father_name') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="mother_name" class="form-label">Mother's Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mother_name" name="mother_name"
                            value="{{ old('mother_name') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile') }}"
                            required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="gender_id" class="form-label">Gender <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="gender_id" name="gender_id" required>
                            <option value="">Select Gender</option>
                            @foreach ($genders as $gender)
                                <option value="{{ $gender->id }}" {{ old('gender_id') == $gender->id ? 'selected' : '' }}>
                                    {{ $gender->gender_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="marital_status_id" class="form-label">Marital Status <span
                                class="text-danger">*</span></label>
                        <select class="form-select select2" id="marital_status_id" name="marital_status_id" required>
                            <option value="">Select Marital Status</option>
                            @foreach ($maritalStatuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ old('marital_status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->marital_status_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="religion_id" class="form-label">Religion <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="religion_id" name="religion_id" required>
                            <option value="">Select Religion</option>
                            @foreach ($religions as $religion)
                                <option value="{{ $religion->id }}"
                                    {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                    {{ $religion->religion_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="nid_number" class="form-label">NID Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nid_number" name="nid_number"
                            value="{{ old('nid_number') }}" required>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="row mb-4">
                    <div class="col-6 mb-3">
                        <label for="present_address" class="form-label">Present Address</label>
                        <textarea class="form-control" id="present_address" name="present_address" rows="3">{{ old('present_address') }}</textarea>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="permanent_address" class="form-label">Permanent Address</label>
                        <textarea class="form-control" id="permanent_address" name="permanent_address" rows="3">{{ old('permanent_address') }}</textarea>
                    </div>
                </div>

                <!-- Academic & Experience -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-semibold mb-3">Academic & Experience Information</h6>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="academic_qualification" class="form-label">Academic Qualification</label>
                        <input type="text" class="form-control" id="academic_qualification"
                            name="academic_qualification" value="{{ old('academic_qualification') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="years_of_experience" class="form-label">Years of Experience</label>
                        <input type="number" class="form-control" id="years_of_experience" name="years_of_experience"
                            value="{{ old('years_of_experience') }}" min="0">
                    </div>
                </div>

                <!-- Employment -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-semibold mb-3">Employment Information</h6>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="employee_type_id" class="form-label">Employee Type <span
                                class="text-danger">*</span></label>
                        <select class="form-select select2" id="employee_type_id" name="employee_type_id" required>
                            <option value="">Select Employee Type</option>
                            @foreach ($employeeTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('employee_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->employee_type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="status_id" name="status_id" required>
                            @foreach ($statusOptions as $value)
                                <option value="{{ $value->id }}"
                                    {{ old('status_id') == $value->id ? 'selected' : '' }}>{{ $value->status_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-semibold mb-3">Salary Information</h6>
                    </div>
                    <div class="col-md-3 mb-3" id="basic_salary_control">
                        <label for="basic_salary" class="form-label">Basic Salary <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="basic_salary" name="basic_salary" required>
                    </div>
                    <div class="col-md-3 mb-3" id="daily_salary_control">
                        <label for="daily_salary" class="form-label">Daily Salary <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="daily_salary" name="daily_salary" required>
                    </div>
                    <div class="col-md-3 mb-3" id="house_rent_control">
                        <label for="house_rent" class="form-label">House Rent</label>
                        <input type="number" class="form-control" id="house_rent" name="house_rent">
                    </div>
                    <div class="col-md-3 mb-3" id="medical_allowance_control">
                        <label for="medical_allowance" class="form-label">Medical Allowance</label>
                        <input type="number" class="form-control" id="medical_allowance" name="medical_allowance">
                    </div>

                    <div class="col-md-3 mb-3" id="food_allowance_control">
                        <label for="food_allowance" class="form-label">Food Allowance</label>
                        <input type="number" class="form-control" id="food_allowance" name="food_allowance">
                    </div>
                    <div class="col-md-3 mb-3" id="other_allowance_control">
                        <label for="other_allowance" class="form-label">Other Allowance</label>
                        <input type="number" class="form-control" id="other_allowance" name="other_allowance">
                    </div>
                    <div class="col-md-3 mb-3" id="gross_salary_control">
                        <label for="gross_salary" class="form-label">Gross Salary (Auto-calculated) <span
                                class="text-danger">*</span></label>
                        <input readonly type="number" class="form-control" id="gross_salary" name="gross_salary"
                            required>
                    </div>
                </div>

                <!-- File Uploads -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label for="picture" class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="nid_copy" class="form-label">NID Copy</label>
                        <input type="file" class="form-control" id="nid_copy" name="nid_copy"
                            accept=".pdf,.jpg,.jpeg,.png">
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
                                <i class="ti ti-check me-1"></i>Create Bus Helper
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"
                                    id="loadingSpinner">
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
            function calculateGrossSalary() {
                const empTypeText = $('#employee_type_id option:selected').text().trim().toLowerCase();
                if (empTypeText === 'daily') {
                    const dailySalary = parseFloat($('#daily_salary').val()) || 0;
                  
                } else if (empTypeText === 'contractual') {
                    const basic_salary = parseFloat($('#basic_salary').val()) || 0;
                    const foodAllowance = parseFloat($('#food_allowance').val()) || 0;
                    $('#gross_salary').val((basic_salary + foodAllowance).toFixed(2));
                } else {
                    const basicSalary = parseFloat($('#basic_salary').val()) || 0;
                    const houseRent = parseFloat($('#house_rent').val()) || 0;
                    const medicalAllowance = parseFloat($('#medical_allowance').val()) || 0;
                    const otherAllowance = parseFloat($('#other_allowance').val()) || 0;
                    $('#gross_salary').val((basicSalary + houseRent + medicalAllowance + otherAllowance).toFixed(2));
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

                // Reset visibility / requirements first
                basicControl.show();
                houseControl.show();
                medicalControl.show();
                otherControl.show();
                dailyControl.show();
                foodControl.show();
                grossControl.show();

                if (isDaily) {
                    // Show daily-specific controls and gross
                    dailyControl.show();
                    foodControl.hide();
                    grossControl.hide();

                    // Hide monthly/basic controls
                    basicControl.hide();
                    houseControl.hide();
                    medicalControl.hide();
                    otherControl.hide();

                    // Required attributes for visible fields
                    $('#daily_salary').prop('required', true);
                    $('#food_allowance').prop('required', false);

                    // Clear & unset required for hidden fields
                    $('#basic_salary').prop('required', false).val('');
                    $('#house_rent').prop('required', false).val('');
                    $('#medical_allowance').prop('required', false).val('');
                    $('#other_allowance').prop('required', false).val('');
                } else if (isContractual) {
                    // Show contractual controls: basic, food, gross
                    basicControl.show();
                    foodControl.show();
                    grossControl.show();

                    // Hide others
                    dailyControl.hide();
                    houseControl.hide();
                    medicalControl.hide();
                    otherControl.hide();

                    // Required on basic and food
                    $('#basic_salary').prop('required', true);
                    $('#food_allowance').prop('required', true);

                    // Clear & unset daily
                    $('#daily_salary').prop('required', false).val('');
                    $('#house_rent').prop('required', false).val('');
                    $('#medical_allowance').prop('required', false).val('');
                    $('#other_allowance').prop('required', false).val('');
                } else {
                    // Default (monthly/other): show basic components + gross
                    basicControl.show();
                    houseControl.show();
                    medicalControl.show();
                    otherControl.show();
                    foodControl.hide(); // hide food for generic types unless contractual
                    dailyControl.hide();
                    grossControl.show();

                    // Required settings
                    $('#basic_salary').prop('required', true);

                    // Clear & unset not used
                    $('#daily_salary').prop('required', false).val('');
                    $('#food_allowance').prop('required', false).val('');
                }

                // Recalculate gross after toggling
                calculateGrossSalary();
            }

            // Show alert
            function showAlert(type, message) {
                $('#alertContainer').removeClass('d-none').addClass('d-block');
                $('#alertMessage').removeClass().addClass(`alert alert-${type} alert-dismissible fade show`);
                $('#alertText').text(message);
                $('#alertIcon').removeClass().addClass(type === 'success' ? 'ti ti-check-circle me-2' :
                    'ti ti-alert-circle me-2');
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

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toggleFormState(false);
                        if (response.success) {
                            toastr.success(response.message ||
                                'Bus Helper created successfully!');
                            setTimeout(() => {
                                window.location.href =
                                    '{{ route('bus-helpers.index') }}';
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Failed to create bus helper.');
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
                                    fieldElement.after(
                                        `<div class="invalid-feedback">${errors[field][0]}</div>`
                                        );
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
                            const errorMessage = xhr.responseJSON?.message ||
                                'An error occurred. Please try again.';
                            toastr.error(errorMessage);
                            console.error('AJAX Error:', xhr);
                        }
                    }
                });
            });


            // Remove invalid class on input/change
            $('#busHelperForm input, #busHelperForm select, #busHelperForm textarea').on('input change',
        function() {
                $(this).removeClass('is-invalid');
            });

            // Bind events for salary inputs to recalc gross
            $('#basic_salary, #house_rent, #medical_allowance, #other_allowance, #daily_salary, #food_allowance')
                .on('input', calculateGrossSalary);

            // On employee type change toggle fields
            $('#employee_type_id').on('change', function() {
                toggleSalaryFields();
            });

            // Initialize on page load
            toggleSalaryFields();
        });
    </script>
@endsection
