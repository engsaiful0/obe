@php
    $title = $title ?? __('Courses');
    $tablePartial = $tablePartial ?? 'content.teacher-courses.partials.assigned-table';
    $exportRoute = $exportRoute ?? null;
    $scope = $scope ?? 'assigned';
@endphp
@include('content.teacher-courses.partials.breadcrumb', ['items' => $breadcrumbItems ?? []])

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">{{ $title }}</h5>
        <div class="d-flex flex-wrap gap-2 no-print">
            @if ($exportRoute)
                <a href="{{ $exportRoute }}" class="btn btn-sm btn-success">
                    <i class="ti ti-file-spreadsheet"></i> {{ __('Export Excel') }}
                </a>
            @endif
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="ti ti-printer"></i> {{ __('Print') }}
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3 no-print">
            <div class="col-md-4">
                <input type="text" id="teacher-course-search" class="form-control form-control-sm"
                    placeholder="{{ __('Search courses...') }}" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select id="teacher-course-sort" class="form-select form-select-sm select2">
                    <option value="">{{ __('Sort by (default)') }}</option>
                    <option value="course_code" @selected(request('sort') === 'course_code')>{{ __('Course Code') }}</option>
                    <option value="course_title" @selected(request('sort') === 'course_title')>{{ __('Course Title') }}</option>
                    <option value="program" @selected(request('sort') === 'program')>{{ __('Program') }}</option>
                    <option value="session" @selected(request('sort') === 'session')>{{ __('Academic Session') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="teacher-course-direction" class="form-select form-select-sm">
                    <option value="desc" @selected(request('direction', 'desc') === 'desc')>{{ __('Descending') }}</option>
                    <option value="asc" @selected(request('direction') === 'asc')>{{ __('Ascending') }}</option>
                </select>
            </div>
        </div>

        <div id="teacher-course-table-wrapper" class="position-relative border rounded">
            <div id="teacher-course-loading"
                class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center"
                style="z-index: 5;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">{{ __('Loading') }}</span>
                </div>
            </div>
            <div id="teacher-course-table-container">
                @include($tablePartial, ['courses' => $courses])
            </div>
        </div>
    </div>
</div>
