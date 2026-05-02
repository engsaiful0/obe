@extends('layouts/layoutMaster')

@section('title', __('Bulk marks entry'))

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">{{ __('Student marks entry — bulk') }}</h5>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('student-marks.index') }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('Marks list') }}
                </a>

                <a href="{{ route('student-marks.create') }}" class="btn btn-sm btn-outline-primary">
                    {{ __('Single student') }}
                </a>
            </div>
        </div>

        <div class="card-body">
            <form id="sm-bulk-filters">
                @csrf

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-lg-4 col-md-4">
                        <label class="form-label small mb-0">
                            {{ __('Academic session') }} <span class="text-danger">*</span>
                        </label>

                        <select name="academic_session_id" id="sm_sess" class="form-select form-select-sm" required>
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($academicSessions as $sess)
                                <option value="{{ $sess->id }}">
                                    {{ $sess->session_name }} · {{ $sess->academic_year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-4">
                        <label class="form-label small mb-0">
                            {{ __('Program') }} <span class="text-danger">*</span>
                        </label>

                        <select name="program_id" id="sm_prog" class="form-select form-select-sm" required>
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($programs as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->program_code }} — {{ \Illuminate\Support\Str::limit($p->program_name, 36) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-4">
                        <label class="form-label small mb-0">
                            {{ __('Course') }} <span class="text-danger">*</span>
                        </label>

                        <select name="course_id" id="sm_course" class="form-select form-select-sm" required disabled>
                            <option value="">{{ __('Select') }}</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <p class="small text-muted mb-0">
                            {{ __('After you choose academic session, program, and course, you can download the Excel template. All assessment components appear in one sheet. Per-question caps are validated on save/import.') }}
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button"
                            class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2"
                            id="sm-load-grid">
                        <span class="obe-btn-label">{{ __('Load students & questions') }}</span>
                        <span class="spinner-border spinner-border-sm d-none obe-btn-spinner"
                              role="status"
                              aria-hidden="true"></span>
                    </button>

                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            id="sm-download-template">
                        {{ __('Download Excel template') }}
                    </button>

                    <button type="button"
                            class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1"
                            id="sm-reset">
                        <span class="obe-btn-label">{{ __('Reset marks for filters') }}</span>
                        <span class="spinner-border spinner-border-sm d-none obe-btn-spinner"
                              role="status"
                              aria-hidden="true"></span>
                    </button>
                </div>
            </form>

            <div id="sm-bulk-feedback" class="alert alert-danger d-none" role="alert"></div>

            <form id="sm-import-form" enctype="multipart/form-data" class="border rounded p-3 mb-3">
                @csrf

                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small">{{ __('Excel / CSV marks file') }}</label>

                        <input type="file"
                               name="file"
                               id="sm-import-file"
                               class="form-control form-control-sm"
                               accept=".xlsx,.xls,.csv"
                               required>
                    </div>

                    <div class="col-md-8">
                        <button type="button"
                                class="btn btn-success btn-sm d-inline-flex align-items-center gap-2"
                                id="sm-import-submit">
                            <span class="obe-btn-label">{{ __('Import marks') }}</span>
                            <span class="spinner-border spinner-border-sm d-none obe-btn-spinner"
                                  role="status"></span>
                        </button>

                        <small class="text-muted d-block mt-2">
                            {{ __('Use the downloaded template. Row errors are listed if validation fails.') }}
                        </small>
                    </div>
                </div>
            </form>

            <div id="sm-import-errors" class="alert alert-warning d-none"></div>

            <div class="table-responsive border rounded mb-3 d-none" id="sm-matrix-wrap">
                <table class="table table-sm align-middle mb-0" id="sm-matrix-table">
                    <thead class="table-light"></thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="d-flex gap-2">
                <button type="button"
                        class="btn btn-primary d-inline-flex align-items-center gap-2"
                        id="sm-bulk-save"
                        disabled>
                    <span class="obe-btn-label">{{ __('Save all rows') }}</span>
                    <span class="spinner-border spinner-border-sm d-none obe-btn-spinner"
                          role="status"></span>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>

    <script>
        window.__studentMarksCascade = @json($cascadeUrls);
        window.__studentMarksRoutes = @json($routes ?? []);
        window.__studentMarksPage = 'bulk';
    </script>

    <script src="{{ asset('assets/js/student-marks.js') }}"></script>
@endsection