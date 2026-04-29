@extends('layouts/layoutMaster')

@section('title', 'Bus Schedule')

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
</script>
<script src="{{ asset('assets/js/bus-schedule.js') }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Bus Schedule</h4>
                <a href="{{ route('bus-schedules.schedule-index') }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-list me-1"></i> View All Schedules
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('bus-schedules.store-schedule') }}" method="POST" id="scheduleForm" data-ajax="true">
                    @csrf

                    <!-- Common Fields -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Schedule Category <span class="text-danger">*</span></label>
                            <select class="select2 form-select @error('keyword_id') is-invalid @enderror" name="keyword_id" id="keyword_id" required>
                                <option value="">Select Category</option>
                                @foreach($keywords as $keyword)
                                <option value="{{ $keyword->id }}" {{ old('keyword_id') == $keyword->id ? 'selected' : '' }}>
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
                                value="{{ old('effective_from', date('Y-m-d')) }}" required>
                            @error('effective_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="select2 form-select @error('status') is-invalid @enderror" name="status" id="status" required>
                                <option value="">Select Status</option>
                                @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->status_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bus User <span class="text-danger">*</span></label>
                            <select class="select2 form-select @error('bus_user_id') is-invalid @enderror" name="bus_user_id" id="bus_user_id" required>
                                <option value="">Select Bus User</option>
                                @foreach($busUsers as $user)
                                <option value="{{ $user->id }}" {{ old('bus_user_id') == $user->id ? 'selected' : '' }}>
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
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                name="remarks" id="remarks" 
                                rows="3" 
                                placeholder="Enter schedule remarks (optional)">{{ old('remarks') }}</textarea>
                            @error('description')
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
                                <!-- First Data Row -->
                                <tr class="data-row" data-row-index="0">
                                    <td>
                                        <div class="d-flex gap-1 align-items-center time-select-group" data-index="0">
                                            <select name="schedules[0][hours]" class="form-select form-select-sm" style="width: auto;" required>
                                                <option value="">Hour</option>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                            <span>:</span>
                                            <select name="schedules[0][minutes]" class="form-select form-select-sm" style="width: auto;" required>
                                                <option value="">Min</option>
                                                @for($i = 0; $i <= 59; $i++)
                                                    @php $min=str_pad($i, 2, '0' , STR_PAD_LEFT); @endphp
                                                    <option value="{{ $min }}">{{ $min }}</option>
                                                @endfor
                                            </select>
                                            <select name="schedules[0][am_pm]" class="form-select form-select-sm" style="width: auto;" required>
                                                <option value="">AM/PM</option>
                                                <option value="am">AM</option>
                                                <option value="pm">PM</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="select2 form-select starting-point-select"
                                            name="schedules[0][starting_point_id]" required>
                                            <option value="">Select Starting Point</option>
                                            @foreach($stoppages as $stoppage)
                                            <option value="{{ $stoppage->id }}">
                                                {{ $stoppage->stoppage_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="select2 form-select bus-route-select"
                                            name="schedules[0][bus_route_id]" required>
                                            <option value="">Select Bus Route</option>
                                            @foreach($busRoutes as $route)
                                            <option value="{{ $route->id }}">
                                                {{ $route->route_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="schedules[0][description]" placeholder="Description">
                                    </td>


                                    <td class="action-btn">
                                        <button type="button" class="btn btn-primary btn-sm add-row">
                                            <i class="ti ti-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>



                    <!-- Submit Button -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner" role="status" aria-hidden="true"></span>
                                <span id="submitText">Create Schedule</span>
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