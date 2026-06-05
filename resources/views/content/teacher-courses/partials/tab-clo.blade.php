<div class="row g-3">
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('CLO-wise Performance') }}</h6>
                <p class="text-muted small">{{ __('Review student achievement against course learning outcomes.') }}</p>
                <a href="{{ route('my-courses.grade-sheet', $assignment) }}" class="btn btn-sm btn-outline-primary">{{ __('View Marks Data') }}</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('CLO Attainment') }}</h6>
                <p class="text-muted small">{{ __('CLO attainment reports are available in the course file and reports section.') }}</p>
                <a href="{{ route('my-courses.course-file', $assignment) }}" class="btn btn-sm btn-outline-warning">{{ __('Course File CLO Section') }}</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('Student Achievement') }}</h6>
                <a href="{{ route('teacher-courses.students', $assignment) }}" class="btn btn-sm btn-outline-secondary">{{ __('Student List') }}</a>
            </div>
        </div>
    </div>
</div>
