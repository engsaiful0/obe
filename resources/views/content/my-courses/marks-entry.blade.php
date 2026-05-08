@extends('layouts/layoutMaster')

@section('title', __('Marks Entry'))

@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('Marks Entry') }}</h5>
                <small class="text-muted">
                    {{ $courseAssignment->course?->course_code }} - {{ $courseAssignment->course?->course_title }}
                </small>
            </div>
            <a href="{{ route('my-courses.course-list') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back') }}</a>
        </div>
        <div class="card-body">
            <div id="my-course-feedback" class="alert d-none" role="alert"></div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" id="marks-student-search" class="form-control form-control-sm"
                        placeholder="{{ __('Search student by name or code...') }}">
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('my-courses.download-template', $courseAssignment) }}" class="btn btn-outline-primary btn-sm">
                        {{ __('Download Sample Template') }}
                    </a>
                </div>
            </div>

            <form id="my-course-import-form" enctype="multipart/form-data" class="border rounded p-3 mb-3">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-7">
                        <label class="form-label small mb-1">{{ __('Bulk Upload (Excel)') }}</label>
                        <input type="file" name="file" id="marks-import-file" class="form-control form-control-sm"
                            accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="col-md-5">
                        <button type="button" id="marks-import-btn"
                            class="btn btn-success btn-sm d-inline-flex align-items-center gap-2">
                            <span class="obe-btn-label">{{ __('Upload Marks') }}</span>
                            <span class="spinner-border spinner-border-sm d-none obe-btn-spinner" role="status"></span>
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive border rounded position-relative">
                <div id="marks-loading"
                    class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center"
                    style="z-index: 5;">
                    <div class="spinner-border text-primary" role="status"><span
                            class="visually-hidden">{{ __('Loading') }}</span></div>
                </div>

                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Student') }}</th>
                            @foreach ($markColumns as $column)
                                <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="marks-student-body"></tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div id="marks-pagination" class="small text-muted"></div>
                <button type="button" id="marks-save-btn" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <span class="obe-btn-label">{{ __('Save Marks') }}</span>
                    <span class="spinner-border spinner-border-sm d-none obe-btn-spinner" role="status"></span>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        window.__myCourseMarksConfig = {
            columns: @json($markColumns),
            initialStudents: @json($students),
            studentsRoute: @json(route('my-courses.students', $courseAssignment)),
            saveRoute: @json(route('my-courses.save-marks', $courseAssignment)),
            importRoute: @json(route('my-courses.import', $courseAssignment))
        };
    </script>
    <script src="{{ asset('assets/js/my-courses.js') }}"></script>
@endsection
