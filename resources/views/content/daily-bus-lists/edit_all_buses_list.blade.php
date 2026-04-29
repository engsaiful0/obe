@extends('layouts/layoutMaster')

@section('title', 'Edit All Buses List Entry')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-bus-list-ajax.css') }}">
@endsection

@section('page-script')
    
    <script src="{{ asset('assets/js/daily-bus-list-ajax.js') }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Add Daily Bus List Entry</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('daily-bus-lists.index') }}" class="btn btn-secondary">
                        <i data-feather="arrow-left"></i> Back to List
                    </a>
                    <button type="button" class="btn btn-info" id="loadLastSavedBtn">
                        <i data-feather="download"></i> Load Last Saved Data
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="dailyBusListForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="list_date">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="list_date" name="list_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="bus_type">Bus Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="bus_type" name="bus_type" required>
                                    <option value="">Select Bus Type</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="vehicle_id">Vehicle <span class="text-danger">*</span></label>
                                <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Select Vehicle</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="vehicle_sub_type_id">Vehicle Sub Type</label>
                                <select class="form-select" id="vehicle_sub_type_id" name="vehicle_sub_type_id">
                                    <option value="">Select Vehicle Sub Type</option>
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
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="end_stoppage_id">End Stoppage <span class="text-danger">*</span></label>
                                <select class="form-select" id="end_stoppage_id" name="end_stoppage_id" required>
                                    <option value="">Select End Stoppage</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="start_time">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="driver_id">Driver</label>
                                <select class="form-select" id="driver_id" name="driver_id">
                                    <option value="">Select Driver</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="assistant_id">Assistant</label>
                                <select class="form-select" id="assistant_id" name="assistant_id">
                                    <option value="">Select Assistant</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any remarks..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i data-feather="save"></i> Save Entry
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

<!-- Loading Spinner -->
<div id="loadingSpinner" class="d-none">
    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let filterOptions = {};

    // Load filter options
    function loadFilterOptions() {
        showSpinner();
        $.ajax({
            url: '{{ route("daily-bus-lists.filter-options") }}',
            method: 'GET',
            success: function(response) {
                filterOptions = response;
                
                // Populate bus types
                const busTypeSelect = $('#bus_type');
                busTypeSelect.empty().append('<option value="">Select Bus Type</option>');
                $.each(response.bus_types, function(key, value) {
                    busTypeSelect.append(`<option value="${key}">${value}</option>`);
                });

                // Populate vehicles
                const vehicleSelect = $('#vehicle_id');
                vehicleSelect.empty().append('<option value="">Select Vehicle</option>');
                $.each(response.vehicles, function(index, vehicle) {
                    vehicleSelect.append(`<option value="${vehicle.id}">${vehicle.model_name} (${vehicle.registration_number})</option>`);
                });

                // Populate vehicle sub types
                const vehicleSubTypeSelect = $('#vehicle_sub_type_id');
                vehicleSubTypeSelect.empty().append('<option value="">Select Vehicle Sub Type</option>');
                $.each(response.vehicle_sub_types, function(index, subType) {
                    vehicleSubTypeSelect.append(`<option value="${subType.id}">${subType.sub_type_name}</option>`);
                });

                // Populate stoppages
                const startStoppageSelect = $('#start_stoppage_id');
                const endStoppageSelect = $('#end_stoppage_id');
                startStoppageSelect.empty().append('<option value="">Select Start Stoppage</option>');
                endStoppageSelect.empty().append('<option value="">Select End Stoppage</option>');
                $.each(response.stoppages, function(index, stoppage) {
                    startStoppageSelect.append(`<option value="${stoppage.id}">${stoppage.stoppage_name}</option>`);
                    endStoppageSelect.append(`<option value="${stoppage.id}">${stoppage.stoppage_name}</option>`);
                });

                // Populate drivers
                const driverSelect = $('#driver_id');
                driverSelect.empty().append('<option value="">Select Driver</option>');
                $.each(response.drivers, function(index, driver) {
                    driverSelect.append(`<option value="${driver.id}">${driver.full_name} (${driver.driver_unique_id})</option>`);
                });

                // Populate assistants
                const assistantSelect = $('#assistant_id');
                assistantSelect.empty().append('<option value="">Select Assistant</option>');
                $.each(response.assistants, function(index, assistant) {
                    assistantSelect.append(`<option value="${assistant.id}">${assistant.assistant_name} (${assistant.assistant_id})</option>`);
                });

                hideSpinner();
            },
            error: function(xhr, status, error) {
                hideSpinner();
                console.error('Error loading filter options:', error);
                toastr.error('Error loading form options');
            }
        });
    }

    // Load last saved data
    $('#loadLastSavedBtn').click(function() {
        const date = $('#list_date').val();
        if (!date) {
            toastr.warning('Please select a date first');
            return;
        }

        showSpinner();
        $.ajax({
            url: '{{ route("daily-bus-lists.last-saved-data") }}',
            method: 'GET',
            data: { date: date },
            success: function(response) {
                hideSpinner();
                if (response.data && response.data.length > 0) {
                    // Load the first entry data into the form
                    const firstEntry = response.data[0];
                    $('#vehicle_id').val(firstEntry.vehicle_id).trigger('change');
                    $('#start_stoppage_id').val(firstEntry.start_stoppage_id).trigger('change');
                    $('#end_stoppage_id').val(firstEntry.end_stoppage_id).trigger('change');
                    $('#start_time').val(firstEntry.start_time);
                    $('#driver_id').val(firstEntry.driver_id).trigger('change');
                    $('#assistant_id').val(firstEntry.assistant_id).trigger('change');
                    $('#bus_type').val(firstEntry.bus_type).trigger('change');
                    $('#vehicle_sub_type_id').val(firstEntry.vehicle_sub_type_id).trigger('change');
                    $('#remarks').val(firstEntry.remarks);
                    toastr.success('Last saved data loaded successfully');
                } else {
                    toastr.info('No saved data found for this date');
                }
            },
            error: function(xhr, status, error) {
                hideSpinner();
                console.error('Error loading last saved data:', error);
                toastr.error('Error loading last saved data');
            }
        });
    });

    // Form submission
    $('#dailyBusListForm').submit(function(e) {
        e.preventDefault();
        
        showSpinner();
        $('#submitBtn').prop('disabled', true);

        $.ajax({
            url: '{{ route("daily-bus-lists.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                hideSpinner();
                $('#submitBtn').prop('disabled', false);
                
                if (response.success) {
                    toastr.success(response.message);
                    // Reset form
                    $('#dailyBusListForm')[0].reset();
                    // Redirect to all-buses-list page
                    window.location.href = '{{ route("daily-bus-lists.all-buses-list") }}';
                }
            },
            error: function(xhr, status, error) {
                hideSpinner();
                $('#submitBtn').prop('disabled', false);
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        toastr.error(messages[0]);
                    });
                } else {
                    toastr.error('Error saving entry');
                }
            }
        });
    });

    // Reset form
    $('#resetBtn').click(function() {
        $('#dailyBusListForm')[0].reset();
        $('.form-select').trigger('change');
    });

    // Show/hide spinner
    function showSpinner() {
        $('#loadingSpinner').removeClass('d-none');
    }

    function hideSpinner() {
        $('#loadingSpinner').addClass('d-none');
    }

    // Set today's date as default
    $('#list_date').val(new Date().toISOString().split('T')[0]);

    // Initialize
    loadFilterOptions();
});
</script>
@endpush
