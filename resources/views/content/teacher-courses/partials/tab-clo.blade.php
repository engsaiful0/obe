<div class="row g-3">
    <div class="col-12">
        @include('content.teacher-courses.partials.clo-attainment-live', [
            'cloAttainment' => $dashboard['clo_attainment'] ?? ['rows' => [], 'chart' => []],
            'closAchieved' => $dashboard['clos_achieved'] ?? 0,
            'closTotal' => $dashboard['clos_total'] ?? 0,
        ])
    </div>
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('Student Achievement') }}</h6>
                <a href="{{ route('teacher-courses.students', $assignment) }}" class="btn btn-sm btn-outline-secondary">{{ __('Student List') }}</a>
                <a href="{{ route('my-courses.grade-sheet', $assignment) }}" class="btn btn-sm btn-outline-primary mt-2">{{ __('Grade Sheet') }}</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('Course File CLO Section') }}</h6>
                <a href="{{ route('my-courses.course-file', $assignment) }}" class="btn btn-sm btn-outline-warning">{{ __('Open Course File') }}</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('CLO-wise Performance') }}</h6>
                <p class="text-muted small mb-0">{{ __('Accurate CLO scores require question-level marks via the Student Marks module when question-CLO mappings exist.') }}</p>
            </div>
        </div>
    </div>
</div>
