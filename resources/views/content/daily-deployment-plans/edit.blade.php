@extends('layouts/layoutMaster')

@section('title', 'Edit Daily Deployment Plan')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}">
    <style>
        /* Checkbox dropdown for bus multi-selects */
        .bus-multi-dropdown {
            position: relative;
        }
        .bus-multi-dropdown .dropdown-toggle {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .bus-multi-dropdown .dropdown-menu {
            max-height: 220px;
            overflow-y: auto;
        }
        .bus-multi-dropdown .form-check-label {
            font-size: 0.8rem;
        }
    </style>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/deployment-plan-edit.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>

    <script>
        (function($){
            $(document).ready(function(){

                // Update the dropdown button label to show selected bus items
                function updateBusLabel($dropdown){
                    const selected = $dropdown.find('.dropdown-menu .bus-checkbox:checked')
                        .map(function(){
                            return $(this).closest('.form-check').find('label').text().trim();
                        }).get();

                    const $label = $dropdown.find('.bus-multi-label');

                    if(selected.length === 0){
                        $label.text('Select Bus');
                    } else if(selected.length <= 5){
                        $label.text(selected.join(', '));
                    } else {
                        $label.text(selected.slice(0,5).join(', ') + ' +' + (selected.length - 5) + ' more');
                    }
                }

                // Initialize labels on page load (handles pre-checked in edit scenarios)
                $('.bus-multi-dropdown').each(function(){
                    updateBusLabel($(this));
                });

                // Update label when any checkbox changes
                $(document).on('change', '.bus-checkbox', function(){
                    const $checkbox = $(this);
                    const $dropdown = $checkbox.closest('.bus-multi-dropdown');
                    updateBusLabel($dropdown);

                    // Only perform duplicate check when a checkbox is being checked
                    if (!$checkbox.is(':checked')) return;

                    // ID format used in the template: bus_{index}_{subTypeId}_{busId}
                    const idParts = ($checkbox.attr('id') || '').split('_');
                    const subTypeId = idParts[2] || '';
                    const busId = $checkbox.val();

                    // Find other checked checkboxes with same busId and same subType
                    const conflicts = [];
                    $('input.bus-checkbox:checked').not($checkbox).each(function(){
                        const $other = $(this);
                        const otherParts = ($other.attr('id') || '').split('_');
                        const otherSubType = otherParts[2] || '';
                        if (otherSubType === subTypeId && $other.val() === busId) {
                            const stoppageName = $other.closest('tr').find('td.sticky-column').text().trim()
                                || $other.closest('tr').find('input[name*="[stoppage_id]"]').val()
                                || 'stoppage';
                            if ($.inArray(stoppageName, conflicts) === -1) conflicts.push(stoppageName);
                        }
                    });

                    if (conflicts.length) {
                        const busLabel = $checkbox.closest('.form-check').find('label').text().trim() || busId;
                        const msg = `${busLabel} already selected in: ${conflicts.join(', ')}.`;
                        toastr.error(msg);
                        // revert the user's selection
                        $checkbox.prop('checked', false);
                        updateBusLabel($dropdown);
                    }
                });

                // Optional: update label when dropdown closes (in case checkboxes changed programmatically)
                $(document).on('hidden.bs.dropdown', '.bus-multi-dropdown .dropdown-menu', function(){
                    const $dropdown = $(this).closest('.bus-multi-dropdown');
                    updateBusLabel($dropdown);
                });

            });
        })(jQuery);
    </script>
   
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Daily Deployment Plan</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('deployment-plans.view-daily-deployment-plan') }}" class="btn btn-secondary">
                        <i data-feather="arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Alert Container -->
                <div id="alertContainer"></div>
                
                <form id="deploymentPlanForm" method="POST" action="{{ route('deployment-plans.update', ['deployment_plan' => $dailyDeploymentPlan->id]) }}" data-redirect-url="{{ route('deployment-plans.view-daily-deployment-plan') }}">
                    @csrf
                    @method('PUT')

                    <!-- Common Fields -->
                    <div class="row mb-4">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Deployment Date <span class="text-danger">*</span></label>
                            <input type="date" value="{{ $dailyDeploymentPlan->deployment_date->format('Y-m-d') }}" class="form-control" id="deployment_date" name="deployment_date" required>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Trip Time <span class="text-danger">*</span></label>
                            <select class="select2 form-select" id="trip_time_id" name="trip_time_id" required>
                                <option value="">Select Trip Time</option>
                                @foreach($tripTimes as $value)
                                <option value="{{ $value->id }}" {{ $dailyDeploymentPlan->trip_time_id == $value->id ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($value->time_value)->format('h:i') }} {{ $value->time_period }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Bus User <span class="text-danger">*</span></label>
                            <select class="form-select" id="bus_user_id" name="bus_user_id" required>
                                <option value="">Select Bus User</option>
                                @foreach($busUsers as $busUser)
                                <option value="{{ $busUser->id }}" {{ $dailyDeploymentPlan->bus_user_id == $busUser->id ? 'selected' : '' }}>
                                    {{ $busUser->bus_user_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-4">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Enter any remarks...">{{ $dailyDeploymentPlan->remarks }}</textarea>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Deployment Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="deployment_type_id" name="deployment_type_id" required>
                                <option value="">Select Deployment Type</option>
                                @foreach($deploymentTypes as $deploymentType)
                                <option value="{{ $deploymentType->id }}" {{ $dailyDeploymentPlan->deployment_type_id == $deploymentType->id ? 'selected' : '' }}>
                                    {{ $deploymentType->deployment_type_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Trip Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="trip_type" name="trip_type" required>
                                <option value="">Select Type</option>
                                <option value="arrival" {{ $dailyDeploymentPlan->trip_type == 'arrival' ? 'selected' : '' }}>Arrival</option>
                                <option value="departure" {{ $dailyDeploymentPlan->trip_type == 'departure' ? 'selected' : '' }}>Departure</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <!-- Table for Stoppages and Bus Sub-Types -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0" id="deploymentTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th class="sticky-column">Stoppage <span class="text-danger">*</span></th>
                                            @foreach($busSubTypes as $busSubType)
                                            <th class="min-width-200">{{ $busSubType->sub_type_name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // Group items by stoppage
                                            $itemsByStoppage = $dailyDeploymentPlan->items->groupBy('stoppage_id');
                                            // Get all stoppage IDs that have items
                                            $stoppageIdsWithItems = $itemsByStoppage->keys();
                                            // Get all stoppages
                                            $allStoppages = $stoppages->keyBy('id');
                                        @endphp
                                        @foreach($stoppages as $index => $stoppage)
                                        @php
                                            $stoppageItems = $itemsByStoppage->get($stoppage->id, collect());
                                            $assignmentsBySubType = $stoppageItems->keyBy('bus_sub_type_id');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="sticky-column fw-semibold">{{ $stoppage->stoppage_name }}
                                                <input type="hidden" name="items[{{ $index }}][stoppage_id]" value="{{ $stoppage->id }}">
                                            </td>
                                            @foreach($busSubTypes as $busSubType)
                                            @php
                                                // Collect all selected bus IDs for this stoppage and sub-type
                                                $assignmentsForSubType = $stoppageItems->where('bus_sub_type_id', $busSubType->id);
                                                $selectedBusIds = [];
                                                foreach ($assignmentsForSubType as $assignment) {
                                                    if (!empty($assignment->bus_id)) {
                                                        $selectedBusIds[] = (int) $assignment->bus_id;
                                                    }
                                                }

                                                // Get buses for this sub-type
                                                $buses = [];
                                                if($busSubType->id == \App\Models\BusSubType::OWN_BUS_SUB_TYPE_ID) {
                                                    $buses = $ownBus;
                                                } elseif($busSubType->id == \App\Models\BusSubType::BRTC_BUS_SUB_TYPE_ID) {
                                                    $buses = $brtcBuses;
                                                } elseif($busSubType->id == \App\Models\BusSubType::HIRED_BUS_SUB_TYPE_ID) {
                                                    $buses = $hiredBus;
                                                }
                                            @endphp
                                            <td>
                                                <div class="dropdown w-100 bus-multi-dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm w-100 text-start dropdown-toggle"
                                                            type="button"
                                                            data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                        <span class="bus-multi-label">
                                                            @if(count($selectedBusIds))
                                                                {{ count($selectedBusIds) }} selected
                                                            @else
                                                                Select Bus
                                                            @endif
                                                        </span>
                                                    </button>
                                                    <div class="dropdown-menu p-2">
                                                        @foreach($buses as $bus)
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input bus-checkbox"
                                                                   type="checkbox"
                                                                   value="{{ $bus->id }}"
                                                                   id="bus_{{ $index }}_{{ $busSubType->id }}_{{ $bus->id }}"
                                                                   name="items[{{ $index }}][bus_assignments][{{ $busSubType->id }}][bus_id][]"
                                                                   {{ in_array((int) $bus->id, $selectedBusIds, true) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="bus_{{ $index }}_{{ $busSubType->id }}_{{ $bus->id }}">
                                                                {{ $bus->bus_number }}
                                                            </label>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <input type="hidden" name="items[{{ $index }}][bus_assignments][{{ $busSubType->id }}][bus_sub_type_id]" value="{{ $busSubType->id }}">
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner" role="status"></span>
                                    <span id="submitText">
                                        <i data-feather="save"></i> <span class="d-none d-sm-inline">Update Deployment Plan</span>
                                        <span class="d-inline d-sm-none">Update</span>
                                    </span>
                                </button>
                                <a href="{{ route('deployment-plans.view-daily-deployment-plan') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

