@extends('layouts/layoutMaster')

@section('title', 'Edit Trip')

@section('page-style')
<style>
    .form-control:disabled,
    .form-select:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
    }

    .conditional-field {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Edit Trip</h5>
    </div>
    <div class="card-body position-relative"
        data-buses='@json($busOptions)'
        data-bus-subtype="{{ $busTrip->bus->busSubType->sub_type_name }}">
        <!-- Alert Messages -->
        <div id="alertContainer" class="d-none">
            <div class="alert alert-dismissible fade show" role="alert" id="alertMessage">
                <i class="me-2" id="alertIcon"></i>
                <span id="alertText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>

        <form action="{{ route('bus-trips.update', $busTrip) }}" method="POST" id="busTripForm">
            @csrf
            @method('PUT')

            <!-- Bus & Date Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-bus me-2"></i>Bus & Trip Information
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="bus_sub_type_id" class="form-label">Sub Type</label>
                        <select class="form-select select2" id="bus_sub_type_id" name="bus_sub_type_id">
                            <option value="">Select Bus Sub Type</option>
                            @foreach ($busSubTypes as $busSubType)
                            <option value="{{ $busSubType->id }}" {{ old('bus_sub_type_id', $busTrip->bus_sub_type_id) == $busSubType->id ? 'selected' : '' }}>{{ $busSubType->sub_type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="bus_id" class="form-label">Bus <span class="text-danger">*</span></label>
                    <select name="bus_id" id="bus_id" class="select2 form-select @error('bus_id') is-invalid @enderror" required>
                        <option value="">Select Vehicle</option>
                        @foreach($buses as $bus)
                        <option value="{{ $bus->id }}"
                            data-subtype="{{ $bus->busSubType->sub_type_name ?? '' }}"
                            {{ old('bus_id', $busTrip->bus_id) == $bus->id ? 'selected' : '' }}>
                            {{ $bus->registration_number }} - {{ $bus->model_name }}
                            ({{ $bus->busSubType->sub_type_name ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" id="vehicleInfo"></div>
                </div>

                <div class="col-md-3">
                    <label for="alternate_driver_id" class="form-label">Alternate Driver</label>
                    <select name="alternate_driver_id" id="alternate_driver_id" class="select2 form-select @error('alternate_driver_id') is-invalid @enderror">
                        <option value="">Select Driver</option>
                        @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ old('alternate_driver_id', $busTrip->alternate_driver_id) == $driver->id ? 'selected' : '' }}>
                            {{ $driver->full_name }} ({{ $driver->driver_unique_id ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="alternate_bus_helper_id" class="form-label">Alternate Bus Helper</label>
                    <select name="alternate_bus_helper_id" id="alternate_bus_helper_id" class="select2 form-select @error('alternate_bus_helper_id') is-invalid @enderror">
                        <option value="">Select Bus Helper</option>
                        @foreach($busHelpers as $busHelper)
                        <option value="{{ $busHelper->id }}" {{ old('alternate_bus_helper_id', $busTrip->alternate_bus_helper_id) == $busHelper->id ? 'selected' : '' }}>
                            {{ $busHelper->bus_helper_name }} ({{ $busHelper->bus_helper_id ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                    @error('bus_helper_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="attendance_date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="trip_date" id="trip_date"
                        class="form-control @error('trip_date') is-invalid @enderror"
                        value="{{ old('trip_date', $busTrip->trip_date->format('Y-m-d')) }}" required>
                    @error('trip_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="trip_type" class="form-label">Trip Type <span class="text-danger">*</span></label>
                    {{-- trip_type dropdown intentionally not enhanced with Select2 --}}
                    <select name="trip_type" id="trip_type" class="form-select @error('trip_type') is-invalid @enderror" required>
                        <option value="">Select Trip Type</option>
                        <option value="in" {{ old('trip_type', $busTrip->trip_type) == 'in' ? 'selected' : '' }}>In</option>
                        <option value="out" {{ old('trip_type', $busTrip->trip_type) == 'out' ? 'selected' : '' }}>Out</option>
                    </select>
                    @error('trip_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="trip_type" class="form-label">No. of Passengers <span class="text-danger">*</span></label>
                    <input type="number" name="passengers" id="passengers" min="0" class="form-control @error('passengers') is-invalid @enderror" value="{{ old('passengers', $busTrip->passengers) }}" placeholder="0">
                    @error('passengers')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Stoppage Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-map-pin me-2"></i>Route Information
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="start_stoppage_id" class="form-label">Start Stoppage <span class="text-danger">*</span></label>
                    <select name="start_stoppage_id" id="start_stoppage_id" class="select2 form-select @error('start_stoppage_id') is-invalid @enderror" required>
                        <option value="">Select Start Stoppage</option>
                        @foreach($stoppages as $stoppage)
                        <option value="{{ $stoppage->id }}" {{ old('start_stoppage_id', $busTrip->start_stoppage_id) == $stoppage->id ? 'selected' : '' }}>
                            {{ $stoppage->stoppage_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('start_stoppage_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="end_stoppage_id" class="form-label">End Stoppage <span class="text-danger">*</span></label>
                    <select name="end_stoppage_id" id="end_stoppage_id" class="select2 form-select @error('end_stoppage_id') is-invalid @enderror" required>
                        <option value="">Select End Stoppage</option>
                        @foreach($stoppages as $stoppage)
                        <option value="{{ $stoppage->id }}" {{ old('end_stoppage_id', $busTrip->end_stoppage_id) == $stoppage->id ? 'selected' : '' }}>
                            {{ $stoppage->stoppage_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('end_stoppage_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @php
            $inTime = optional($busTrip->in_time);
            $inHours = old('in_time_hours', $inTime ? $inTime->format('g') : '');
            $inMinutes = old('in_time_minutes', $inTime ? $inTime->format('i') : '');
            $inAmPm = old('in_time_am_pm', $inTime ? strtolower($inTime->format('A')) : '');

            $outTime = optional($busTrip->out_time);
            $outHours = old('out_time_hours', $outTime ? $outTime->format('g') : '');
            $outMinutes = old('out_time_minutes', $outTime ? $outTime->format('i') : '');
            $outAmPm = old('out_time_am_pm', $outTime ? strtolower($outTime->format('A')) : '');
            @endphp

            <!-- Time Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-clock me-2"></i>Time & Distance Information
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4 conditional-field" id="inTimeField">
                    <label class="form-label">In Time <span class="text-danger">*</span></label>
                    <div class="d-flex gap-1 align-items-center time-select-group">
                        <select name="in_time_hours" id="in_time_hours" class="form-select form-select-sm" style="width: auto;">
                            <option value="">HH</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ (string)$i === (string)$inHours ? 'selected' : '' }}>
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                </option>
                                @endfor
                        </select>

                        <select name="in_time_minutes" id="in_time_minutes" class="form-select form-select-sm" style="width: auto;">
                            <option value="">MM</option>
                            @for($i = 0; $i <= 59; $i++)
                                @php $min=str_pad($i, 2, '0' , STR_PAD_LEFT); @endphp
                                <option value="{{ $min }}" {{ $min === $inMinutes ? 'selected' : '' }}>{{ $min }}</option>
                                @endfor
                        </select>

                        <select name="in_time_am_pm" id="in_time_am_pm" class="form-select form-select-sm" style="width: auto;">
                            <option value="">AM/PM</option>
                            <option value="am" {{ $inAmPm === 'am' ? 'selected' : '' }}>AM</option>
                            <option value="pm" {{ $inAmPm === 'pm' ? 'selected' : '' }}>PM</option>
                        </select>
                    </div>
                    <input type="hidden" name="in_time" id="in_time" value="{{ old('in_time', $inTime ? $inTime->format('H:i') : '') }}">
                    @error('in_time')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 conditional-field" id="outTimeField">
                    <label class="form-label">Out Time <span class="text-danger">*</span></label>
                    <div class="d-flex gap-1 align-items-center time-select-group">
                        <select name="out_time_hours" id="out_time_hours" class="form-select form-select-sm" style="width: auto;">
                            <option value="">HH</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ (string)$i === (string)$outHours ? 'selected' : '' }}>
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                </option>
                                @endfor
                        </select>

                        <select name="out_time_minutes" id="out_time_minutes" class="form-select form-select-sm" style="width: auto;">
                            <option value="">MM</option>
                            @for($i = 0; $i <= 59; $i++)
                                @php $min=str_pad($i, 2, '0' , STR_PAD_LEFT); @endphp
                                <option value="{{ $min }}" {{ $min === $outMinutes ? 'selected' : '' }}>{{ $min }}</option>
                                @endfor
                        </select>

                        <select name="out_time_am_pm" id="out_time_am_pm" class="form-select form-select-sm" style="width: auto;">
                            <option value="">AM/PM</option>
                            <option value="am" {{ $outAmPm === 'am' ? 'selected' : '' }}>AM</option>
                            <option value="pm" {{ $outAmPm === 'pm' ? 'selected' : '' }}>PM</option>
                        </select>
                    </div>
                    <input type="hidden" name="out_time" id="out_time" value="{{ old('out_time', $outTime ? $outTime->format('H:i') : '') }}">
                    @error('out_time')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 conditional-field" id="distanceField">
                    <label for="total_distance" class="form-label">Total Distance (KM) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" name="total_distance" id="total_distance"
                            class="form-control @error('total_distance') is-invalid @enderror"
                            value="{{ old('total_distance', $busTrip->total_distance) }}" placeholder="0.00">
                        <span class="input-group-text">KM</span>
                    </div>
                    @error('total_distance')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Required for BRTC Hired Bus</div>
                </div>
            </div>

            <!-- Remarks -->
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="3"
                        class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $busTrip->remarks) }}</textarea>
                    @error('remarks')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('bus-trips.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="ti ti-check me-1"></i>Update Trip
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
    function fetchDistanceForStoppages(stoppageId) {
        const $distanceInput = $('#total_distance');

        // Only proceed if stoppage is selected and distance input exists (BRTC only)
        if (!stoppageId || !$distanceInput.length) {
            // Clear distance if stoppage is not selected
            if ($distanceInput.length) {
                $distanceInput.val('');
            }
            return;
        }

        // Make AJAX request to get distance from stoppages table
        $.ajax({
            url: '{{ route("app-settings-stoppage.get-distance") }}',
            method: 'GET',
            data: {
                stoppage_id: stoppageId
            },
            success: function(response) {
                if (response.success && response.distance) {
                    $distanceInput.val(response.distance);
                } else {
                    $distanceInput.val('');
                }
            },
            error: function(xhr) {
                // If distance not found, clear the field
                $distanceInput.val('');
            }
        });
    }

    $(document).ready(function() {
        // Vehicle Sub Type Change Event
        $('#bus_sub_type_id').on('change', function() {
            const subTypeId = $(this).val();
            const busSelect = $('#bus_id');

            if (subTypeId) {
                busSelect.prop('disabled', true).html('<option value="">Loading buses...</option>');

                $.ajax({
                    url: '{{ route("bus-trips.get-buses-names-by-subtype") }}',
                    type: 'GET',
                    data: {
                        sub_type_id: subTypeId
                    },
                    success: function(response) {
                        busSelect.empty().append('<option value="">Select Bus</option>');
                        if (response.success && response.buses && response.buses.length > 0) {
                            $.each(response.buses, function(index, bus) {
                                busSelect.append(`<option value="${bus.id}">${bus.model_name} (${bus.registration_number})</option>`);
                            });
                        } else {
                            busSelect.append('<option value="">No buses found</option>');
                        }
                        busSelect.prop('disabled', false);
                    },
                    error: function() {
                        toastr.error('Failed to load buses.');
                        busSelect.prop('disabled', false);
                    }
                });
            } else {
                busSelect.empty().append('<option value="">Select Bus</option>');
                presetBuses.forEach(function(bus) {
                    busSelect.append(`<option value="${bus.id}">${bus.model_name} (${bus.registration_number})</option>`);
                });
            }
        });

        const cardBody = $('.card-body').first();
        const presetBuses = JSON.parse(cardBody.attr('data-buses') || '[]');
        const submitBtn = $('#submitBtn');
        const loadingSpinner = $('#loadingSpinner');
        let busSubType = cardBody.attr('data-bus-subtype') || '';

        function convertTo24Hour(hours, minutes, amPm) {
            if (!hours || !minutes || !amPm) {
                return '';
            }

            let h = parseInt(hours, 10);
            if (amPm === 'pm' && h < 12) {
                h += 12;
            }
            if (amPm === 'am' && h === 12) {
                h = 0;
            }

            return `${String(h).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        }

        function updateTimeHidden(prefix) {
            const hours = $(`#${prefix}_time_hours`).val();
            const minutes = $(`#${prefix}_time_minutes`).val();
            const amPm = $(`#${prefix}_time_am_pm`).val();
            const value = convertTo24Hour(hours, minutes, amPm);
            $(`#${prefix}_time`).val(value);
            return value;
        }

        $('#in_time_hours, #in_time_minutes, #in_time_am_pm').on('change', function() {
            updateTimeHidden('in');
        });

        $('#out_time_hours, #out_time_minutes, #out_time_am_pm').on('change', function() {
            updateTimeHidden('out');
        });

        updateTimeHidden('in');
        updateTimeHidden('out');

        // Initialize Select2
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: cardBody
            });
        }

        // Show alert message
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

        // Hide alert
        function hideAlert() {
            $('#alertContainer').removeClass('d-block').addClass('d-none');
        }

        // Toggle loading state
        function setLoading(loading) {
            submitBtn.prop('disabled', loading);
            loadingSpinner.toggleClass('d-none', !loading);
        }

        // Clear all validation errors
        function clearValidation() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        }

        // Add validation error to field
        function addFieldError(fieldId, message) {
            const $field = $('#' + fieldId);
            $field.addClass('is-invalid');
            $field.after('<div class="invalid-feedback d-block">' + message + '</div>');
        }

        // Show/hide conditional fields
        function updateConditionalFields() {
            const tripType = $('#trip_type').val();
            const selectedBus = $('#bus_id option:selected');
            busSubType = selectedBus.data('subtype') || busSubType;

            // Hide all conditional fields first
            $('.conditional-field').hide().find('input, select').prop('required', false);

            // Show In Time if trip type is "in"
            if (tripType === 'in') {
                $('#inTimeField').show()
                    .find('select').prop('required', true);
            }

            // Show Out Time if trip type is "out"
            if (tripType === 'out') {
                $('#outTimeField').show()
                    .find('select').prop('required', true);
            }

            // Show Distance field for BRTC Hired Bus
            if (busSubType === 'BRTC Bus') {

                $('#distanceField').show();
                $('#total_distance').prop('required', true);
                $('#vehicleInfo').html('<span class="badge bg-info">BRTC Bus - Distance tracking required</span>');
            } else if (busSubType === 'Hired Bus') {
                $('#vehicleInfo').html('<span class="badge bg-success">Hired Bus - Fixed daily rate</span>');
            } else {
                $('#vehicleInfo').empty();
            }
        }

        // Vehicle change handler
        $('#bus_id').on('change', function() {
            updateConditionalFields();
        });

        // Trip type change handler
        $('#trip_type').on('change', function() {
            updateConditionalFields();
        });

        // Vehicle Sub Type change handler
        $('#bus_sub_type_id').on('change', function() {
            updateConditionalFields();
        });

        // Initialize conditional fields
        updateConditionalFields();

        // jQuery Validation Function
        function validateForm() {
            clearValidation();
            let isValid = true;
            let errors = [];
            const currentInTime = updateTimeHidden('in');
            const currentOutTime = updateTimeHidden('out');

            // Validate Vehicle
            if (!$('#bus_id').val()) {
                addFieldError('bus_id', 'Please select a bus');
                errors.push('Bus is required');
                isValid = false;
            }

            // Validate Date
            if (!$('#trip_date').val()) {
                addFieldError('trip_date', 'Trip date is required');
                errors.push('Trip date is required');
                isValid = false;
            }

            // Validate Trip Type
            if (!$('#trip_type').val()) {
                addFieldError('trip_type', 'Please select trip type');
                errors.push('Trip type is required');
                isValid = false;
            }

            // Validate Start Stoppage
            if (!$('#start_stoppage_id').val()) {
                addFieldError('start_stoppage_id', 'Please select start stoppage');
                errors.push('Start stoppage is required');
                isValid = false;
            }

            // Validate End Stoppage
            if (!$('#end_stoppage_id').val()) {
                addFieldError('end_stoppage_id', 'Please select end stoppage');
                errors.push('End stoppage is required');
                isValid = false;
            } else if ($('#start_stoppage_id').val() === $('#end_stoppage_id').val()) {
                addFieldError('end_stoppage_id', 'End stoppage must be different from start stoppage');
                errors.push('End stoppage must be different from start stoppage');
                isValid = false;
            }

            // Validate In Time (if trip type is in)
            if ($('#trip_type').val() === 'in' && !currentInTime) {
                addFieldError('in_time_hours', 'In time is required for In trips');
                errors.push('In time is required');
                isValid = false;
            }

            // Validate Out Time (if trip type is out)
            if ($('#trip_type').val() === 'out' && !currentOutTime) {
                addFieldError('out_time_hours', 'Out time is required for Out trips');
                errors.push('Out time is required');
                isValid = false;
            }

            // Validate Distance (if BRTC Hired Bus)
            if (busSubType === 'BRTC Bus') {
                const distance = $('#total_distance').val();
                if (!distance) {
                    addFieldError('total_distance', 'Total distance is required for BRTC Hired Bus');
                    errors.push('Total distance is required');
                    isValid = false;
                } else if (parseFloat(distance) <= 0) {
                    addFieldError('total_distance', 'Distance must be greater than 0');
                    errors.push('Distance must be greater than 0');
                    isValid = false;
                }
            }

            // Show errors if any
            if (!isValid) {
                showAlert('danger', errors.join('<br>'));
            }

            return isValid;
        }

        // Form submission
        $('#busTripForm').on('submit', function(e) {
            e.preventDefault();
            hideAlert();

            // Client-side validation
            if (!validateForm()) {
                return false;
            }

            // Submit via AJAX
            setLoading(true);
            updateTimeHidden('in');
            updateTimeHidden('out');
            let formData = new FormData(this);

            $.ajax({
                url: '{{ route("bus-trips.update", $busTrip) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    setLoading(false);

                    if (response.success) {
                        toastr.success(response.message || 'Bus trip updated successfully!');
                        window.location.href = response.redirect_url || "{{ route('bus-trips.index') }}";
                    } else {
                        toastr.error(response.message || 'Failed to update bus trip.');
                    }
                },
                error: function(xhr) {
                    setLoading(false);

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        $.each(errors, function(field, messages) {
                            addFieldError(field, messages[0]);
                            errorMessages.push(messages[0]);
                        });

                        toastr.error(errorMessages.join('<br>'));
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'An error occurred while updating.');
                    }
                }
            });
        });

        // Clear validation on input
        $('#busTripForm input, #busTripForm select, #busTripForm textarea').on('input change', function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').remove();
        });

        // Handle start stoppage change to load distance
        $('#start_stoppage_id').on('change', function() {
            const stoppageId = $(this).val();
            fetchDistanceForStoppages(stoppageId);
        });

        // Also handle Select2 change event
        if ($.fn.select2) {
            $('#start_stoppage_id').on('select2:select', function() {
                const stoppageId = $(this).val();
                fetchDistanceForStoppages(stoppageId);
            });
        }
    });
</script>
@endsection