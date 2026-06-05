<div class="row g-3">
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('PLO Mapping') }}</h6>
                <p class="text-muted small">{{ __('Program learning outcomes mapped through CLO-PO mappings.') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('PLO Achievement') }}</h6>
                <a href="{{ route('teacher-courses.reports', $assignment) }}" class="btn btn-sm btn-outline-primary">{{ __('Attainment Report') }}</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100">
            <div class="card-body">
                <h6>{{ __('PLO Attainment') }}</h6>
                <a href="{{ route('my-courses.course-file', $assignment) }}" class="btn btn-sm btn-outline-warning">{{ __('Course File PLO Section') }}</a>
            </div>
        </div>
    </div>
</div>
