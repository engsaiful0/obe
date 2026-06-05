@php
    $readonly = (bool) ($readonly ?? false);
    $summary = $dashboard['quick_stats'] ?? [];
@endphp
<ul class="nav nav-pills mb-3" id="attendance-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#att-take" type="button">{{ __('Take Attendance') }}</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#att-history" type="button">{{ __('Attendance History') }}</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#att-summary" type="button">{{ __('Attendance Summary') }}</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="att-take">
        @if ($readonly)
            <div class="alert alert-warning">{{ __('Attendance entry is disabled for previous semester courses.') }}</div>
        @endif
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Attendance Date') }}</label>
                <input type="date" id="attendance-date" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-9 text-md-end">
                @if (!$readonly)
                    <button type="button" id="attendance-mark-all-present" class="btn btn-sm btn-outline-success">{{ __('Mark All Present') }}</button>
                    <button type="button" id="attendance-save-btn" class="btn btn-sm btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" id="attendance-save-spinner"></span>
                        {{ __('Save Attendance') }}
                    </button>
                @endif
            </div>
        </div>
        <div id="attendance-feedback" class="alert d-none"></div>
        <div class="table-responsive border rounded">
            <table class="table table-sm table-striped mb-0" id="attendance-roster-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Student Code') }}</th>
                        <th>{{ __('Student Name') }}</th>
                        <th>{{ __('Batch') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Remarks') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="att-history">
        <div class="table-responsive">
            <table class="table table-sm table-striped" id="attendance-history-table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th class="text-center">{{ __('Total') }}</th>
                        <th class="text-center">{{ __('Present') }}</th>
                        <th class="text-center">{{ __('Absent') }}</th>
                        <th class="text-end">{{ __('Percentage') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="att-summary">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card border"><div class="card-body text-center py-3">
                    <div class="text-muted small">{{ __('Classes Taken') }}</div>
                    <div class="fs-4 fw-semibold">{{ (int) ($summary['classes_taken'] ?? 0) }}</div>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border"><div class="card-body text-center py-3">
                    <div class="text-muted small">{{ __('Avg. Attendance') }}</div>
                    <div class="fs-4 fw-semibold text-info">{{ number_format((float) ($summary['attendance_percentage'] ?? 0), 1) }}%</div>
                </div></div>
            </div>
            <div class="col-md-6">
                <p class="text-muted small mb-2">{{ __('You can also upload attendance sheets in the course file or enter attendance marks.') }}</p>
                <a href="{{ route('my-courses.course-file', $assignment) }}#attendance" class="btn btn-sm btn-outline-warning">{{ __('Course File Attendance') }}</a>
                @if (!$readonly)
                    <a href="{{ route('my-courses.marks-entry', $assignment) }}" class="btn btn-sm btn-outline-primary">{{ __('Attendance Marks Entry') }}</a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    window.__studentAttendanceConfig = {
        rosterRoute: @json(route('teacher-courses.attendance.roster', $assignment)),
        saveRoute: @json(route('teacher-courses.attendance.store', $assignment)),
        historyRoute: @json(route('teacher-courses.attendance.history', $assignment)),
        readonly: @json($readonly),
        csrf: @json(csrf_token())
    };
</script>
