@extends('layouts/layoutMaster')

@section('title', 'Add Punishment')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Add New Punishment Record</h5>
    </div>
    <div class="card-body">
        <form id="punishment-form" action="{{ route('punishments.store') }}" method="POST" enctype="multipart/form-data">
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
                    <select name="bus_sub_type_id" id="bus_sub_type_id" class="select2 form-select">
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
               
                <div class="col-md-3">
                    <label for="punishment_date" class="form-label">Punishment Date <span class="text-danger">*</span></label>
                    <input type="date" name="punishment_date" id="punishment_date" class="form-control @error('punishment_date') is-invalid @enderror"
                        value="{{ old('punishment_date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <!-- Punishment Details -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-alert-triangle me-2"></i>Punishment Details
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="punishment_type_id" class="form-label">Punishment Type <span class="text-danger">*</span></label>
                    <select name="punishment_type_id" id="punishment_type_id" class="select2 form-select" required>
                        <option value="">Select Punishment Type</option>
                        @foreach($punishmentTypes as $value)
                        <option value="{{ $value->id }}" {{ old('punishment_type_id') == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="violation_type_id" class="form-label">Violation Type <span class="text-danger">*</span></label>
                    <select name="violation_type_id" id="violation_type_id" class="select2 form-select" required>
                        <option value="">Select Violation Type</option>
                        @foreach($violationTypes as $violationType)
                        <option value="{{ $violationType->id }}" {{ old('violation_type_id') == $violationType->id ? 'selected' : '' }}>
                            {{ $violationType->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="suspension_days" class="form-label">Suspension Days</label>
                    <input type="number" min="1" name="suspension_days" id="suspension_days"
                        class="form-control" value="{{ old('suspension_days') }}" placeholder="0">
                </div>

                <div class="col-md-3">
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

                <div class="col-md-3">
                    <label for="fine_amount" class="form-label">Fine Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" min="0" name="fine_amount" id="fine_amount"
                            class="form-control" value="{{ old('fine_amount') }}" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" rows="4" class="form-control" required>{{ old('description') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="3" class="form-control">{{ old('remarks') }}</textarea>
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
                    <input type="file" name="document" id="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Max size: 5MB, Formats: PDF, JPEG, PNG, JPG</div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('punishments.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                        <button type="submit" id="submit-btn" class="btn btn-primary">
                            <span class="btn-text">
                                <i class="ti ti-check me-1"></i>Create Punishment
                            </span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Saving...
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

        // Initialize Select2
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: $('.card-body')
            });
        }

        // Initialize Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#punishment_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                defaultDate: 'today'
            });
        }

        const form = document.getElementById('punishment-form');

        const fv = FormValidation.formValidation(form, {
            fields: {
                bus_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a bus'
                        }
                    }
                },
                punishment_date: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a punishment date'
                        }
                    }
                },
                punishment_type_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a punishment type'
                        }
                    }
                },
                violation_type_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a violation type'
                        }
                    }
                },
                description: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter a description'
                        },
                        stringLength: {
                            min: 10,
                            message: 'Description must be at least 10 characters long'
                        }
                    }
                },
                fine_amount: {
                    validators: {
                        numeric: {
                            message: 'Please enter a valid amount'
                        },
                        greaterThan: {
                            min: 0,
                            message: 'Amount must be greater than or equal to 0'
                        }
                    }
                },
                document: {
                    validators: {
                        file: {
                            extension: 'pdf,jpg,jpeg,png',
                            type: 'application/pdf,image/jpeg,image/png',
                            maxSize: 5 * 1024 * 1024,
                            message: 'Valid file types: PDF, JPG, PNG (max 5MB)'
                        }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: '.col-md-3, .col-md-6'
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        });

        // Handle form submit when valid
        fv.on('core.form.valid', function() {
            submitForm();
        });

        function submitForm() {
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submit-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnSpinner = submitBtn.querySelector('.btn-spinner');

            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            submitBtn.disabled = true;

            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Punishment record created successfully!');
                    form.reset();
                    $('.select2').val(null).trigger('change');

                    setTimeout(function() {
                        window.location.href = '{{ route("punishments.index") }}';
                    }, 1500);
                },
                error: function(xhr) {
                    let message = 'An error occurred while saving the punishment.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                },
                complete: function() {
                    btnText.classList.remove('d-none');
                    btnSpinner.classList.add('d-none');
                    submitBtn.disabled = false;
                }
            });
        }

        // File validation
        $('#document').on('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 5 * 1024 * 1024;
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                if (file.size > maxSize) {
                    toastr.error('File size must be less than 5MB');
                    this.value = '';
                } else if (!allowedTypes.includes(file.type)) {
                    toastr.error('Invalid file type. Allowed: PDF, JPG, PNG');
                    this.value = '';
                }
            }
        });

        // Prevent negative values
        $('#fine_amount, #suspension_days').on('input', function() {
            const value = parseFloat($(this).val());
            if (value < 0) $(this).val(0);
        });

        // Load buses when bus sub type changes
        $('#bus_sub_type_id').on('change', function() {
            const subTypeId = $(this).val();
            const busSelect = $('#bus_id');

            if (subTypeId) {
                busSelect.prop('disabled', true).html('<option value="">Loading buses...</option>');

                $.ajax({
                    url: '{{ route("punishments.get-buses-by-subtype") }}',
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
                        busSelect.prop('disabled', false).trigger('change');
                    },
                    error: function() {
                        toastr.error('Failed to load buses.');
                        busSelect.html('<option value="">Select Bus</option>').prop('disabled', false);
                    }
                });
            } else {
                busSelect.empty().append('<option value="">Select Bus</option>').prop('disabled', false);
            }
        });
    });
</script>
@endsection