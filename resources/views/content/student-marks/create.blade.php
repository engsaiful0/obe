@extends('layouts/layoutMaster')

@section('title', __('Enter marks'))

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">{{ __('Single student marks') }}</h5>
            <a href="{{ route('student-marks.bulk') }}" class="btn btn-sm btn-outline-primary">{{ __('Bulk entry') }}</a>
        </div>
        <div class="card-body">
            <form action="{{ route('student-marks.store') }}" method="POST" id="sm-single-setup" autocomplete="off" data-async-no-submit>
                @csrf
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-3">
                        <label class="form-label small mb-0">{{ __('Academic session') }} <span class="text-danger">*</span></label>
                        <select name="academic_session_id" id="sm_sess" class="form-select form-select-sm" required>
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($academicSessions as $sess)
                                <option value="{{ $sess->id }}" @selected(old('academic_session_id') == $sess->id)>{{ $sess->session_name }} ·
                                    {{ $sess->academic_year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">{{ __('Program') }}</label>
                        <select name="program_id" id="sm_prog" class="form-select form-select-sm" required>
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->program_code.' - '.$p->program_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">{{ __('Course') }}</label>
                        <select name="course_id" id="sm_course" class="form-select form-select-sm" required disabled></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">{{ __('Batch') }}</label>
                        <select name="batch_id" id="sm_batch" class="form-select form-select-sm" required disabled></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">{{ __('Section') }}</label>
                        <select name="section_id" id="sm_section" class="form-select form-select-sm" disabled>
                            <option value="">{{ __('Optional') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">{{ __('Component') }}</label>
                        <select name="assessment_component_id" id="sm_comp" class="form-select form-select-sm" required disabled></select>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary obe-async-load-single" disabled id="sm-load-context">
                    {{ __('Load student list & parts') }}</button>
            </form>

            <form action="{{ route('student-marks.store') }}" method="POST" id="sm-single-save" data-ajax-submit class="mt-4 d-none">
                @csrf
                <div class="alert alert-secondary small">
                    {{ __('Fields below are populated after choosing context and clicking load.') }}
                </div>
                <div data-ajax-errors class="alert alert-danger d-none"></div>
                <input type="hidden" name="academic_session_id" id="hf_sess">
                <input type="hidden" name="program_id" id="hf_prog">
                <input type="hidden" name="course_id" id="hf_course">
                <input type="hidden" name="batch_id" id="hf_batch">
                <input type="hidden" name="section_id" id="hf_section">
                <input type="hidden" name="assessment_component_id" id="hf_comp">
                <div class="mb-3">
                    <label class="form-label">{{ __('Student') }}</label>
                    <select name="student_id" id="sm_student" class="form-select form-select-sm" required></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('OBE sheet status') }}</label>
                    <select name="status_id" id="sm_status_single" class="form-select form-select-sm" required>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->id }}">{{ $st->status_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="sm-single-questions" class="row g-2 mb-3"></div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Total marks') }}</label>
                        <input type="text" inputmode="decimal" name="total_marks" id="sm_single_total"
                            class="form-control form-control-sm" readonly>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary obe-ajax-primary d-inline-flex align-items-center gap-2">
                    <span class="obe-btn-label">{{ __('Save marks') }}</span>
                    <span class="spinner-border spinner-border-sm d-none obe-btn-spinner"></span>
                </button>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
    <script>
        window.__studentMarksCascade = @json($cascadeUrls);
        window.__studentMarksRoutes = @json($routes ?? []);
        window.__studentMarksPage = 'create';
    </script>
    <script src="{{ asset('assets/js/student-marks.js') }}"></script>
@endsection
