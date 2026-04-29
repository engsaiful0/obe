@extends('layouts/layoutMaster')

@section('title', 'Edit Punishment')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Edit Punishment Record</h5>
    </div>
    <div class="card-body">
        <form id="punishment-form" action="{{ route('punishments.update', $punishment->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
                            <option value="{{ $subType->id }}" data-name="{{ $subType->sub_type_name }}" {{ old('bus_sub_type_id', $punishment->bus->bus_sub_type_id ?? null) == $subType->id ? 'selected' : '' }}>
                                {{ $subType->sub_type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="bus_id" class="form-label">Bus <span class="text-danger">*</span></label>
                    <select name="bus_id" id="bus_id" class="select2 form-select" required>
                        <option value="">Select Bus</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" {{ old('bus_id', $punishment->bus_id) == $bus->id ? 'selected' : '' }}>
                                {{ $bus->bus_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

               

                <div class="col-md-3">
                    <label for="punishment_date" class="form-label">Punishment Date <span class="text-danger">*</span></label>
                    <input type="date" name="punishment_date" id="punishment_date" class="form-control"
                           value="{{ old('punishment_date', $punishment->punishment_date->format('Y-m-d')) }}" required>
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
                        <option value="">Select Type</option>
                        @foreach($punishmentTypes as $type)
                            <option value="{{ $type->id }}" {{ old('punishment_type_id', $punishment->punishment_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="violation_type_id" class="form-label">Violation Type <span class="text-danger">*</span></label>
                    <select name="violation_type_id" id="violation_type_id" class="select2 form-select" required>
                        <option value="">Select Violation</option>
                        @foreach($violationTypes as $v)
                            <option value="{{ $v->id }}" {{ old('violation_type_id', $punishment->violation_type_id) == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="suspension_days" class="form-label">Suspension Days</label>
                    <input type="number" name="suspension_days" id="suspension_days" class="form-control"
                           value="{{ old('suspension_days', $punishment->suspension_days) }}" min="0">
                </div>

                <div class="col-md-3">
                    <label for="fine_amount" class="form-label">Fine Amount</label>
                    <input type="number" step="0.01" name="fine_amount" id="fine_amount" class="form-control"
                           value="{{ old('fine_amount', $punishment->fine_amount) }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description', $punishment->description) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" class="form-control" rows="4">{{ old('remarks', $punishment->remarks) }}</textarea>
                </div>
            </div>

            <!-- Supporting Document -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    @if($punishment->document_path)
                        <a href="{{ asset('storage/'.$punishment->document_path) }}" target="_blank" class="btn btn-outline-primary mb-2">
                            <i class="ti ti-file-text me-1"></i>View Current Document
                        </a>
                    @endif
                    <input type="file" name="document" id="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Max 5MB (PDF/JPG/PNG)</div>
                </div>
            </div>

            <!-- Actions -->
            <div class="text-end">
                <a href="{{ route('punishments.index') }}" class="btn btn-secondary me-2">
                    <i class="ti ti-x me-1"></i>Cancel
                </a>
                <button type="submit" id="submit-btn" class="btn btn-primary">
                    <span class="btn-text"><i class="ti ti-check me-1"></i>Update</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Updating...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


@section('page-script')
<script>
$(document).ready(function () {
    // Initialize Select2 & Flatpickr
    $('.select2').select2({ dropdownParent: $('.card-body') });
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#punishment_date', { dateFormat: 'Y-m-d' });
    }

    const form = document.getElementById('punishment-form');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnSpinner = submitBtn.querySelector('.btn-spinner');

    // Setup FormValidation
    const fv = FormValidation.formValidation(form, {
        fields: {
            bus_id: { validators: { notEmpty: { message: 'Select bus' } } },
            punishment_date: { validators: { notEmpty: { message: 'Select date' } } },
            punishment_type_id: { validators: { notEmpty: { message: 'Select punishment type' } } },
            violation_type_id: { validators: { notEmpty: { message: 'Select violation type' } } },
            description: { validators: { notEmpty: { message: 'Enter description' } } },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '' }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
            submitButton: new FormValidation.plugins.SubmitButton()
        },
    });

    // Prevent native submit — always. Then manually validate.
    form.addEventListener('submit', function(e) {
        e.preventDefault();    // <- IMPORTANT: stop native navigation
        // trigger validation; when valid, fv will fire core.form.valid
        fv.validate();
    });

    // When validation passes, do AJAX update
    fv.on('core.form.valid', function () {
        const formData = new FormData(form);

        // Ensure method override for Laravel PUT
        if (!formData.has('_method')) {
            formData.append('_method', 'PUT');
        }

        // UI: disable + show spinner
        disableForm(true);

        $.ajax({
            url: form.action,
            type: 'POST', // use POST with method override
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                toastr.success(response.message || 'Punishment updated successfully!');
                // Optionally redirect after a short delay
                setTimeout(function() {
                    window.location.href = '{{ route("punishments.index") }}';
                }, 900);
            },
            error: function (xhr) {
                // If validation errors returned, show them inline (simple approach)
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    // Clear existing invalid states
                    $(form).find('.is-invalid').removeClass('is-invalid');
                    $(form).find('.invalid-feedback').remove();

                    Object.keys(errors).forEach(function (field) {
                        const el = form.querySelector('[name="'+field+'"]');
                        if (el) {
                            $(el).addClass('is-invalid');
                            // append small invalid feedback (Bootstrap)
                            const msg = document.createElement('div');
                            msg.className = 'invalid-feedback';
                            msg.innerText = errors[field][0];
                            if (el.parentElement) {
                                el.parentElement.appendChild(msg);
                            } else {
                                el.insertAdjacentElement('afterend', msg);
                            }
                        }
                    });
                    toastr.error('Please fix the highlighted errors and try again.');
                } else {
                    const msg = xhr.responseJSON?.message || 'Update failed! Please try again.';
                    toastr.error(msg);
                }
            },
            complete: function () {
                // Re-enable UI
                disableForm(false);
            }
        });
    });

    // Utility to disable/enable form and toggle spinner
    function disableForm(disabled) {
        $(form).find('input, select, textarea, button').prop('disabled', disabled);
        btnText.classList.toggle('d-none', disabled);
        btnSpinner.classList.toggle('d-none', !disabled);
    }

    // Load buses when bus sub type changes
    $('#bus_sub_type_id').on('change', function() {
        const subTypeId = $(this).val();
        const busSelect = $('#bus_id');
        const currentBusId = '{{ old("bus_id", $punishment->bus_id) }}';

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
                            const selected = (currentBusId && bus.id == currentBusId) ? 'selected' : '';
                            busSelect.append(`<option value="${bus.id}" ${selected}>${displayText}</option>`);
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

    // Load buses on page load if bus_sub_type_id is already selected
    var initialSubTypeId = $('#bus_sub_type_id').val();
    if (initialSubTypeId) {
        $('#bus_sub_type_id').trigger('change');
    }
});
</script>
@endsection

