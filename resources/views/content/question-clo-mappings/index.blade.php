@extends('layouts/layoutMaster')

@section('title', __('Question–CLO mappings'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('Question–CLO mapping') }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('question-clo-mappings.matrix') }}" class="btn btn-outline-primary btn-sm">{{ __('Question matrix') }}</a>
            <a href="{{ route('question-clo-mappings.create') }}" class="btn btn-primary btn-sm">{{ __('Add mapping') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('question-clo-mappings.index') }}" id="question-clo-filter-form" autocomplete="off">
            <div class="row g-2 mb-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-0">{{ __('Academic session') }}</label>
                    <select name="academic_session_id" class="form-select form-select-sm question-clo-filter">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($academicSessions as $sess)
                            <option value="{{ $sess->id }}" @selected(request('academic_session_id') == $sess->id)>{{ $sess->session_name }} · {{ $sess->academic_year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">{{ __('Program') }}</label>
                    <select name="program_id" class="form-select form-select-sm question-clo-filter">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($programs as $p)
                            <option value="{{ $p->id }}" @selected(request('program_id') == $p->id)>{{ $p->program_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">{{ __('Course') }}</label>
                    <select name="course_id" class="form-select form-select-sm question-clo-filter">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($courses as $cr)
                            <option value="{{ $cr->id }}" data-program-id="{{ $cr->program_id }}" @selected(request('course_id') == $cr->id)>{{ $cr->course_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">{{ __('Component') }}</label>
                    <select name="assessment_component_id" class="form-select form-select-sm question-clo-filter">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($assessmentComponents as $ac)
                            <option value="{{ $ac->id }}" data-course-id="{{ $ac->course_id }}" @selected(request('assessment_component_id') == $ac->id)>
                                {{ $ac->component_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">{{ __('CLO') }}</label>
                    <select name="clo_id" class="form-select form-select-sm question-clo-filter">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($clos as $clo)
                            <option value="{{ $clo->id }}" data-course-id="{{ $clo->course_id }}" @selected(request('clo_id') == $clo->id)>{{ $clo->clo_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">{{ __("Bloom") }}</label>
                    <select name="bloom_id" class="form-select form-select-sm question-clo-filter">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($blooms as $bl)
                            <option value="{{ $bl->id }}" @selected(request('bloom_id') == $bl->id)>{{ $bl->level_order }}. {{ $bl->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">{{ __('Status') }}</label>
                    <select name="status_id" class="form-select form-select-sm question-clo-filter">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->status_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">{{ __('Search') }}</label>
                    <input type="search" name="q" id="q" class="form-control form-control-sm" value="{{ request('q') }}"
                        placeholder="{{ __('Label, part, CLO, course…') }}" autocomplete="off">
                </div>
            </div>
        </form>

        <div id="question-clo-mapping-table-wrapper" class="position-relative rounded border">
            <div id="qcm-loading-overlay" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center rounded" style="z-index: 5; min-height: 140px;">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('Loading') }}…</span></div>
            </div>
            <div id="question-clo-mapping-table-inner">
                @include('content.question-clo-mappings._table')
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
<script>
    window.__qcmCascadeUrls = @json($cascadeUrls);
</script>
<script src="{{ asset('assets/js/question-clo-mapping-filter.js') }}"></script>
@endsection
