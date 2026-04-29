@extends('layouts/layoutMaster')

@section('title', 'Edit Bus Schedule')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/css/bus-schedule-form.css') }}">
@endsection

@section('page-script')
<script>
    // Configure toastr before any scripts run
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }
    
    window.busScheduleUrls = {
        update: '{{ route('bus-schedules.schedule-update', $busSchedule->id) }}',
        index: '{{ route('bus-schedules.schedule-index') }}'
    };
    window.busScheduleId = {{ $busSchedule->id }};
    window.initialRowCount = {{ $busSchedule->entries->count() }};
</script>
<script src="{{ asset('assets/js/bus-schedule.js') }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Bus Schedule</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('bus-schedules.schedule-update', $busSchedule->id) }}" method="POST" id="scheduleForm" data-ajax="true">
                    @csrf
                    @method('PUT')

                    <!-- Common Fields -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Schedule Category <span class="text-danger">*</span></label>
                            <select class="select2 form-select @error('keyword_id') is-invalid @enderror" name="keyword_id" id="keyword_id" required>
                                <option value="">Select Category</option>
                                @foreach($keywords as $keyword)
                                <option value="{{ $keyword->id }}" {{ old('keyword_id', $busSchedule->bus_schedule_keyword_id) == $keyword->id ? 'selected' : '' }}>
                                    {{ $keyword->keyword_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('keyword_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('effective_from') is-invalid @enderror"
                                name="effective_from" id="effective_from"
                                value="{{ old('effective_from', $busSchedule->effective_from?->format('Y-m-d')) }}" required>
                            @error('effective_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="select2 form-select @error('status') is-invalid @enderror" name="status" id="status" required>
                                <option value="">Select Status</option>
                                @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ old('status', $busSchedule->status_id) == $status->id ? 'selected' : '' }}>
                                    {{ $status->status_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bus User <span class="text-danger">*</span></label>
                            <select class="select2 form-select @error('bus_user_id') is-invalid @enderror" name="bus_user_id" id="bus_user_id" required>
                                <option value="">Select Bus User</option>
                                @foreach($busUsers as $user)
                                <option value="{{ $user->id }}" {{ old('bus_user_id', $busSchedule->bus_user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->bus_user_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('bus_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description Field -->
                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                name="remarks" id="remarks" 
                                rows="3" 
                                placeholder="Enter schedule remarks (optional)">{{ old('remarks', $busSchedule->remarks) }}</textarea>
                            @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Dynamic Schedule Rows -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="mb-3">Schedule Details</h5>
                        </div>
                    </div>

                    <div id="scheduleRows">
                        <table id="scheduleTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Start Time</th>
                                    <th>Starting Point</th>
                                    <th>Bus Route</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($busSchedule->entries as $index => $entry)
                                @php
                                    $startTime = $entry->start_time ? \Carbon\Carbon::parse($entry->start_time) : null;
                                    $hour24 = $startTime ? $startTime->format('G') : null;
                                    $minute = $startTime ? $startTime->format('i') : null;
                                    
                                    // Convert 24-hour to 12-hour format
                                    if ($hour24 !== null) {
                                        if ($hour24 == 0) {
                                            $hour12 = 12;
                                            $amPm = 'am';
                                        } elseif ($hour24 < 12) {
                                            $hour12 = $hour24;
                                            $amPm = 'am';
                                        } elseif ($hour24 == 12) {
                                            $hour12 = 12;
                                            $amPm = 'pm';
                                        } else {
                                            $hour12 = $hour24 - 12;
                                            $amPm = 'pm';
                                        }
                                    } else {
                                        $hour12 = null;
                                        $amPm = null;
                                    }
                                    
                                    $selectedHour = old('schedules.'.$index.'.hours', $hour12);
                                    $selectedMinute = old('schedules.'.$index.'.minutes', $minute);
                                    $selectedAmPm = old('schedules.'.$index.'.am_pm', $amPm);
                                @endphp
                                <tr class="data-row" data-row-index="{{ $index }}">
                                    <td>
                                        <div class="d-flex gap-1 align-items-center time-select-group" data-index="{{ $index }}">
                                            <select name="schedules[{{ $index }}][hours]" class="form-select form-select-sm" style="width: auto;" required>
                                                <option value="">Hour</option>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ $selectedHour == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                            <span>:</span>
                                            <select name="schedules[{{ $index }}][minutes]" class="form-select form-select-sm" style="width: auto;" required>
                                                <option value="">Min</option>
                                                @for($i = 0; $i <= 59; $i++)
                                                    @php $min=str_pad($i, 2, '0' , STR_PAD_LEFT); @endphp
                                                    <option value="{{ $min }}" {{ $selectedMinute === (string)$min ? 'selected' : '' }}>{{ $min }}</option>
                                                @endfor
                                            </select>
                                            <select name="schedules[{{ $index }}][am_pm]" class="form-select form-select-sm" style="width: auto;" required>
                                                <option value="">AM/PM</option>
                                                <option value="am" {{ $selectedAmPm === 'am' ? 'selected' : '' }}>AM</option>
                                                <option value="pm" {{ $selectedAmPm === 'pm' ? 'selected' : '' }}>PM</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="select2 form-select starting-point-select"
                                            name="schedules[{{ $index }}][starting_point_id]" required>
                                            <option value="">Select Starting Point</option>
                                            @foreach($stoppages as $stoppage)
                                            <option value="{{ $stoppage->id }}" {{ old('schedules.'.$index.'.starting_point_id', $entry->starting_point_id) == $stoppage->id ? 'selected' : '' }}>
                                                {{ $stoppage->stoppage_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="select2 form-select bus-route-select"
                                            name="schedules[{{ $index }}][bus_route_id]" required>
                                            <option value="">Select Bus Route</option>
                                            @foreach($busRoutes as $route)
                                            <option value="{{ $route->id }}" {{ old('schedules.'.$index.'.bus_route_id', $entry->bus_route_id) == $route->id ? 'selected' : '' }}>
                                                {{ $route->route_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" value="{{ old('schedules.'.$index.'.description', $entry->description) }}" class="form-control" name="schedules[{{ $index }}][description]" placeholder="Description">
                                    </td>
                                    <td class="action-btn">
                                        @if($index === 0)
                                            <button type="button" class="btn btn-primary btn-sm add-row">
                                                <i class="ti ti-plus"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Submit Button -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner" role="status" aria-hidden="true"></span>
                                <span id="submitText">Update Schedule</span>
                            </button>
                            <a href="{{ route('bus-schedules.schedule-index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

