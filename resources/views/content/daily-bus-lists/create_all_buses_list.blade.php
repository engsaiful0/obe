@extends('layouts/layoutMaster')

@section('title', 'Add All Buses List Entry')

@section('vendor-style')
<style>
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
    
    .table-responsive {
        border-radius: 0.375rem;
        overflow: hidden;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .form-select:focus,
    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endsection

@section('page-script')
<script>
    window.dailyBusListUrls = {
        getVehiclesBySubType: '{{ route("daily-bus-lists.get-vehicles-by-subtype") }}',
        storeMultiple: '{{ route("daily-bus-lists.store-multiple") }}',
        lastSavedData: '{{ route("daily-bus-lists.last-saved-data") }}',
        checkVehicleData: '{{ route("daily-bus-lists.check-vehicle-data") }}',
        allBusesList: '{{ route("daily-bus-lists.all-buses-list") }}'
    };
    console.log('Daily Bus List URLs:', window.dailyBusListUrls);
</script>
<script src="{{ asset('assets/js/daily-bus-list-ajax.js') }}?v={{ time() }}"></script>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Add Daily Bus List Entry</h4>
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
                <!-- Alert Messages -->
                <div id="alertContainer" class="mb-3"></div>
                
                <form id="dailyBusListForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="list_date">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="list_date" name="list_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="vehicle_sub_type_id">Vehicle Sub Type <span class="text-danger">*</span></label>
                            <select class="select2 form-select" id="vehicle_sub_type_id" name="vehicle_sub_type_id" required>
                                <option value="">Select Vehicle Sub Type</option>
                                <option value="all">All Sub Types</option>
                                @foreach ($vehicleSubTypes as $vehicleSubType)
                                    <option value="{{ $vehicleSubType->id }}">{{ $vehicleSubType->sub_type_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="vehicleListContainer">
                                <!-- Vehicle rows will load here via AJAX -->
                                <div class="text-center text-muted py-5" id="vehiclePlaceholder">
                                    <div class="spinner-border text-primary d-none" id="loadingSpinner" role="status"></div>
                                    <p class="mt-2">Select a vehicle sub type to load vehicles...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status" aria-hidden="true"></span>
                                <span id="submitText">
                                    <i data-feather="save"></i> Save Entry
                                </span>
                            </button>
                            <button type="button" class="btn btn-secondary" id="resetBtn">
                                <i data-feather="refresh-cw"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection




