@extends('layouts/layoutMaster')

@section('title', __('Marks Entry'))

@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">{{ __('Marks Entry') }}</h5>
                <small class="text-muted">{{ __('Assignment details are loaded automatically.') }}</small>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ route('my-courses.grade-sheet', $courseAssignment) }}" class="btn btn-info btn-sm">{{ __('Grade Sheet') }}</a>
                <a href="{{ route('my-courses.course-list') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back') }}</a>
            </div>
        </div>
        <div class="card-body">
            @include('content.my-courses.partials.assignment-context', [
                'courseAssignment' => $courseAssignment,
                'batchLabels' => $batchLabels ?? [],
            ])

            <div id="my-course-feedback" class="alert d-none" role="alert"></div>
            @if (!empty($marksUnavailableMessage))
                <div class="alert alert-warning" role="alert">{{ $marksUnavailableMessage }}</div>
            @endif

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" id="marks-student-search" class="form-control form-control-sm"
                        placeholder="{{ __('Search by name, code, or registration no...') }}">
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('my-courses.download-template', $courseAssignment) }}" class="btn btn-outline-primary btn-sm">
                        {{ __('Download Excel Template') }}
                    </a>
                </div>
            </div>

            <div id="import" class="border rounded p-3 mb-3">
                <h6 class="mb-2">{{ __('Excel Import') }}</h6>
                <form id="my-course-import-form" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label small mb-1">{{ __('Upload Excel File') }}</label>
                            <input type="file" name="file" id="marks-import-file" class="form-control form-control-sm"
                                accept=".xlsx,.xls,.csv">
                        </div>
                        <div class="col-md-5 d-flex gap-1">
                            <button type="button" id="marks-preview-btn" class="btn btn-outline-success btn-sm">
                                {{ __('Preview Import') }}
                            </button>
                            <button type="button" id="marks-import-btn" class="btn btn-success btn-sm d-none">
                                {{ __('Confirm Import') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive border rounded position-relative" style="max-height: 70vh;">
                <div id="marks-loading"
                    class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center"
                    style="z-index: 5;">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('Loading') }}</span></div>
                </div>
                <table class="table table-sm align-middle mb-0 table-sticky">
                    <thead class="table-light">
                        <tr>
                            <th class="sticky-col">{{ __('Student') }}</th>
                            @foreach ($markColumns as $column)
                                <th class="text-nowrap">{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                            @endforeach
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('%') }}</th>
                            <th>{{ __('Grade') }}</th>
                            <th class="sticky-col-end">{{ __('Save') }}</th>
                        </tr>
                    </thead>
                    <tbody id="marks-student-body"></tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <div id="marks-pagination" class="small text-muted"></div>
                <button type="button" id="marks-save-btn" class="btn btn-primary">
                    {{ __('Bulk Save All') }}
                </button>
            </div>
            <p class="small text-muted mt-2 mb-0">
                {{ __('Maximum marks for this course: :max', ['max' => $maxMarks ?? 100]) }}
            </p>
        </div>
    </div>

    <div class="modal fade" id="importPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Import Preview') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="import-preview-table">
                            <thead class="table-light"></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" id="marks-import-confirm-btn" class="btn btn-success">{{ __('Import') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        window.__myCourseMarksConfig = {
            columns: @json($markColumns),
            maxMarks: @json($maxMarks ?? 100),
            initialStudents: @json($students),
            studentsRoute: @json(route('my-courses.students', $courseAssignment)),
            saveRoute: @json(route('my-courses.save-marks', $courseAssignment)),
            saveSingleRoute: @json(route('my-courses.save-single-mark', $courseAssignment)),
            importRoute: @json(route('my-courses.import', $courseAssignment)),
            importPreviewRoute: @json(route('my-courses.import.preview', $courseAssignment))
        };
    </script>
    <script src="{{ asset('assets/js/my-courses.js') }}"></script>
@endsection
