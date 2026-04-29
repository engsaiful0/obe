@extends('layouts/layoutMaster')

@section('title', 'Edit Daily Bus List Entry')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/css/daily-bus-list-ajax.css') }}">
<style>
    .loading {
        pointer-events: none;
        opacity: 0.7;
    }
    
    .loading * {
        cursor: wait !important;
    }
    
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }
    
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
    
    .is-invalid {
        border-color: #dc3545;
    }
</style>
@endsection



@section('page-script')
<script>
    $(document).ready(function() {
        let filterOptions = {};


        // Form submission
        $('#dailyBusListForm').submit(function(e) {
            e.preventDefault();

            // Clear previous validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Basic client-side validation
            let isValid = true;
            const requiredFields = ['vehicle_id', 'start_stoppage_id', 'end_stoppage_id', 'trip_time_id', 'list_date'];
            
            requiredFields.forEach(function(field) {
                const element = $(`[name="${field}"]`);
                if (!element.val()) {
                    element.addClass('is-invalid');
                    element.after(`<div class="invalid-feedback">This field is required.</div>`);
                    isValid = false;
                }
            });

            // Validate that start and end stoppages are different
            if ($('#start_stoppage_id').val() === $('#end_stoppage_id').val() && $('#start_stoppage_id').val()) {
                $('#end_stoppage_id').addClass('is-invalid');
                $('#end_stoppage_id').after(`<div class="invalid-feedback">End stoppage must be different from start stoppage.</div>`);
                isValid = false;
            }

            if (!isValid) {
                toastr.error('Please fix the validation errors before submitting.');
                return;
            }

            showSpinner();
            setButtonLoading($('#submitBtn'), true);

            $.ajax({
                url: '{{ route("daily-bus-lists.update", $dailyBusList->id) }}',
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideSpinner();
                    setButtonLoading($('#submitBtn'), false);

                    if (response.success) {
                        toastr.success(response.message);
                        // Redirect to index page
                        window.location.href = '{{ route("daily-bus-lists.all-buses-list") }}';
                    }
                },
                error: function(xhr, status, error) {
                    hideSpinner();
                    setButtonLoading($('#submitBtn'), false);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            const element = $(`[name="${field}"]`);
                            element.addClass('is-invalid');
                            element.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                        });
                        toastr.error('Please fix the validation errors.');
                    } else {
                        toastr.error('Error updating entry. Please try again.');
                    }
                }
            });
        });

        // Reset form
        $('#resetBtn').click(function() {
            if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
                $('#dailyBusListForm')[0].reset();
                $('.form-select').trigger('change');
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                
                // Restore original values
                $('#list_date').val('{{ $dailyBusList->list_date->format("Y-m-d") }}');
                $('#trip_time_id').val('{{ $dailyBusList->trip_time_id }}');
                $('#vehicle_sub_type_id').val('{{ $dailyBusList->vehicle_sub_type_id }}');
                $('#vehicle_id').val('{{ $dailyBusList->vehicle_id }}');
                $('#start_stoppage_id').val('{{ $dailyBusList->start_stoppage_id }}');
                $('#end_stoppage_id').val('{{ $dailyBusList->end_stoppage_id }}');
                $('#remarks').val('{{ $dailyBusList->remarks }}');
                
                toastr.info('Form has been reset to original values.');
            }
        });

        // Show/hide spinner
        function showSpinner() {
            $('#loadingSpinner').removeClass('d-none');
            $('body').addClass('loading');
        }

        function hideSpinner() {
            $('#loadingSpinner').addClass('d-none');
            $('body').removeClass('loading');
        }

        // Add loading state to buttons
        function setButtonLoading(button, loading = true) {
            if (loading) {
                button.prop('disabled', true);
                button.data('original-text', button.html());
                button.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...');
            } else {
                button.prop('disabled', false);
                button.html(button.data('original-text'));
            }
        }

        // Initialize form with current values
        // The form values are already set by the Blade template
        // No need to override them

        // Track form changes
        let formChanged = false;
        $('#dailyBusListForm input, #dailyBusListForm select, #dailyBusListForm textarea').on('change', function() {
            formChanged = true;
        });

        // Warn before leaving if form has changes
        $(window).on('beforeunload', function() {
            if (formChanged) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });

        // Reset form changed flag on successful submission
        $('#dailyBusListForm').on('submit', function() {
            formChanged = false;
        });

        // Handle vehicle sub type change
        $('#vehicle_sub_type_id').on('change', function() {
            const subTypeId = $(this).val();
            const vehicleSelect = $('#vehicle_id');

            if (subTypeId) {
                showSpinner();
                vehicleSelect.prop('disabled', true).html('<option value="">Loading vehicles...</option>');

                $.ajax({
                    url: '{{ route("daily-bus-lists.get-vehicles-names-by-subtype") }}',
                    type: 'GET',
                    data: {
                        sub_type_id: subTypeId
                    },
                    success: function(response) {
                        vehicleSelect.empty().append('<option value="">Select Vehicle</option>');
                        if (response.success && response.vehicles && response.vehicles.length > 0) {
                            $.each(response.vehicles, function(index, vehicle) {
                                vehicleSelect.append(`<option value="${vehicle.id}">${vehicle.model_name} (${vehicle.registration_number})</option>`);
                            });
                        } else {
                            vehicleSelect.append('<option value="">No vehicles found</option>');
                        }
                        vehicleSelect.prop('disabled', false);
                        hideSpinner();
                    },
                    error: function() {
                        toastr.error('Failed to load vehicles.');
                        vehicleSelect.prop('disabled', false);
                        hideSpinner();
                    }
                });
            } else {
                vehicleSelect.empty().append('<option value="">Select Vehicle</option>');
                @foreach($vehicles as $vehicle)
                vehicleSelect.append('<option value="{{ $vehicle->id }}">{{ $vehicle->model_name }} ({{ $vehicle->registration_number }})</option>');
                @endforeach
            }
        });
       
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Daily Bus List Entry</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('daily-bus-lists.index') }}" class="btn btn-secondary">
                        <i data-feather="arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="dailyBusListForm" action="{{ route('daily-bus-lists.update', $dailyBusList->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="vehicle_sub_type_id">Vehicle Sub Type</label>
                                <select class="form-select" id="vehicle_sub_type_id" name="vehicle_sub_type_id">
                                    <option value="">Select Vehicle Sub Type</option>
                                    @foreach ($vehicleSubTypes as $vehicleSubType)
                                    <option value="{{ $vehicleSubType->id }}" {{ $vehicleSubType->id == $dailyBusList->vehicle_sub_type_id ? 'selected' : '' }}>{{ $vehicleSubType->sub_type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="vehicle_id">Vehicle <span class="text-danger">*</span></label>
                                <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ $vehicle->id == $dailyBusList->vehicle_id ? 'selected' : '' }}>{{ $vehicle->model_name }} ({{ $vehicle->registration_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="start_stoppage_id">Start Stoppage <span class="text-danger">*</span></label>
                                <select class="form-select" id="start_stoppage_id" name="start_stoppage_id" required>
                                    <option value="">Select Start Stoppage</option>
                                    @foreach ($stoppages as $stoppage)
                                    <option value="{{ $stoppage->id }}" {{ $stoppage->id == $dailyBusList->start_stoppage_id ? 'selected' : '' }}>{{ $stoppage->stoppage_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="end_stoppage_id">End Stoppage <span class="text-danger">*</span></label>
                                <select class="form-select" id="end_stoppage_id" name="end_stoppage_id" required>
                                    <option value="">Select End Stoppage</option>
                                    @foreach ($stoppages as $stoppage)
                                    <option value="{{ $stoppage->id }}" {{ $stoppage->id == $dailyBusList->end_stoppage_id ? 'selected' : '' }}>{{ $stoppage->stoppage_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="trip_time_id">Trip Time <span class="text-danger">*</span></label>
                                <select class="form-select" id="trip_time_id" name="trip_time_id" required>
                                    <option value="">Select Trip Time</option>
                                    @foreach($tripTimes as $tripTime)
                                        <option value="{{ $tripTime->id }}" {{ $dailyBusList->trip_time_id == $tripTime->id ? 'selected' : '' }}>
                                            {{ $tripTime->time_name }} - {{ \Carbon\Carbon::parse($tripTime->time_value)->format('H:i') }} {{ $tripTime->time_period }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="list_date">Date <span class="text-danger">*</span></label>
                                <input type="date" value="{{ $dailyBusList->list_date->format('Y-m-d') }}" class="form-control" id="list_date" name="list_date" required>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                      
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label" for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any remarks...">{{ $dailyBusList->remarks }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i data-feather="save"></i> Update Entry
                                </button>
                                <button type="button" class="btn btn-secondary" id="resetBtn">
                                    <i data-feather="refresh-cw"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection