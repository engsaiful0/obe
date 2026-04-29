@php
$isBRTC = $tabPrefix === 'brtc';
$isOwnBus = $tabPrefix === 'own';
$globalIndex = 0; // This will be used to maintain unique indices across all tabs
@endphp

@if(count($buses) > 0)
    <div class="table-responsive mb-4">
        <table class="table table-striped table-hover table-bordered busesTable-{{ $tabPrefix }}" id="busesTable-{{ $tabPrefix }}">
            <thead>
                <tr>
                   
                    <th width="3%">#</th>
                    <th width="12%"> Bus Number</th>
                    @if($isOwnBus)
                    <th width="12%">Alternate Driver<br>Helper</th>
                    @endif
                    <th width="10%">Start Stoppage<br>End Stoppage</th>
                    <th width="10%">No.of Pass</th>
                    <th width="10%">Trip Type</th>
                    <th width="8%">Hour:Minute (AM/PM)</th>
                    @if($isBRTC)
                    <th width="8%">Distance (KM)</th>
                    @endif
                    <th>Trip Number</th>
                    <th width="8%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($buses as $bus)
                @php
                $index = $loop->index;
                $uniqueIndex = $tabPrefix . '_' . $index;
                @endphp
                <tr data-bus-id="{{ $bus->id }}" data-sub-type="{{ $bus->busSubType->sub_type_name ?? '' }}" data-tab="{{ $tabPrefix }}">
                  
                    <td>{{ $loop->iteration }}</td>
                    <td>
                       {{ $bus->bus_number }}
                    </td>

                    @if($isOwnBus)
                    <td>
                        <select name="trips[{{ $uniqueIndex }}][alternate_driver_id]"
                            class="form-select form-select-sm">
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ $driver->full_name }}
                            </option>
                            @endforeach
                        </select>
                        <select name="trips[{{ $uniqueIndex }}][alternate_bus_helper_id]"
                            class="form-select form-select-sm mt-1">
                            <option value="">Select Helper</option>
                            @foreach($busHelpers as $helper)
                            <option value="{{ $helper->id }}">
                                {{ $helper->bus_helper_name }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    @endif

                    <td>
                        <select name="trips[{{ $uniqueIndex }}][start_stoppage_id]"
                            class="select2 form-select form-select-sm start-stoppage-select">
                            <option value="">Select Start Stoppage</option>
                            @foreach($stoppages as $stoppage)
                            <option value="{{ $stoppage->id }}" {{ old('trips.'.$uniqueIndex.'.start_stoppage_id') == $stoppage->id ? 'selected' : '' }}>
                                {{ $stoppage->stoppage_name }}
                            </option>
                            @endforeach
                        </select>
                        <br>
                        <select name="trips[{{ $uniqueIndex }}][end_stoppage_id]"
                            class="select2 form-select form-select-sm end-stoppage-select mt-1">
                            <option value="">Select End Stoppage</option>
                            @foreach($stoppages as $stoppage)
                            <option value="{{ $stoppage->id }}" {{ old('trips.'.$uniqueIndex.'.end_stoppage_id') == $stoppage->id ? 'selected' : '' }}>
                                {{ $stoppage->stoppage_name }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    
                    <td>
                        <input style="width:100px" name="trips[{{ $uniqueIndex }}][passengers]"
                            type="number"
                            class="form-control form-control-sm"
                            min="0"
                            placeholder="0">
                    </td>
                    <td >
                        @php
                            $selectedTripType = old('trips.'.$uniqueIndex.'.trip_type', $defaultTripType ?? '');
                        @endphp
                        <select style="width:100px" name="trips[{{ $uniqueIndex }}][trip_type]"
                            class=" form-select form-select-sm trip-type-select">
                            <option>Select</option>
                            <option value="arrival" {{ $selectedTripType == 'arrival' ? 'selected' : '' }}>Arrival</option>
                            <option value="departure" {{ $selectedTripType == 'departure' ? 'selected' : '' }}>Departure</option>
                        </select>
                    </td>
                    <td>
                        @php
                            $selectedHours = old('trips.'.$uniqueIndex.'.hours', $currentHour);
                            $selectedMinutes = old('trips.'.$uniqueIndex.'.minutes', $currentMinute);
                            $selectedAmPm = old('trips.'.$uniqueIndex.'.am_pm', $currentAmPm);
                            // Ensure hour is an integer for comparison, use current hour as fallback
                            $selectedHours = ($selectedHours !== null && $selectedHours !== '') ? (int)$selectedHours : (int)$currentHour;
                        @endphp
                        <div class="d-flex gap-1 align-items-center time-select-group" data-index="{{ $uniqueIndex }}">
                            <select name="trips[{{ $uniqueIndex }}][hours]" class="form-select form-select-sm" style="width: auto;">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $selectedHours == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>

                            <select name="trips[{{ $uniqueIndex }}][minutes]" class="form-select form-select-sm" style="width: auto;">
                                @for($i = 0; $i <= 59; $i++)
                                    @php $min=str_pad($i, 2, '0' , STR_PAD_LEFT); @endphp
                                    <option value="{{ $min }}" {{ $min === (string)$selectedMinutes ? 'selected' : '' }}>{{ $min }}</option>
                                @endfor
                            </select>

                            <select name="trips[{{ $uniqueIndex }}][am_pm]" class="form-select form-select-sm" style="width: auto;">
                                <option value="am" {{ $selectedAmPm === 'am' ? 'selected' : '' }}>AM</option>
                                <option value="pm" {{ $selectedAmPm === 'pm' ? 'selected' : '' }}>PM</option>
                            </select>
                        </div>
                    </td>
                    @if($isBRTC)
                    <td>
                        <input type="number" 
                               name="trips[{{ $uniqueIndex }}][total_distance]" 
                               class="form-control form-control-sm distance-input" 
                               step="0.01" 
                               min="0" 
                               placeholder="KM">
                    </td>
                    @endif
                    <td>
                        <input readonly class="form-control form-control-sm distance-input" placeholder="TN" type="number"  name="trips[{{ $uniqueIndex }}][trip_number]" value="{{ $busTripNumbers[$bus->id] ?? '' }}">
                    </td>
                    <td>
                        <button type="button" 
                                class="btn btn-primary btn-sm save-single-trip" 
                                data-unique-index="{{ $uniqueIndex }}"
                                data-bus-id="{{ $bus->id }}"
                                data-bus-number="{{ $bus->bus_number }}">
                            <span class="btn-text">Save</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </span>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Save All Button for this tab -->
    <div class="d-flex justify-content-end mb-3">
        <button type="button" 
                class="btn btn-success save-all-trips" 
                data-tab="{{ $tabPrefix }}">
            <i class="ti ti-device-floppy me-1"></i>
            <span class="btn-text">Save All {{ ucfirst($tabPrefix) }} Bus Trips</span>
            <span class="spinner-border spinner-border-sm d-none" role="status">
                <span class="visually-hidden">Loading...</span>
            </span>
        </button>
    </div>
@else
    <div class="alert alert-info">
        <i class="ti ti-info-circle me-2"></i>
        No {{ ucfirst(str_replace('-', ' ', $tabPrefix)) }} buses available.
    </div>
@endif

