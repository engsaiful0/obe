@extends('layouts/layoutMaster')

@section('title', 'Add All Bus Trips')

@section('page-style')
<style>
    .form-control:disabled,
    .form-select:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
    }

    .table-responsive {
        border-radius: 0.375rem;
        overflow: hidden;
    }

    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.75em;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .bus-info-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    .save-single-trip .spinner-border,
    .save-all-trips .spinner-border {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }

    .table-success {
        background-color: #d1e7dd !important;
        transition: background-color 0.3s ease;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }

    .table-danger {
        background-color: #f8d7da !important;
        transition: background-color 0.3s ease;
    }

    .save-single-trip .spinner-border,
    .save-all-trips .spinner-border {
        margin-left: 0.5rem;
    }

    .save-single-trip:disabled,
    .save-all-trips:disabled {
        pointer-events: none;
    }
</style>
@endsection

@section('content')
@php
if (!function_exists('getDefaultTripType')) {
    function getDefaultTripType($hour24)
    {
        return $hour24 >= 12 ? 'out' : 'in';
    }
}

if (!function_exists('getDefaultStoppages')) {
    function getDefaultStoppages($hour, $amPm, $iiucStoppageId)
    {
        $defaults = [
            'start_stoppage_id' => null,
            'end_stoppage_id' => null
        ];

        if ($amPm === 'am') {
            if ($hour >= 6 && $hour <= 11) {
                $defaults['end_stoppage_id'] = $iiucStoppageId;
            }
        } elseif ($amPm === 'pm') {
            if ($hour == 12 || ($hour >= 1 && $hour <= 5)) {
                $defaults['start_stoppage_id'] = $iiucStoppageId;
            }
        }

        return $defaults;
    }
}

// Get current time using application timezone
$now = \Carbon\Carbon::now(config('app.timezone'));
$currentHour = (int)$now->format('g'); // 1-12 format
$currentMinute = $now->format('i'); // 00-59 format
$currentAmPm = strtolower($now->format('a')); // 'am' or 'pm'
$currentHour24 = (int)$now->format('H');
$defaultTripType = $currentHour24 >= 12 ? 'out' : 'in';

// Find IIUC stoppage
$iiucStoppage = $stoppages->firstWhere('stoppage_name', 'IIUC');
$iiucStoppageId = $iiucStoppage ? $iiucStoppage->id : null;

// Get default stoppages based on time
// 6 AM to 11 AM: End Stoppage = IIUC
// 12 PM to 5 PM: Start Stoppage = IIUC
$defaultStoppages = getDefaultStoppages($currentHour, $currentAmPm, $iiucStoppageId);
$defaultStartStoppageId = $defaultStoppages['start_stoppage_id'] ?? null;
$defaultEndStoppageId = $defaultStoppages['end_stoppage_id'] ?? null;
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Add All Bus Trips</h5>
        <a href="{{ route('bus-trips.index') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to List
        </a>
    </div>
    <div class="card-body" id="addAllCardBody"
        data-iiuc-stoppage="{{ $iiucStoppageId }}"
        data-default-trip-type="{{ $defaultTripType }}"
        data-default-start-stoppage="{{ $defaultStartStoppageId }}"
        data-default-end-stoppage="{{ $defaultEndStoppageId }}">
        <!-- Alert Messages -->
        <div id="alertContainer" class="d-none mb-3">
            <div class="alert alert-dismissible fade show" role="alert" id="alertMessage">
                <i class="me-2" id="alertIcon"></i>
                <span id="alertText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>

        <form action="{{ route('bus-trips.store') }}" method="POST" id="bulkTripForm">
            @csrf

            <!-- Date and Trip Type Selection -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="trip_date" class="form-label">Trip Date <span class="text-danger">*</span></label>
                    <input type="date" name="trip_date" id="trip_date"
                        class="form-control @error('trip_date') is-invalid @enderror"
                        value="{{ old('trip_date', $today ?? date('Y-m-d')) }}" 
                        max="{{ date('Y-m-d') }}" required>
                    <small class="text-muted d-block mt-1">You can select previous dates. Trip numbers will be automatically calculated for the selected date.</small>
                    @error('trip_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @php
            // Organize buses by sub type
            $busesCollection = collect($buses);
            $ownBuses = $busesCollection->filter(function($bus) {
                return $bus->bus_sub_type_id == \App\Models\BusSubType::OWN_BUS_SUB_TYPE_ID;
            });
            $brtcBuses = $busesCollection->filter(function($bus) {
                return $bus->bus_sub_type_id == \App\Models\BusSubType::BRTC_BUS_SUB_TYPE_ID;
            });
            $hiredBuses = $busesCollection->filter(function($bus) {
                return $bus->bus_sub_type_id == \App\Models\BusSubType::HIRED_BUS_SUB_TYPE_ID;
            });
            @endphp

            <!-- Bus Type Tabs -->
            <ul class="nav nav-tabs mb-3" id="busTypeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="own-bus-tab" data-bs-toggle="tab" data-bs-target="#own-bus" type="button" role="tab" aria-controls="own-bus" aria-selected="true">
                        Own Bus <span class="badge bg-primary ms-1">{{ $ownBuses->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="brtc-bus-tab" data-bs-toggle="tab" data-bs-target="#brtc-bus" type="button" role="tab" aria-controls="brtc-bus" aria-selected="false">
                        BRTC Bus <span class="badge bg-primary ms-1">{{ $brtcBuses->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="hired-bus-tab" data-bs-toggle="tab" data-bs-target="#hired-bus" type="button" role="tab" aria-controls="hired-bus" aria-selected="false">
                        Hired Bus <span class="badge bg-primary ms-1">{{ $hiredBuses->count() }}</span>
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="busTypeTabContent">
                <!-- Own Bus Tab -->
                <div class="tab-pane fade show active" id="own-bus" role="tabpanel" aria-labelledby="own-bus-tab">
                    @include('content.bus-trips.partials.own-bus-table', [
                        'buses' => $ownBuses,
                        'drivers' => $drivers,
                        'busHelpers' => $busHelpers,
                        'stoppages' => $stoppages,
                        'currentHour' => $currentHour,
                        'currentMinute' => $currentMinute,
                        'currentAmPm' => $currentAmPm,
                        'defaultTripType' => $defaultTripType,
                        'tabPrefix' => 'own',
                        'busTripNumbers' => $busTripNumbers ?? []
                    ])
                </div>

                <!-- BRTC Bus Tab -->
                <div class="tab-pane fade" id="brtc-bus" role="tabpanel" aria-labelledby="brtc-bus-tab">
                    @include('content.bus-trips.partials.brtc-bus-table', [
                        'buses' => $brtcBuses,
                        'drivers' => $drivers,
                        'busHelpers' => $busHelpers,
                        'stoppages' => $stoppages,
                        'currentHour' => $currentHour,
                        'currentMinute' => $currentMinute,
                        'currentAmPm' => $currentAmPm,
                        'defaultTripType' => $defaultTripType,
                        'defaultStoppages' => $defaultStoppages,
                        'iiucStoppageId' => $iiucStoppageId,
                        'tabPrefix' => 'brtc',
                        'busTripNumbers' => $busTripNumbers ?? []
                    ])
                </div>

                <!-- Hired Bus Tab -->
                <div class="tab-pane fade" id="hired-bus" role="tabpanel" aria-labelledby="hired-bus-tab">
                    @include('content.bus-trips.partials.hired-bus-table', [
                        'buses' => $hiredBuses,
                        'drivers' => $drivers,
                        'busHelpers' => $busHelpers,
                        'stoppages' => $stoppages,
                        'currentHour' => $currentHour,
                        'currentMinute' => $currentMinute,
                        'currentAmPm' => $currentAmPm,
                        'defaultTripType' => $defaultTripType,
                        'defaultStoppages' => $defaultStoppages,
                        'iiucStoppageId' => $iiucStoppageId,
                        'tabPrefix' => 'hired',
                        'busTripNumbers' => $busTripNumbers ?? []
                    ])
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        const pageCardBody = $('#addAllCardBody');
        const iiucStoppageId = pageCardBody.data('iiuc-stoppage') || null;
        const systemDefaultTripType = pageCardBody.data('default-trip-type') || null;
        const systemDefaultStartStoppage = pageCardBody.data('default-start-stoppage') || null;
        const systemDefaultEndStoppage = pageCardBody.data('default-end-stoppage') || null;

        // Function to update trip numbers when date changes
        function updateTripNumbersForDate(tripDate) {
            if (!tripDate) {
                return;
            }

            // Get all bus IDs from all tabs
            const busIds = [];
            $('input[name*="[bus_id]"]').each(function() {
                const busId = $(this).val();
                if (busId && busIds.indexOf(busId) === -1) {
                    busIds.push(busId);
                }
            });

            if (busIds.length === 0) {
                return;
            }

            // Show loading state
            $('input[name*="[trip_number]"]').prop('disabled', true).val('...');

            // AJAX request to get trip numbers for the selected date
            $.ajax({
                url: '{{ route("bus-trips.get-trip-numbers-for-date") }}',
                type: 'POST',
                data: {
                    trip_date: tripDate,
                    bus_ids: busIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success && response.trip_numbers) {
                        // Update trip numbers for each bus
                        $('input[name*="[bus_id]"]').each(function() {
                            const busId = $(this).val();
                            const tripNumber = response.trip_numbers[busId] || 1;
                            const $row = $(this).closest('tr');
                            $row.find('input[name*="[trip_number]"]').val(tripNumber).prop('disabled', false);
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error updating trip numbers:', xhr);
                    // Re-enable inputs even on error
                    $('input[name*="[trip_number]"]').prop('disabled', false);
                    // Try to restore previous values or set to 1
                    $('input[name*="[trip_number]"]').each(function() {
                        if (!$(this).val() || $(this).val() === '...') {
                            $(this).val(1);
                        }
                    });
                }
            });
        }

        // Handle date change
        $('#trip_date').on('change', function() {
            const selectedDate = $(this).val();
            if (selectedDate) {
                updateTripNumbersForDate(selectedDate);
            }
        });
        // Initialize Select2 for active tab
        function initializeSelect2(tabPrefix) {
            if ($.fn.select2) {
                $(`.tab-pane#${tabPrefix}-bus .select2`).select2({
                    dropdownParent: $('.card-body')
                });
                
                // For BRTC tab, auto-populate distance if stoppages are already selected
                if (tabPrefix === 'brtc') {
                    setTimeout(function() {
                        $(`.tab-pane#${tabPrefix}-bus tbody tr`).each(function() {
                            const $row = $(this);
                            if ($row.data('tab') === 'brtc') {
                                const $startSelect = $row.find('.start-stoppage-select').first();
                                const $endSelect = $row.find('.end-stoppage-select').first();
                                const uniqueIndex = $startSelect.data('index');
                                
                                if (uniqueIndex) {
                                    // Check start stoppage first, then end stoppage
                                    const startStoppageId = $startSelect.val();
                                    const endStoppageId = $endSelect.val();
                                    
                                    // Use the selected stoppage (prefer end if both are selected)
                                    const selectedStoppageId = endStoppageId || startStoppageId;
                                    if (selectedStoppageId) {
                                      //  fetchDistanceForStoppages(uniqueIndex, $row, selectedStoppageId);
                                    }
                                }
                            }
                        });
                    }, 200);
                }
            }
        }

        // Initialize Select2 for the active tab on page load
        initializeSelect2('own');

        // Handle tab switching - ensure tables are shown/hidden correctly
        function switchTab(tabButton) {
            const targetId = $(tabButton).data('bs-target');
            const tabPrefix = targetId.replace('#', '').replace('-bus', '');
            
            // Remove active class from all tabs
            $('#busTypeTabs .nav-link').removeClass('active').attr('aria-selected', 'false');
            
            // Remove show and active classes from all tab panes
            $('#busTypeTabContent .tab-pane').removeClass('show active');
            
            // Add active class to clicked tab
            $(tabButton).addClass('active').attr('aria-selected', 'true');
            
            // Show the target tab pane
            $(targetId).addClass('show active');
            
            // Initialize Select2 for the newly active tab
            setTimeout(function() {
                initializeSelect2(tabPrefix);
            }, 100);
        }

        // Handle tab clicks
        $('#own-bus-tab, #hired-bus-tab, #brtc-bus-tab').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            switchTab(this);
        });

        // Also handle Bootstrap's tab event if it fires
        $('#busTypeTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const targetTab = $(e.target).data('bs-target').replace('#', '');
            const tabPrefix = targetTab.replace('-bus', '');
            initializeSelect2(tabPrefix);
        });

        // Select All checkbox per tab
        $('.selectAll-own, .selectAll-brtc, .selectAll-hired').on('change', function() {
            const tabPrefix = $(this).data('tab');
            $(`.trip-checkbox-${tabPrefix}`).prop('checked', $(this).prop('checked'));
        });

        // Update select all when individual checkboxes change
        $(document).on('change', '.trip-checkbox', function() {
            const tabPrefix = $(this).closest('tr').data('tab');
            const total = $(`.trip-checkbox-${tabPrefix}`).length;
            const checked = $(`.trip-checkbox-${tabPrefix}:checked`).length;
            $(`.selectAll-${tabPrefix}`).prop('checked', total === checked && total > 0);
        });

        // Apply default values to per-row fields
        function applyDefaults() {
            const defaultTripType = $('#trip_type').val() || systemDefaultTripType;
            const defaultStartStoppage = $('#start_stoppage_id').val() || systemDefaultStartStoppage;
            const defaultEndStoppage = $('#end_stoppage_id').val() || systemDefaultEndStoppage;

            $('.trip-type-select').each(function() {
                if (!$(this).val() && defaultTripType) {
                    $(this).val(defaultTripType).trigger('change');
                }
            });

            $('.start-stoppage-select').each(function() {
                if (!$(this).val() && defaultStartStoppage) {
                    $(this).val(defaultStartStoppage).trigger('change');
                }
            });

            $('.end-stoppage-select').each(function() {
                if (!$(this).val() && defaultEndStoppage) {
                    $(this).val(defaultEndStoppage).trigger('change');
                }
            });
        }

        // Apply defaults when global fields change
        $('#trip_type, #start_stoppage_id, #end_stoppage_id').on('change', function() {
            applyDefaults();
        });

        // Initialize
        applyDefaults();

        function getDefaultTripTypeFromTime(hour, amPm) {
            if (!hour || !amPm) {
                return '';
            }

            let hourInt = parseInt(hour, 10);
            if (isNaN(hourInt)) {
                return '';
            }

            hourInt = hourInt % 12;
            if (amPm.toLowerCase() === 'pm') {
                hourInt += 12;
            }

            return hourInt >= 12 ? 'out' : 'in';
        }

        // Function to determine default stoppages based on time
        // 6 AM to 11 AM: End Stoppage = IIUC
        // 12 PM to 5 PM: Start Stoppage = IIUC
        function getDefaultStoppagesFromTime(hour, amPm, iiucId) {
            const defaults = {
                start_stoppage_id: null,
                end_stoppage_id: null
            };
            
            if (!iiucId) {
                return defaults;
            }
            
            const hourInt = parseInt(hour);
            if (amPm === 'am') {
                // 6 AM to 11 AM: End Stoppage = IIUC
                if (hourInt >= 6 && hourInt <= 11) {
                    defaults.end_stoppage_id = iiucId;
                }
            } else if (amPm === 'pm') {
                // 12 PM to 5 PM: Start Stoppage = IIUC
                if (hourInt == 12 || (hourInt >= 1 && hourInt <= 5)) {
                    defaults.start_stoppage_id = iiucId;
                }
            }
            
            return defaults;
        }

        // Auto-update trip type and stoppages when time changes (only for BRTC and Hired Bus)
        $(document).on('change', '#brtc-bus .time-select-group select, #hired-bus .time-select-group select', function() {
            const $timeGroup = $(this).closest('.time-select-group');
            const uniqueIndex = $timeGroup.data('index');
            const tabPrefix = $timeGroup.closest('.tab-pane').attr('id').replace('-bus', '');
            
            // Only apply to BRTC and Hired Bus tabs
            if (tabPrefix !== 'brtc' && tabPrefix !== 'hired') {
                return;
            }
            
            const $row = $timeGroup.closest('tr');
            const hours = $row.find(`select[name="trips[${uniqueIndex}][hours]"]`).val();
            const amPm = $row.find(`select[name="trips[${uniqueIndex}][am_pm]"]`).val();
            const $tripTypeSelect = $row.find(`select[name="trips[${uniqueIndex}][trip_type]"].auto-trip-type`);
            const $startStoppageSelect = $row.find(`select[name="trips[${uniqueIndex}][start_stoppage_id]"].auto-stoppage-select`);
            const $endStoppageSelect = $row.find(`select[name="trips[${uniqueIndex}][end_stoppage_id]"].auto-stoppage-select`);
            
            if (hours && amPm) {
                // Update trip type
                if ($tripTypeSelect.length) {
                    const defaultTripType = getDefaultTripTypeFromTime(hours, amPm);
                    
                    // Auto-update trip type based on time range
                    // 6 AM - 11 AM = IN, 12 PM - 5 PM = OUT
                    if (defaultTripType) {
                        $tripTypeSelect.val(defaultTripType).trigger('change');
                    } else {
                        // If time is outside the default ranges, clear the selection
                        // Users can still manually select if needed
                        if (!$tripTypeSelect.val()) {
                            $tripTypeSelect.val('').trigger('change');
                        }
                    }
                }
                
                // Update stoppages
                if (iiucStoppageId) {
                    const defaultStoppages = getDefaultStoppagesFromTime(hours, amPm, iiucStoppageId);
                    
                    // Update start stoppage if default is set
                    if (defaultStoppages.start_stoppage_id && $startStoppageSelect.length) {
                        // Only auto-update if currently empty
                        if (!$startStoppageSelect.val()) {
                            $startStoppageSelect.val(defaultStoppages.start_stoppage_id).trigger('change');
                        }
                    }
                    
                    // Update end stoppage if default is set
                    if (defaultStoppages.end_stoppage_id && $endStoppageSelect.length) {
                        // Only auto-update if currently empty
                        if (!$endStoppageSelect.val()) {
                            $endStoppageSelect.val(defaultStoppages.end_stoppage_id).trigger('change');
                        }
                    }
                }
            }
        });

        // Function to fetch and populate distance when stoppages change (only for BRTC)
        function fetchDistanceForStoppages(uniqueIndex, $row, stoppageId) {
            const $distanceInput = $row.find(`input[name="trips[${uniqueIndex}][total_distance]"]`);

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

        // Handle stoppage changes for BRTC buses to auto-populate distance
        // Handle both regular change and Select2 change events
        $(document).on('change', '#brtc-bus .start-stoppage-select, #brtc-bus .end-stoppage-select', function() {
            const $select = $(this);
            const uniqueIndex = $select.data('index');
            const $row = $select.closest('tr');
            const stoppageId = $select.val();
            
            // Only process if this is a BRTC bus row
            if ($row.data('tab') === 'brtc') {
                fetchDistanceForStoppages(uniqueIndex, $row, stoppageId);
            }
        });

        // Also handle Select2 change events specifically (in case regular change doesn't fire)
        $(document).on('select2:select select2:change', '#brtc-bus .start-stoppage-select, #brtc-bus .end-stoppage-select', function() {
            const $select = $(this);
            const uniqueIndex = $select.data('index');
            const $row = $select.closest('tr');
            const stoppageId = $select.val();
            
            // Only process if this is a BRTC bus row
            if ($row.data('tab') === 'brtc') {
                fetchDistanceForStoppages(uniqueIndex, $row, stoppageId);
            }
        });

        // Function to convert 12-hour time to 24-hour format
        function convertTo24Hour(hours, minutes, amPm) {
            let hour24 = parseInt(hours);
            if (amPm === 'pm' && hour24 !== 12) {
                hour24 += 12;
            } else if (amPm === 'am' && hour24 === 12) {
                hour24 = 0;
            }
            return String(hour24).padStart(2, '0') + ':' + minutes + ':00';
        }

        // Function to validate trip data
        function validateTripData(uniqueIndex, $row, tabPrefix) {
            const errors = [];
            const busNumber = $row.find('td:eq(1) strong').text();
            const rowTabPrefix = $row.data('tab') || tabPrefix;
            const isOwnBus = rowTabPrefix === 'own';
            const isBRTCBus = rowTabPrefix === 'brtc';
            const isHiredBus = rowTabPrefix === 'hired';

            // Validate driver_id (required only for Own Bus, optional for BRTC and Hired Bus)
            if (isOwnBus) {
                const driverId = $row.find(`input[name="trips[${uniqueIndex}][driver_id]"]`).val();
                const alternateDriverId = $row.find(`select[name="trips[${uniqueIndex}][alternate_driver_id]"]`).val();
                if (!driverId && !alternateDriverId) {
                    errors.push(`Bus ${busNumber}: Driver is required (assign a driver to the bus or select an alternate driver)`);
                }
            }
            // BRTC and Hired Bus: driver is optional, no validation needed

            // Get trip type
            const tripType = $row.find(`select[name="trips[${uniqueIndex}][trip_type]"]`).val();
            if (!tripType) {
                errors.push(`Bus ${busNumber}: Trip type is required`);
            }

            // Get stoppages
            const startStoppage = $row.find(`select[name="trips[${uniqueIndex}][start_stoppage_id]"]`).val();
            if (!startStoppage) {
                errors.push(`Bus ${busNumber}: Start stoppage is required`);
            }

            const endStoppage = $row.find(`select[name="trips[${uniqueIndex}][end_stoppage_id]"]`).val();
            if (!endStoppage) {
                errors.push(`Bus ${busNumber}: End stoppage is required`);
            }

            // Validate time
            const hours = $row.find(`select[name="trips[${uniqueIndex}][hours]"]`).val();
            const minutes = $row.find(`select[name="trips[${uniqueIndex}][minutes]"]`).val();
            const amPm = $row.find(`select[name="trips[${uniqueIndex}][am_pm]"]`).val();
            
            if (!hours || !minutes || !amPm) {
                errors.push(`Bus ${busNumber}: Time is required`);
            }

            // Validate distance ONLY for BRTC buses
            if (isBRTCBus) {
                const distance = $row.find(`input[name="trips[${uniqueIndex}][total_distance]"]`).val();
                if (!distance || parseFloat(distance) <= 0) {
                    errors.push(`Bus ${busNumber}: Distance is required for BRTC buses`);
                }
            }

            return errors;
        }

        // Function to prepare trip data for submission
        function prepareTripData(uniqueIndex, $row) {
            const tripType = $row.find(`select[name="trips[${uniqueIndex}][trip_type]"]`).val();
            const startStoppage = $row.find(`select[name="trips[${uniqueIndex}][start_stoppage_id]"]`).val();
            const endStoppage = $row.find(`select[name="trips[${uniqueIndex}][end_stoppage_id]"]`).val();
            
            // Get time components
            const hours = $row.find(`select[name="trips[${uniqueIndex}][hours]"]`).val();
            const minutes = $row.find(`select[name="trips[${uniqueIndex}][minutes]"]`).val();
            const amPm = $row.find(`select[name="trips[${uniqueIndex}][am_pm]"]`).val();
            
            // Convert to 24-hour format
            const time24 = convertTo24Hour(hours, minutes, amPm);

            // Get driver_id - use alternate if bus doesn't have one (only for Own Bus)
            const tabPrefix = $row.data('tab');
            const isOwnBus = tabPrefix === 'own';
            const driverId = $row.find(`input[name="trips[${uniqueIndex}][driver_id]"]`).val();
            let alternateDriverId = null;
            let alternateBusHelperId = null;
            
            if (isOwnBus) {
                alternateDriverId = $row.find(`select[name="trips[${uniqueIndex}][alternate_driver_id]"]`).val();
                alternateBusHelperId = $row.find(`select[name="trips[${uniqueIndex}][alternate_bus_helper_id]"]`).val();
            }
            
            const finalDriverId = driverId || alternateDriverId;
            
            // Ensure driver_id is not empty string
            if (!finalDriverId || finalDriverId === '') {
                console.error('No driver_id found for trip:', uniqueIndex);
            }

            // Helper function to convert empty strings to null
            function emptyToNull(value) {
                return (value === '' || value === null || value === undefined) ? null : value;
            }
            
            const tripData = {
                bus_id: $row.find(`input[name="trips[${uniqueIndex}][bus_id]"]`).val(),
                driver_id: emptyToNull(finalDriverId),
                bus_sub_type_id: $row.find(`input[name="trips[${uniqueIndex}][bus_sub_type_id]"]`).val(),
                bus_helper_id: emptyToNull($row.find(`input[name="trips[${uniqueIndex}][bus_helper_id]"]`).val()),
                alternate_driver_id: emptyToNull(alternateDriverId),
                alternate_bus_helper_id: emptyToNull(alternateBusHelperId),
                passengers: emptyToNull($row.find(`input[name="trips[${uniqueIndex}][passengers]"]`).val()),
                trip_date: $('#trip_date').val(),
                trip_type: tripType,
                trip_number: emptyToNull($row.find(`input[name="trips[${uniqueIndex}][trip_number]"]`).val()),
                start_stoppage_id: startStoppage,
                end_stoppage_id: endStoppage,
                remarks: emptyToNull($('#remarks').val())
            };
            
            // Log trip data for debugging
            console.log('Prepared trip data:', tripData);
            console.log('Bus Sub Type ID:', tripData.bus_sub_type_id, 'Expected for tab:', tabPrefix);

            // Add time based on trip type
            if (tripType === 'in') {
                tripData.in_time = time24;
            } else if (tripType === 'out') {
                tripData.out_time = time24;
            }

            // Add distance for BRTC buses
            const distance = $row.find(`input[name="trips[${uniqueIndex}][total_distance]"]`).val();
            if (distance) {
                tripData.total_distance = distance;
            }

            return tripData;
        }

        // Get route based on tab prefix
        function getStoreRoute(tabPrefix) {
            switch(tabPrefix) {
                case 'own':
                    return '{{ route("bus-trips.store-own-bus") }}';
                case 'brtc':
                    return '{{ route("bus-trips.store-brtc-bus") }}';
                case 'hired':
                    return '{{ route("bus-trips.store-hired-bus") }}';
                default:
                    return '{{ route("bus-trips.store") }}';
            }
        }

        // Save single trip
        $(document).on('click', '.save-single-trip', function() {
            const $btn = $(this);
            const uniqueIndex = $btn.data('unique-index');
            const busId = $btn.data('bus-id');
            const busNumber = $btn.data('bus-number');
            const $row = $btn.closest('tr');
            const tabPrefix = $row.data('tab');
            
            // Prevent double submission
            if ($btn.prop('disabled')) {
                return;
            }
            
            // Validate trip date
            if (!$('#trip_date').val()) {
                showAlert('danger', 'Please select a trip date');
                return;
            }

            // Validate trip data
            const errors = validateTripData(uniqueIndex, $row, tabPrefix);
            if (errors.length > 0) {
                showAlert('danger', errors.join('<br>'));
                return;
            }

            // Prepare trip data
            const tripData = prepareTripData(uniqueIndex, $row);

            // Show spinner and disable button
            $btn.prop('disabled', true);
            $btn.find('.btn-text').text('Saving...');
            $btn.find('.spinner-border').removeClass('d-none');

            // Get the correct route based on tab
            const storeUrl = getStoreRoute(tabPrefix);

            // Submit via AJAX
            $.ajax({
                url: storeUrl,
                type: 'POST',
                data: tripData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Reset button state
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').text('Save');
                    $btn.find('.spinner-border').addClass('d-none');
                    
                    showAlert('success', `Bus trip for ${busNumber} saved successfully!`);
                    
                    // Mark row as saved with visual feedback
                    $row.addClass('table-success');
                    $btn.removeClass('btn-primary').addClass('btn-success');
                    $btn.find('.btn-text').html('<i class="ti ti-check me-1"></i>Saved');
                    
                    setTimeout(() => {
                        $row.removeClass('table-success');
                        $btn.removeClass('btn-success').addClass('btn-primary');
                        $btn.find('.btn-text').text('Save');
                    }, 3000);
                },
                error: function(xhr) {
                    // Reset button state
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').text('Save');
                    $btn.find('.spinner-border').addClass('d-none');
                    
                    let errorMessage = `Failed to save bus trip for ${busNumber}.`;
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    } else if (xhr.status === 0) {
                        errorMessage = 'Network error. Please check your connection.';
                    }
                    
                    showAlert('danger', errorMessage);
                }
            });
        });

        // Save all trips for a tab
        $(document).on('click', '.save-all-trips', function() {
            const $btn = $(this);
            const tabPrefix = $btn.data('tab');
            const $tabPane = $(`#${tabPrefix}-bus`);
            
            // Prevent double submission
            if ($btn.prop('disabled')) {
                return;
            }
            
            // Validate trip date
            if (!$('#trip_date').val()) {
                showAlert('danger', 'Please select a trip date');
                return;
            }

            // Get all rows in this tab
            const $rows = $tabPane.find('tbody tr');
            if ($rows.length === 0) {
                showAlert('info', 'No buses to save in this tab.');
                return;
            }

            // Validate all trips
            let allErrors = [];
            const trips = [];
            const tripRows = [];
            
            $rows.each(function() {
                const $row = $(this);
                const $saveBtn = $row.find('.save-single-trip');
                const uniqueIndex = $saveBtn.data('unique-index');
                
                if (!uniqueIndex) return;
                
                const errors = validateTripData(uniqueIndex, $row, tabPrefix);
                
                if (errors.length > 0) {
                    allErrors = allErrors.concat(errors);
                } else {
                    trips.push(prepareTripData(uniqueIndex, $row));
                    tripRows.push($row);
                }
            });

            if (allErrors.length > 0) {
                showAlert('danger', allErrors.join('<br>'));
                return;
            }

            if (trips.length === 0) {
                showAlert('info', 'No valid trips to save.');
                return;
            }

            // Show spinner and update button text
            $btn.prop('disabled', true);
            const originalText = $btn.find('.btn-text').text();
            $btn.find('.btn-text').text(`Saving ${trips.length} trips...`);
            $btn.find('.spinner-border').removeClass('d-none');
            
            // Disable all individual save buttons in this tab
            $tabPane.find('.save-single-trip').prop('disabled', true);

            // Get the correct route based on tab
            const storeUrl = getStoreRoute(tabPrefix);

            // Submit all trips sequentially to avoid overwhelming the server
            let successCount = 0;
            let errorCount = 0;
            const totalTrips = trips.length;
            let completedCount = 0;

            // Process trips one by one
            function processNextTrip(index) {
                if (index >= totalTrips) {
                    // All trips processed
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').text(originalText);
                    $btn.find('.spinner-border').addClass('d-none');
                    $tabPane.find('.save-single-trip').prop('disabled', false);
                    
                    if (errorCount === 0) {
                        showAlert('success', `All ${successCount} trips saved successfully!`);
                        // Mark all rows as saved
                        tripRows.forEach(function($row) {
                            $row.addClass('table-success');
                            setTimeout(() => {
                                $row.removeClass('table-success');
                            }, 3000);
                        });
                    } else {
                        // Collect all error messages with details
                        let errorMessages = [];
                        tripRows.forEach(function($row, idx) {
                            if ($row.data('error-message')) {
                                const busNumber = $row.find('td:eq(1) strong').text();
                                const errorMsg = $row.data('error-message');
                                errorMessages.push(`Bus ${busNumber}: ${errorMsg}`);
                            }
                        });
                        
                        let message = `${successCount} trips saved successfully, ${errorCount} failed.`;
                        if (errorMessages.length > 0) {
                            message += '<br><br><strong>Errors:</strong><br>' + errorMessages.join('<br>');
                        }
                        showAlert('warning', message);
                    }
                    return;
                }

                const trip = trips[index];
                const $row = tripRows[index];
                
                // Update progress
                completedCount++;
                $btn.find('.btn-text').text(`Saving ${completedCount}/${totalTrips} trips...`);

                $.ajax({
                    url: storeUrl,
                    type: 'POST',
                    data: trip,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        successCount++;
                        $row.addClass('table-success');
                        processNextTrip(index + 1);
                    },
                    error: function(xhr) {
                        errorCount++;
                        $row.addClass('table-danger');
                        
                        // Collect error details for debugging
                        let errorMsg = 'Failed to save trip.';
                        let errorDetails = [];
                        
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                                errorDetails.push(errorMsg);
                            }
                            if (xhr.responseJSON.errors) {
                                Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                                    const fieldErrors = xhr.responseJSON.errors[key];
                                    if (Array.isArray(fieldErrors)) {
                                        fieldErrors.forEach(function(err) {
                                            errorDetails.push(`${key}: ${err}`);
                                        });
                                    } else {
                                        errorDetails.push(`${key}: ${fieldErrors}`);
                                    }
                                });
                                errorMsg = errorDetails.join('<br>');
                            }
                        } else if (xhr.status === 0) {
                            errorMsg = 'Network error. Please check your connection.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server error. Please try again.';
                        }
                        
                        // Store error message in row data for later display
                        $row.data('error-message', errorMsg);
                        
                        // Show error in console for debugging with full details
                        console.error('Trip save error:', {
                            trip: trip,
                            error: errorMsg,
                            status: xhr.status,
                            response: xhr.responseJSON,
                            busNumber: $row.find('td:eq(1) strong').text(),
                            fullResponse: xhr.responseJSON
                        });
                        
                        // Show alert with detailed error
                        if (errorDetails.length > 0) {
                            const busNumber = $row.find('td:eq(1) strong').text();
                            showAlert('danger', `Bus ${busNumber}: ${errorMsg}`);
                        }
                        
                        setTimeout(() => {
                            $row.removeClass('table-danger');
                        }, 3000);
                        processNextTrip(index + 1);
                    }
                });
            }

            // Start processing trips
            processNextTrip(0);
        });

        // Form submission
        $('#bulkTripForm').on('submit', function(e) {
            e.preventDefault();

            // Validate form
            if (!$('#trip_date').val()) {
                showAlert('danger', 'Please select a trip date');
                return false;
            }

            // Check if at least one trip is enabled
            if ($('.trip-checkbox:checked').length === 0) {
                showAlert('danger', 'Please select at least one bus trip to record');
                return false;
            }

            // Validate enabled trips
            let isValid = true;
            let errors = [];

            $('.trip-checkbox:checked').each(function() {
                const $row = $(this).closest('tr');
                const nameAttr = $(this).attr('name');
                const indexMatch = nameAttr.match(/\[([^\]]+)\]/);
                const uniqueIndex = indexMatch ? indexMatch[1] : '';
                const busNumber = $row.find('td:eq(2) strong').text();

                // Get trip type (per-row or default)
                const tripType = $row.find(`select[name="trips[${uniqueIndex}][trip_type]"]`).val() || $('#trip_type').val();
                if (!tripType) {
                    errors.push(`Bus ${busNumber}: Trip type is required`);
                    isValid = false;
                }

                // Get stoppages (per-row or default)
                const startStoppage = $row.find(`select[name="trips[${uniqueIndex}][start_stoppage_id]"]`).val() || $('#start_stoppage_id').val();
                if (!startStoppage) {
                    errors.push(`Bus ${busNumber}: Start stoppage is required`);
                    isValid = false;
                }

                const endStoppage = $row.find(`select[name="trips[${uniqueIndex}][end_stoppage_id]"]`).val() || $('#end_stoppage_id').val();
                if (!endStoppage) {
                    errors.push(`Bus ${busNumber}: End stoppage is required`);
                    isValid = false;
                }

                // Validate time - get hours, minutes, and am_pm
                const hours = $row.find(`select[name="trips[${uniqueIndex}][hours]"]`).val();
                const minutes = $row.find(`select[name="trips[${uniqueIndex}][minutes]"]`).val();
                const amPm = $row.find(`select[name="trips[${uniqueIndex}][am_pm]"]`).val();
                
                if (!hours || !minutes || !amPm) {
                    errors.push(`Bus ${busNumber}: Time is required`);
                    isValid = false;
                }

                // Validate distance for BRTC buses
                const subType = $row.data('sub-type');
                if (subType && subType.includes('BRTC')) {
                    const distance = $row.find(`input[name="trips[${uniqueIndex}][total_distance]"]`).val();
                    if (!distance || parseFloat(distance) <= 0) {
                        errors.push(`Bus ${busNumber}: Distance is required for BRTC buses`);
                        isValid = false;
                    }
                }
            });

            if (!isValid) {
                showAlert('danger', errors.join('<br>'));
                return false;
            }

            // Prepare form data
            const formData = new FormData(this);
            const trips = [];

            $('.trip-checkbox:checked').each(function() {
                const nameAttr = $(this).attr('name');
                const indexMatch = nameAttr.match(/\[([^\]]+)\]/);
                const uniqueIndex = indexMatch ? indexMatch[1] : '';
                const $row = $(this).closest('tr');

                // Get values (per-row or fallback to global defaults)
                const tripType = $row.find(`select[name="trips[${uniqueIndex}][trip_type]"]`).val() || $('#trip_type').val();
                const startStoppage = $row.find(`select[name="trips[${uniqueIndex}][start_stoppage_id]"]`).val() || $('#start_stoppage_id').val();
                const endStoppage = $row.find(`select[name="trips[${uniqueIndex}][end_stoppage_id]"]`).val() || $('#end_stoppage_id').val();

                // Get time components
                const hours = $row.find(`select[name="trips[${uniqueIndex}][hours]"]`).val();
                const minutes = $row.find(`select[name="trips[${uniqueIndex}][minutes]"]`).val();
                const amPm = $row.find(`select[name="trips[${uniqueIndex}][am_pm]"]`).val();
                
                // Convert to 24-hour format for time field
                let time24 = '';
                if (hours && minutes && amPm) {
                    let hour24 = parseInt(hours);
                    if (amPm === 'pm' && hour24 !== 12) {
                        hour24 += 12;
                    } else if (amPm === 'am' && hour24 === 12) {
                        hour24 = 0;
                    }
                    time24 = String(hour24).padStart(2, '0') + ':' + minutes + ':00';
                }

                const tripData = {
                    bus_id: $row.find(`input[name="trips[${uniqueIndex}][bus_id]"]`).val(),
                    bus_sub_type_id: $row.find(`input[name="trips[${uniqueIndex}][bus_sub_type_id]"]`).val(),
                    alternate_driver_id: $row.find(`select[name="trips[${uniqueIndex}][alternate_driver_id]"]`).val() || null,
                    alternate_bus_helper_id: $row.find(`select[name="trips[${uniqueIndex}][alternate_bus_helper_id]"]`).val() || null,
                    passengers: $row.find(`input[name="trips[${uniqueIndex}][passengers]"]`).val() || null,
                    trip_date: $('#trip_date').val(),
                    trip_type: tripType,
                    start_stoppage_id: startStoppage,
                    end_stoppage_id: endStoppage,
                    remarks: $('#remarks').val() || null
                };

                // Add time based on trip type
                if (tripType === 'in' && time24) {
                    tripData.in_time = time24;
                } else if (tripType === 'out' && time24) {
                    tripData.out_time = time24;
                }

                // Add distance for BRTC buses
                const distance = $row.find(`input[name="trips[${uniqueIndex}][total_distance]"]`).val();
                if (distance) {
                    tripData.total_distance = distance;
                }

                trips.push(tripData);
            });

            // Submit each trip individually
            setLoading(true);
            let successCount = 0;
            let errorCount = 0;
            const totalTrips = trips.length;

            trips.forEach((trip, index) => {
                $.ajax({
                    url: '{{ route("bus-trips.store") }}',
                    type: 'POST',
                    data: trip,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        successCount++;
                        if (successCount + errorCount === totalTrips) {
                            setLoading(false);
                            if (errorCount === 0) {
                                toastr.success(`All ${successCount} trips recorded successfully!`);
                                window.location.href = "{{ route('bus-trips.index') }}";
                            } else {
                                toastr.warning(`${successCount} trips recorded, ${errorCount} failed.`);
                            }
                        }
                    },
                    error: function(xhr) {
                        errorCount++;
                        if (successCount + errorCount === totalTrips) {
                            setLoading(false);
                            toastr.error(`${errorCount} trip(s) failed to record.`);
                        }
                    }
                });
            });
        });

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

        function setLoading(loading) {
            $('#submitBtn').prop('disabled', loading);
            if (loading) {
                $('#loadingSpinner').removeClass('d-none');
                $('#submitBtn').html('<i class="ti ti-check me-1"></i>Recording... <span class="spinner-border spinner-border-sm ms-2"></span>');
            } else {
                $('#loadingSpinner').addClass('d-none');
                $('#submitBtn').html('<i class="ti ti-check me-1"></i>Record Trips');
            }
        }

        function resetForm() {
            if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
                $('#bulkTripForm')[0].reset();
                $('.trip-checkbox').prop('checked', true);
                $('.selectAll-own, .selectAll-brtc, .selectAll-hired').prop('checked', true);
                $('.distance-input').val('');

                // Reset Select2 dropdowns
                if ($.fn.select2) {
                    $('.select2').val(null).trigger('change');
                }

                // Reapply defaults
                applyDefaults();
            }
        }
    });
</script>
@endsection