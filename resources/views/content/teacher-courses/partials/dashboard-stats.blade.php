@php
    $stats = $dashboard['quick_stats'] ?? [];
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small">{{ __('Total Students') }}</div>
                <div class="fs-4 fw-semibold text-primary">{{ (int) ($stats['total_students'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small">{{ __('Total Classes') }}</div>
                <div class="fs-4 fw-semibold">{{ (int) ($stats['total_classes'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small">{{ __('Classes Taken') }}</div>
                <div class="fs-4 fw-semibold">{{ (int) ($stats['classes_taken'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small">{{ __('Attendance %') }}</div>
                <div class="fs-4 fw-semibold text-info">{{ number_format((float) ($stats['attendance_percentage'] ?? 0), 1) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small">{{ __('Marks Entry') }}</div>
                <div class="fs-5 fw-semibold">{{ number_format((float) ($stats['marks_entry_progress'] ?? 0), 1) }}%</div>
                <div class="progress mt-1" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: {{ min(100, (float) ($stats['marks_entry_progress'] ?? 0)) }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small">{{ __('CLO Progress') }}</div>
                <div class="fs-5 fw-semibold">{{ number_format((float) ($stats['clo_assessment_progress'] ?? 0), 1) }}%</div>
                <div class="progress mt-1" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: {{ min(100, (float) ($stats['clo_assessment_progress'] ?? 0)) }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
