@if($vehicles->count() > 0)
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th width="5%">#</th>
                <th width="20%">Vehicle</th>
                <th width="18%">Start Stoppage <span class="text-danger">*</span></th>
                <th width="18%">End Stoppage <span class="text-danger">*</span></th>
                <th width="15%">Trip Time <span class="text-danger">*</span></th>
                <th width="24%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vehicles as $vehicle)
            <tr>
                <td class="text-center">
                    <span class="badge bg-primary">{{ $loop->iteration }}</span>
                </td>
                <td>
                    <input type="hidden" name="buses[{{ $vehicle->id }}][vehicle_id]" value="{{ $vehicle->id }}">
                    <input type="hidden" name="buses[{{ $vehicle->id }}][vehicle_sub_type_id]" value="{{ $vehicle->vehicle_sub_type_id }}">
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <i data-feather="truck" class="text-primary"></i>
                        </div>
                        <div>
                            <strong>{{ $vehicle->vehicleSubType->sub_type_name }}</strong><br>
                            <small class="text-muted">{{ $vehicle->model_name }} ({{ $vehicle->registration_number }})</small>
                        </div>
                    </div>
                </td>
                <td>
                    <select class="form-select start-stoppage-select" name="buses[{{ $vehicle->id }}][start_stoppage_id]" required>
                        <option value="">Select Start Stoppage</option>
                        @foreach ($stoppages as $stoppage)
                            <option value="{{ $stoppage->id }}">{{ $stoppage->stoppage_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="form-select end-stoppage-select" name="buses[{{ $vehicle->id }}][end_stoppage_id]" required>
                        <option value="">Select End Stoppage</option>
                        @foreach ($stoppages as $stoppage)
                            <option value="{{ $stoppage->id }}">{{ $stoppage->stoppage_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="form-select trip-time-select" name="buses[{{ $vehicle->id }}][trip_time_id]" required>
                        <option value="">Select Trip Time</option>
                        @foreach ($tripTimes as $tripTime)
                            <option value="{{ $tripTime->id }}">
                                {{ $tripTime->time_name }} - {{ \Carbon\Carbon::parse($tripTime->time_value)->format('H:i') }} {{ $tripTime->time_period }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <textarea class="form-control" name="buses[{{ $vehicle->id }}][remarks]" rows="2" placeholder="Enter remarks (optional)..."></textarea>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="alert alert-info">
    <i data-feather="info"></i>
    <strong>Note:</strong> Please fill in all required fields (marked with *) for each vehicle. The start and end stoppages must be different for each vehicle.
</div>
@else
<div class="text-center py-5">
    <div class="mb-3">
        <i data-feather="truck" class="text-muted" style="width: 48px; height: 48px;"></i>
    </div>
    <h5 class="text-muted">No vehicles found</h5>
    <p class="text-muted">No active vehicles found for the selected sub type.</p>
</div>
@endif
