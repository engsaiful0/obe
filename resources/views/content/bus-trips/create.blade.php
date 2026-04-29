@extends('layouts/layoutMaster')

@section('title', 'Add Trip')

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

    .nav-tabs .nav-link {
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        font-weight: 600;
    }

    .bus-info-card {
        background: #f8f9fa;
        border-left: 4px solid #696cff;
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
    }

    .bus-info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.25rem 0;
    }

    .bus-info-label {
        font-weight: 500;
        color: #6c757d;
    }

    .bus-info-value {
        color: #212529;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Record Trip</h5>
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

        <!-- Bus Type Tabs -->
        <ul class="nav nav-tabs mb-4" id="busTypeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="own-bus-tab" data-bs-toggle="tab" data-bs-target="#own-bus" type="button" role="tab" data-sub-type-id="{{ \App\Models\BusSubType::OWN_BUS_SUB_TYPE_ID }}">
                    <i class="ti ti-bus me-2"></i>Own Bus
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="hired-bus-tab" data-bs-toggle="tab" data-bs-target="#hired-bus" type="button" role="tab" data-sub-type-id="{{ \App\Models\BusSubType::HIRED_BUS_SUB_TYPE_ID }}">
                    <i class="ti ti-bus me-2"></i>Hired Bus
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="brtc-bus-tab" data-bs-toggle="tab" data-bs-target="#brtc-bus" type="button" role="tab" data-sub-type-id="{{ \App\Models\BusSubType::BRTC_BUS_SUB_TYPE_ID }}">
                    <i class="ti ti-bus me-2"></i>BRTC Bus
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="busTypeTabContent">
            <!-- Own Bus Tab -->
            <div class="tab-pane fade show active" id="own-bus" role="tabpanel" data-sub-type-name="Own Bus">
                @include('content.bus-trips.partials.trip-form', ['subTypeId' => \App\Models\BusSubType::OWN_BUS_SUB_TYPE_ID, 'subTypeName' => 'Own Bus'])
            </div>

            <!-- Hired Bus Tab -->
            <div class="tab-pane fade" id="hired-bus" role="tabpanel" data-sub-type-name="Hired Bus">
                @include('content.bus-trips.partials.trip-form', ['subTypeId' => \App\Models\BusSubType::HIRED_BUS_SUB_TYPE_ID, 'subTypeName' => 'Hired Bus'])
            </div>

            <!-- BRTC Bus Tab -->
            <div class="tab-pane fade" id="brtc-bus" role="tabpanel" data-sub-type-name="BRTC Bus">
                @include('content.bus-trips.partials.trip-form', ['subTypeId' => \App\Models\BusSubType::BRTC_BUS_SUB_TYPE_ID, 'subTypeName' => 'BRTC Bus'])
            </div>
        </div>
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

        // Function to get default trip type based on current time
        function getDefaultTripType() {
            const now = new Date();
            const currentHour = now.getHours(); // 0-23 format
            
            // From 6 AM to 11 AM (6:00 to 11:59): Default to "IN"
            if (currentHour >= 6 && currentHour < 12) {
                return 'in';
            }
            // From 12 PM to 5 PM (12:00 to 17:59): Default to "OUT"
            else if (currentHour >= 12 && currentHour < 18) {
                return 'out';
            }
            // Outside these hours, return null (no default)
            return null;
        }

        // Function to set default trip type for BRTC and Hired Bus tabs
        function setDefaultTripTypeForTab(tabSelector) {
            const subTypeName = $(tabSelector).data('sub-type-name') || '';
           // const $tripTypeSelect = $(tabSelector + ' #trip_type');
            
            // Only apply default for BRTC Bus and Hired Bus tabs
            if (subTypeName === 'BRTC Bus' || subTypeName === 'Hired Bus') {
                // Only set default if trip type is not already selected
                if (!$tripTypeSelect.val()) {
                    const defaultTripType = getDefaultTripType();
                    if (defaultTripType) {
                        $tripTypeSelect.val(defaultTripType).trigger('change');
                    }
                }
            }
        }

        // Handle tab switching
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const subTypeId = $(e.target).data('sub-type-id');
            const targetTab = $(e.target).data('bs-target');
            
            // Reinitialize Select2 for the active tab
            $(targetTab + ' .select2').select2({
                dropdownParent: $('.card-body')
            });
            
            // Clear form in the newly active tab
            const $form = $(targetTab + ' form');
            $form[0].reset();
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();
            $form.find('#busInfo').html('');
            
            // Load buses for this sub type and set default trip type after loading
            loadBusesForSubType(subTypeId, targetTab, function() {
                // Set default trip type for BRTC and Hired Bus tabs after buses are loaded
                setDefaultTripTypeForTab(targetTab);
            });
        });

        // Load buses for a specific sub type
        function loadBusesForSubType(subTypeId, tabSelector, callback) {
            const $busSelect = $(tabSelector + ' #bus_id');
            $busSelect.prop('disabled', true).html('<option value="">Loading buses...</option>');

            $.ajax({
                url: '{{ route("daily-bus-lists.get-buses-names-by-subtype") }}',
                type: 'GET',
                data: {
                    sub_type_id: subTypeId
                },
                success: function(response) {
                    $busSelect.empty().append('<option value="">Select Bus Number</option>');
                    if (response.success && response.buses && response.buses.length > 0) {
                        $.each(response.buses, function(index, bus) {
                            const seatingCapacity = bus.seating_capacity || '';
                            const registration = bus.registration_number || '';
                            const busType = (bus.bus_sub_type && bus.bus_sub_type.sub_type_name) || '';
                            const status = bus.status || '';
                            
                            $busSelect.append(`<option value="${bus.id}" 
                                data-seating-capacity="${seatingCapacity}"
                                data-registration="${registration}"
                                data-bus-type="${busType}"
                                data-status="${status}">${bus.bus_number}</option>`);
                        });
                    } else {
                        $busSelect.append('<option value="">No buses found</option>');
                    }
                    $busSelect.prop('disabled', false);
                    $busSelect.trigger('change');
                    
                    // Execute callback if provided
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                },
                error: function() {
                    toastr.error('Failed to load buses.');
                    $busSelect.prop('disabled', false);
                    
                    // Execute callback even on error
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                }
            });
        }

        // Initialize buses for active tab
        const activeTab = $('.nav-link.active');
        const activeSubTypeId = activeTab.data('sub-type-id');
        const activeTabSelector = activeTab.data('bs-target');
        loadBusesForSubType(activeSubTypeId, activeTabSelector, function() {
            // Set default trip type for BRTC and Hired Bus tabs after buses are loaded
            setDefaultTripTypeForTab(activeTabSelector);
        });

        // Show alert message
        function showAlert(type, message, tabSelector) {
            const $alertContainer = $(tabSelector + ' #alertContainer');
            const $alertMessage = $(tabSelector + ' #alertMessage');
            const $alertText = $(tabSelector + ' #alertText');
            const $alertIcon = $(tabSelector + ' #alertIcon');
            
            $alertContainer.removeClass('d-none').addClass('d-block');
            $alertMessage.removeClass().addClass(`alert alert-${type} alert-dismissible fade show`);
            $alertText.html(message);
            $alertIcon.removeClass().addClass(type === 'success' ? 'ti ti-check-circle' : 'ti ti-alert-circle');
            $('.card')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // Hide alert
        function hideAlert(tabSelector) {
            $(tabSelector + ' #alertContainer').removeClass('d-block').addClass('d-none');
        }

        // Toggle loading state
        function setLoading(loading, tabSelector) {
            const $submitBtn = $(tabSelector + ' #submitBtn');
            $submitBtn.prop('disabled', loading);

            if (loading) {
                $(tabSelector + ' #loadingSpinner').removeClass('d-none');
                $submitBtn.html('<i class="ti ti-check me-1"></i>Saving... <span class="spinner-border spinner-border-sm ms-2"></span>');
            } else {
                $(tabSelector + ' #loadingSpinner').addClass('d-none');
                $submitBtn.html('<i class="ti ti-check me-1"></i>Record Trip');
            }
        }

        // Clear all validation errors
        function clearValidation(tabSelector) {
            $(tabSelector + ' .is-invalid').removeClass('is-invalid');
            $(tabSelector + ' .invalid-feedback').remove();
        }

        // Add validation error to field
        function addFieldError(fieldId, message, tabSelector) {
            const $field = $(tabSelector + ' #' + fieldId);
            $field.addClass('is-invalid');
            $field.after('<div class="invalid-feedback d-block">' + message + '</div>');
        }

        // Show/hide conditional fields based on trip type and bus subtype
        function updateConditionalFields(tabSelector, subTypeName) {
            const tripType = $(tabSelector + ' #trip_type').val();
            const selectedBus = $(tabSelector + ' #bus_id option:selected');
            const seatingCapacity = selectedBus.data('seating-capacity') || '';

            // Update seating capacity display
            if (seatingCapacity) {
                $(tabSelector + ' #seating_capacity_display').text(seatingCapacity);
                $(tabSelector + ' #seating_capacity_info').show();
            } else {
                $(tabSelector + ' #seating_capacity_info').hide();
            }

            // Hide all conditional fields first
            $(tabSelector + ' .conditional-field').hide().find('input').prop('required', false);

            // Show In Time if trip type is "in"
            if (tripType === 'in') {
                $(tabSelector + ' #inTimeField').show();
                $(tabSelector + ' #in_time').prop('required', true);
                $(tabSelector + ' #out_time').val('');
            }

            // Show Out Time if trip type is "out"
            if (tripType === 'out') {
                $(tabSelector + ' #outTimeField').show();
                $(tabSelector + ' #out_time').prop('required', true);
                $(tabSelector + ' #in_time').val('');
            }

            // Show Distance field for BRTC Bus
            if (subTypeName === 'BRTC Bus') {
                $(tabSelector + ' #distanceField').show();
                $(tabSelector + ' #total_distance').prop('required', true);
                $(tabSelector + ' #busInfo').html('<span class="badge bg-info">BRTC Bus - Distance tracking required</span>');
            } else if (subTypeName === 'Hired Bus') {
                $(tabSelector + ' #total_distance').val('').prop('required', false);
                $(tabSelector + ' #busInfo').html('<span class="badge bg-success">Hired Bus - Fixed daily rate</span>');
            } else {
                $(tabSelector + ' #busInfo').html('');
            }
        }

        // Bus change handler for all tabs
        $(document).on('change', '#own-bus #bus_id, #hired-bus #bus_id, #brtc-bus #bus_id', function() {
            const tabSelector = $(this).closest('.tab-pane').attr('id');
            const subTypeName = $(this).closest('.tab-pane').data('sub-type-name') || '';
            updateConditionalFields('#' + tabSelector, subTypeName);
        });

        // Trip type change handler for all tabs
        $(document).on('change', '#own-bus #trip_type, #hired-bus #trip_type, #brtc-bus #trip_type', function() {
            const tabSelector = $(this).closest('.tab-pane').attr('id');
            const subTypeName = $(this).closest('.tab-pane').data('sub-type-name') || '';
            updateConditionalFields('#' + tabSelector, subTypeName);
        });

        // Initialize conditional fields for active tab
        const activeTabId = $('.tab-pane.active').attr('id');
        const activeSubTypeName = $('.tab-pane.active').data('sub-type-name') || '';
        updateConditionalFields('#' + activeTabId, activeSubTypeName);

        // jQuery Validation Function
        function validateForm(tabSelector, subTypeName) {
            clearValidation(tabSelector);
            let isValid = true;
            let errors = [];

            // Validate Bus
            if (!$(tabSelector + ' #bus_id').val()) {
                addFieldError('bus_id', 'Please select a bus', tabSelector);
                errors.push('Bus is required');
                isValid = false;
            }

            // Validate Date
            if (!$(tabSelector + ' #trip_date').val()) {
                addFieldError('trip_date', 'Trip date is required', tabSelector);
                errors.push('Trip date is required');
                isValid = false;
            }

            // Validate Trip Type
            if (!$(tabSelector + ' #trip_type').val()) {
                addFieldError('trip_type', 'Please select trip type', tabSelector);
                errors.push('Trip type is required');
                isValid = false;
            }

            // Validate Start Stoppage
            if (!$(tabSelector + ' #start_stoppage_id').val()) {
                addFieldError('start_stoppage_id', 'Please select start stoppage', tabSelector);
                errors.push('Start stoppage is required');
                isValid = false;
            }

            // Validate End Stoppage
            if (!$(tabSelector + ' #end_stoppage_id').val()) {
                addFieldError('end_stoppage_id', 'Please select end stoppage', tabSelector);
                errors.push('End stoppage is required');
                isValid = false;
            } else if ($(tabSelector + ' #start_stoppage_id').val() === $(tabSelector + ' #end_stoppage_id').val()) {
                addFieldError('end_stoppage_id', 'End stoppage must be different from start stoppage', tabSelector);
                errors.push('End stoppage must be different from start stoppage');
                isValid = false;
            }

            // Validate In Time (if trip type is in)
            if ($(tabSelector + ' #trip_type').val() === 'in' && !$(tabSelector + ' #in_time').val()) {
                addFieldError('in_time', 'In time is required for In trips', tabSelector);
                errors.push('In time is required');
                isValid = false;
            }

            // Validate Out Time (if trip type is out)
            if ($(tabSelector + ' #trip_type').val() === 'out' && !$(tabSelector + ' #out_time').val()) {
                addFieldError('out_time', 'Out time is required for Out trips', tabSelector);
                errors.push('Out time is required');
                isValid = false;
            }

            // Validate Distance (if BRTC Bus)
            if (subTypeName === 'BRTC Bus') {
                const distance = $(tabSelector + ' #total_distance').val();
                if (!distance) {
                    addFieldError('total_distance', 'Total distance is required for BRTC Bus', tabSelector);
                    errors.push('Total distance is required');
                    isValid = false;
                } else if (parseFloat(distance) <= 0) {
                    addFieldError('total_distance', 'Distance must be greater than 0', tabSelector);
                    errors.push('Distance must be greater than 0');
                    isValid = false;
                }
            }

            // Validate Passengers (if provided, should not exceed seating capacity)
            const passengers = $(tabSelector + ' #passengers').val();
            const seatingCapacity = $(tabSelector + ' #bus_id option:selected').data('seating-capacity');
            if (passengers && seatingCapacity) {
                if (parseInt(passengers) > parseInt(seatingCapacity)) {
                    addFieldError('passengers', `Passengers cannot exceed seating capacity (${seatingCapacity})`, tabSelector);
                    errors.push('Passengers exceed seating capacity');
                    isValid = false;
                }
            }

            // Show errors if any
            if (!isValid) {
                showAlert('danger', errors.join('<br>'), tabSelector);
            }

            return isValid;
        }

        // Form submission handler for all tabs
        $(document).on('submit', '#own-bus form, #hired-bus form, #brtc-bus form', function(e) {
            e.preventDefault();
            const tabSelector = '#' + $(this).closest('.tab-pane').attr('id');
            const subTypeName = $(this).closest('.tab-pane').data('sub-type-name') || '';
            
            hideAlert(tabSelector);

            // Client-side validation
            if (!validateForm(tabSelector, subTypeName)) {
                return false;
            }

            // Submit via AJAX
            setLoading(true, tabSelector);
            let formData = new FormData(this);

            $.ajax({
                url: '{{ route("bus-trips.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    setLoading(false, tabSelector);

                    if (response.success) {
                        toastr.success(response.message || 'Trip recorded successfully!');
                        window.location.href = response.redirect_url || "{{ route('bus-trips.index') }}";
                    } else {
                        toastr.error(response.message || 'Failed to record trip.');
                    }
                },
                error: function(xhr) {
                    setLoading(false, tabSelector);

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        $.each(errors, function(field, messages) {
                            addFieldError(field, messages[0], tabSelector);
                            errorMessages.push(messages[0]);
                        });

                        toastr.error(errorMessages.join('<br>'));
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'An error occurred while saving.');
                    }
                }
            });
        });

        // Clear validation on input
        $(document).on('input change', '#own-bus input, #own-bus select, #own-bus textarea, #hired-bus input, #hired-bus select, #hired-bus textarea, #brtc-bus input, #brtc-bus select, #brtc-bus textarea', function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').remove();
        });

        // Function to fetch distance from stoppage
        function fetchDistanceForStoppage(stoppageId, tabSelector) {
            const $distanceInput = $(tabSelector + ' #total_distance');

            // Only proceed if stoppage is selected and distance input exists
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

        // Handle start stoppage change to load distance for all tabs
        $(document).on('change', '#own-bus #start_stoppage_id, #hired-bus #start_stoppage_id, #brtc-bus #start_stoppage_id', function() {
            const tabSelector = '#' + $(this).closest('.tab-pane').attr('id');
            const stoppageId = $(this).val();
            fetchDistanceForStoppage(stoppageId, tabSelector);
        });

        // Also handle Select2 change event
        if ($.fn.select2) {
            $(document).on('select2:select', '#own-bus #start_stoppage_id, #hired-bus #start_stoppage_id, #brtc-bus #start_stoppage_id', function() {
                const tabSelector = '#' + $(this).closest('.tab-pane').attr('id');
                const stoppageId = $(this).val();
                fetchDistanceForStoppage(stoppageId, tabSelector);
            });
        }
    });
</script>
@endsection
