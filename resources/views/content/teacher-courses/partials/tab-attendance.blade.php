<div class="row g-3">
    <div class="col-md-6">
        <div class="card border">
            <div class="card-body">
                <h6>{{ __('Take Attendance') }}</h6>
                @if ($readonly)
                    <p class="text-muted mb-0">{{ __('Attendance entry is disabled for previous semester courses.') }}</p>
                @else
                    <p class="text-muted">{{ __('Record daily attendance through the course file attendance sheets or marks entry attendance columns.') }}</p>
                    <a href="{{ route('my-courses.course-file', $assignment) }}#attendance" class="btn btn-sm btn-primary">{{ __('Upload Attendance Sheet') }}</a>
                    <a href="{{ route('my-courses.marks-entry', $assignment) }}" class="btn btn-sm btn-outline-primary">{{ __('Attendance Marks Entry') }}</a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border">
            <div class="card-body">
                <h6>{{ __('Attendance Summary') }}</h6>
                <p>{{ __('Classes Taken') }}: <strong>{{ (int) ($dashboard['quick_stats']['classes_taken'] ?? 0) }}</strong></p>
                <p>{{ __('Average Attendance') }}: <strong>{{ number_format((float) ($dashboard['quick_stats']['attendance_percentage'] ?? 0), 1) }}%</strong></p>
                <a href="{{ route('my-courses.course-file', $assignment) }}" class="btn btn-sm btn-outline-info">{{ __('View Attendance Records') }}</a>
            </div>
        </div>
    </div>
</div>
