@extends('layouts/layoutMaster')

@section('title', __('Student marks'))

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">{{ __('Student marks entry') }}</h5>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('student-marks.bulk') }}" class="btn btn-primary btn-sm">{{ __('Bulk entry') }}</a>
                <a href="{{ route('student-marks.create') }}" class="btn btn-outline-primary btn-sm">{{ __('Single student') }}</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('student-marks.index') }}" id="sm-index-filter-form" autocomplete="off">
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small mb-0">{{ __('Academic session') }}</label>
                        <select name="academic_session_id" class="form-select form-select-sm sm-index-filter">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($academicSessions as $sess)
                                <option value="{{ $sess->id }}" @selected(request('academic_session_id') == $sess->id)>
                                    {{ $sess->session_name }} · {{ $sess->academic_year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">{{ __('Program') }}</label>
                        <select name="program_id" class="form-select form-select-sm sm-index-filter">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($programs as $p)
                                <option value="{{ $p->id }}" @selected(request('program_id') == $p->id)>{{ $p->program_code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">{{ __('Course') }}</label>
                        <select name="course_id" class="form-select form-select-sm sm-index-filter">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($courses as $cr)
                                <option value="{{ $cr->id }}" data-program-id="{{ $cr->program_id }}"
                                    @selected(request('course_id') == $cr->id)>{{ $cr->course_code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">{{ __('Batch') }}</label>
                        <select name="batch_id" class="form-select form-select-sm sm-index-filter">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($batches as $b)
                                <option value="{{ $b->id }}" data-program-id="{{ $b->program_id }}"
                                    @selected(request('batch_id') == $b->id)>{{ $b->batch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">{{ __('Section') }}</label>
                        <select name="section_id" class="form-select form-select-sm sm-index-filter">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}" data-program-id="{{ $sec->program_id }}"
                                    @selected(request('section_id') == $sec->id)>
                                    {{ $sec->section_code ?? $sec->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">{{ __('Component') }}</label>
                        <select name="assessment_component_id" class="form-select form-select-sm sm-index-filter">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($assessmentComponents as $ac)
                                <option value="{{ $ac->id }}" data-course-id="{{ $ac->course_id }}"
                                    @selected(request('assessment_component_id') == $ac->id)>{{ $ac->component_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">{{ __('OBE status') }}</label>
                        <select name="status_id" class="form-select form-select-sm sm-index-filter">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($statuses as $st)
                                <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->status_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-0">{{ __('Search student') }}</label>
                        <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                            placeholder="{{ __('Code or name') }}" autocomplete="off">
                    </div>
                </div>
            </form>

            <div id="student-marks-table-wrapper" class="position-relative rounded border">
                <div id="sm-loading-overlay"
                    class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center rounded"
                    style="z-index: 5; min-height: 140px;">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('Loading') }}…</span>
                    </div>
                </div>
                <div id="student-marks-table-inner">
                    @include('content.student-marks._table')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
    <script>
        window.__studentMarksCascade = @json($cascadeUrls);
        window.__studentMarksRoutes = @json($routes ?? []);
        window.__studentMarksPage = 'index';
    </script>
    <script src="{{ asset('assets/js/student-marks.js') }}"></script>
@endsection
