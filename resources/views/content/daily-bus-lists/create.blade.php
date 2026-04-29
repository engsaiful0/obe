@extends('layouts/layoutMaster')

@section('title', 'Add Daily Bus List Entry')

@section('page-script')
<script>
    $(document).ready(function() {

        // Set today's date as default
        $('#list_date').val(new Date().toISOString().split('T')[0]);

        // Submit form via AJAX
        $('#dailyBusListForm').submit(function(e) {
            e.preventDefault();

            const submitBtn = $('#submitBtn');
            const submitSpinner = $('#submitSpinner');
            const submitText = $('#submitText');

            // Show spinner inside button
            submitSpinner.removeClass('d-none');
            submitText.text(' Saving...');
            submitBtn.prop('disabled', true);

            $.ajax({
                url: '{{ route("daily-bus-lists.store") }}',
                type: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Hide spinner and reset button
                    submitSpinner.addClass('d-none');
                    submitText.html('<i data-feather="save"></i> Save Entry');
                    submitBtn.prop('disabled', false);
                
                    if (response.success) {
                        toastr.success(response.message || 'Bus list saved successfully.');
                        $('#dailyBusListForm')[0].reset();
                        // ✅ Redirect after 1.5s delay
                        if (response.success) {
                            toastr.success(response.message);
                            // Redirect to index page
                            window.location.href = '{{ route("daily-bus-lists.all-buses-list") }}';
                        }
                    } else {
                        toastr.warning(response.message || 'Something went wrong, please try again.');
                    }
                },
                error: function(xhr) {
                    // Hide spinner and reset button
                    submitSpinner.addClass('d-none');
                    submitText.html('<i data-feather="save"></i> Save Entry');
                    submitBtn.prop('disabled', false);
                    

                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('An unexpected error occurred.');
                    }
                }
            });
        });

        // Reset form
        $('#resetBtn').click(function() {
            $('#dailyBusListForm')[0].reset();
            $('.select2').val('').trigger('change');
        });

        // Vehicle Sub Type Change Event
        $('#vehicle_sub_type_id').on('change', function() {
            const subTypeId = $(this).val();
            const vehicleSelect = $('#vehicle_id');

            if (subTypeId) {
                showSpinner();
                vehicleSelect.prop('disabled', true).html('<option value="">Loading vehicles...</option>');

                $.ajax({
                    url: '{{ route("daily-bus-lists.get-buses-names-by-subtype") }}',
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
                @foreach($buses as $bus)
                vehicleSelect.append('<option value="{{ $bus->id }}">{{ $bus->model_name }} ({{ $bus->registration_number }})</option>');
                @endforeach
            }
        });

        // Spinner functions for the page
        function showSpinner() {
            $('#loadingSpinner').removeClass('d-none');
        }

        function hideSpinner() {
            $('#loadingSpinner').addClass('d-none');
        }
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Add Daily Bus List Entry</h4>
                <a href="{{ route('daily-bus-lists.index') }}" class="btn btn-secondary">
                    <i data-feather="arrow-left"></i> Back to List
                </a>
            </div>

            <div class="card-body">
                <form id="dailyBusListForm">
                    @csrf
                    <div class="row">

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vehicle_sub_type_id" class="form-label">Vehicle Sub Type</label>
                                <select class="form-select select2" id="vehicle_sub_type_id" name="vehicle_sub_type_id">
                                    <option value="">Select Vehicle Sub Type</option>
                                    @foreach ($busSubTypes as $busSubType)
                                    <option value="{{ $busSubType->id }}">{{ $busSubType->sub_type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vehicle_id" class="form-label">Vehicle <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach ($buses as $bus)
                                    <option value="{{ $bus->id }}">{{ $bus->model_name }} ({{ $bus->registration_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_stoppage_id" class="form-label">Start Stoppage <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="start_stoppage_id" name="start_stoppage_id" required>
                                    <option value="">Select Start Stoppage</option>
                                    @foreach ($stoppages as $stoppage)
                                    <option value="{{ $stoppage->id }}">{{ $stoppage->stoppage_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_stoppage_id" class="form-label">End Stoppage <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="end_stoppage_id" name="end_stoppage_id" required>
                                    <option value="">Select End Stoppage</option>
                                    @foreach ($stoppages as $stoppage)
                                    <option value="{{ $stoppage->id }}">{{ $stoppage->stoppage_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="trip_time_id" class="form-label">Trip Time <span class="text-danger">*</span></label>
                                <select class="form-select" id="trip_time_id" name="trip_time_id" required>
                                    <option value="">Select Trip Time</option>
                                    @foreach($tripTimes as $tripTime)
                                        <option value="{{ $tripTime->id }}">
                                            {{ $tripTime->time_name }} - {{ \Carbon\Carbon::parse($tripTime->time_value)->format('H:i') }} {{ $tripTime->time_period }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="list_date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="list_date" name="list_date" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any remarks..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status" aria-hidden="true"></span>
                            <span id="submitText"><i data-feather="save"></i> Save Entry</span>
                        </button>
                        <button type="button" class="btn btn-secondary" id="resetBtn">
                            <i data-feather="refresh-cw"></i> Reset
                        </button>
                    </div>
                </form>


            </div>
        </div>
    </div>
</div>
@endsection