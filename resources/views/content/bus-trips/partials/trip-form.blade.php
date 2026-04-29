<form action="{{ route('bus-trips.store') }}" method="POST" class="trip-form" data-sub-type-id="{{ $subTypeId }}" data-sub-type-name="{{ $subTypeName }}">
    @csrf
    <input type="hidden" name="bus_sub_type_id" value="{{ $subTypeId }}">

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
            <label for="bus_id" class="form-label">Bus Number <span class="text-danger">*</span></label>
            <select name="bus_id" id="bus_id" class="select2 form-select @error('bus_id') is-invalid @enderror" required>
                <option value="">Select Bus Number</option>
            </select>
            @error('bus_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text" id="busInfo"></div>
        </div>

        <div class="col-md-3">
            <label for="trip_date" class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="trip_date" id="trip_date"
                class="form-control @error('trip_date') is-invalid @enderror"
                value="{{ old('trip_date', date('Y-m-d')) }}" required>
            @error('trip_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="trip_type" class="form-label">Trip Type <span class="text-danger">*</span></label>
            {{-- trip_type dropdown intentionally not enhanced with Select2 --}}
            <select name="trip_type" id="trip_type" class="form-select @error('trip_type') is-invalid @enderror" required>
                <option value="">Select Trip Type</option>
                <option value="in" {{ old('trip_type') == 'arrival' ? 'selected' : '' }}>Arrival</option>
                <option value="out" {{ old('trip_type') == 'departure' ? 'selected' : '' }}>Departure</option>
            </select>
            @error('trip_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="passengers" class="form-label">Passengers</label>
            <input type="number" name="passengers" id="passengers" min="0"
                class="form-control @error('passengers') is-invalid @enderror"
                value="{{ old('passengers') }}" placeholder="0">
            @error('passengers')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text" id="seating_capacity_info" style="display: none;">
                Seat Capacity: <span id="seating_capacity_display" class="fw-bold">0</span>
            </div>
        </div>
    </div>

    <!-- Bus Information Card -->
    <div class="bus-info-card" id="busInfoCard" style="display: none;">
        <div class="row">
            <div class="col-md-3">
                <div class="bus-info-item">
                    <span class="bus-info-label">Seat Capacity:</span>
                    <span class="bus-info-value" id="info_seating_capacity">-</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bus-info-item">
                    <span class="bus-info-label">Bus Type:</span>
                    <span class="bus-info-value" id="info_bus_type">-</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bus-info-item">
                    <span class="bus-info-label">Registration:</span>
                    <span class="bus-info-value" id="info_registration">-</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bus-info-item">
                    <span class="bus-info-label">Status:</span>
                    <span class="bus-info-value" id="info_status">-</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
    

        <div class="col-md-3">
            <label for="alternate_driver_id" class="form-label">Alternate Driver</label>
            <select name="alternate_driver_id" id="alternate_driver_id" class="select2 form-select @error('alternate_driver_id') is-invalid @enderror">
                <option value="">Select Alternate Driver</option>
                @foreach($drivers as $driver)
                <option value="{{ $driver->id }}" {{ old('alternate_driver_id') == $driver->id ? 'selected' : '' }}>
                    {{ $driver->full_name }} ({{ $driver->driver_unique_id ?? 'N/A' }})
                </option>
                @endforeach
            </select>
            @error('alternate_driver_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

       

        <div class="col-md-3">
            <label for="alternate_bus_helper_id" class="form-label">Alternate Bus Helper</label>
            <select name="alternate_bus_helper_id" id="alternate_bus_helper_id" class="select2 form-select @error('alternate_bus_helper_id') is-invalid @enderror">
                <option value="">Select Alternate Bus Helper</option>
                @foreach($busHelpers as $busHelper)
                <option value="{{ $busHelper->id }}" {{ old('alternate_bus_helper_id') == $busHelper->id ? 'selected' : '' }}>
                    {{ $busHelper->bus_helper_name }} ({{ $busHelper->bus_helper_id ?? 'N/A' }})
                </option>
                @endforeach
            </select>
            @error('alternate_bus_helper_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Route Information -->
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
                <option value="{{ $stoppage->id }}" {{ old('start_stoppage_id') == $stoppage->id ? 'selected' : '' }}>
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
                <option value="{{ $stoppage->id }}" {{ old('end_stoppage_id') == $stoppage->id ? 'selected' : '' }}>
                    {{ $stoppage->stoppage_name }}
                </option>
                @endforeach
            </select>
            @error('end_stoppage_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Time & Distance Information -->
    <div class="row mb-4">
        <div class="col-12">
            <h6 class="fw-bold text-primary mb-3">
                <i class="ti ti-clock me-2"></i>Time & Distance Information
            </h6>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 conditional-field" id="inTimeField">
            <label for="in_time" class="form-label">In Time <span class="text-danger">*</span></label>
            <input type="time" name="in_time" id="in_time"
                class="form-control @error('in_time') is-invalid @enderror"
                value="{{ old('in_time') }}">
            @error('in_time')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 conditional-field" id="outTimeField">
            <label for="out_time" class="form-label">Out Time <span class="text-danger">*</span></label>
            <input type="time" name="out_time" id="out_time"
                class="form-control @error('out_time') is-invalid @enderror"
                value="{{ old('out_time') }}">
            @error('out_time')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 conditional-field" id="distanceField">
            <label for="total_distance" class="form-label">Total Distance (KM) <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="number" step="0.01" min="0" name="total_distance" id="total_distance"
                    class="form-control @error('total_distance') is-invalid @enderror"
                    value="{{ old('total_distance') }}" placeholder="0.00">
                <span class="input-group-text">KM</span>
            </div>
            @error('total_distance')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Required for BRTC Bus</div>
        </div>
    </div>

    <!-- Remarks -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea name="remarks" id="remarks" rows="3"
                class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks') }}</textarea>
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
                    <i class="ti ti-check me-1"></i>Record Trip
                    <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="loadingSpinner">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Load bus details when bus is selected
    $(document).on('change', '#own-bus #bus_id, #hired-bus #bus_id, #brtc-bus #bus_id', function() {
        const $selectedOption = $(this).find('option:selected');
        const tabSelector = '#' + $(this).closest('.tab-pane').attr('id');
        
        if ($(this).val()) {
            const seatingCapacity = $selectedOption.data('seating-capacity') || '';
            const registration = $selectedOption.data('registration') || '';
            const busType = $selectedOption.data('bus-type') || '';
            const status = $selectedOption.data('status') || '';
            
            // Update bus info card
            $(tabSelector + ' #info_seating_capacity').text(seatingCapacity || '-');
            $(tabSelector + ' #info_bus_type').text(busType || '-');
            $(tabSelector + ' #info_registration').text(registration || '-');
            $(tabSelector + ' #info_status').text(status || '-');
            $(tabSelector + ' #busInfoCard').show();
            
            // Update seating capacity in passengers field
            if (seatingCapacity) {
                $(tabSelector + ' #seating_capacity_display').text(seatingCapacity);
                $(tabSelector + ' #seating_capacity_info').show();
                $(tabSelector + ' #passengers').attr('max', seatingCapacity);
            } else {
                $(tabSelector + ' #seating_capacity_info').hide();
            }
        } else {
            $(tabSelector + ' #busInfoCard').hide();
            $(tabSelector + ' #seating_capacity_info').hide();
        }
    });
});
</script>

