@extends('layouts/layoutMaster')

@section('title', __('Excel Marks Import'))

@section('content')
    <div class="card" id="marks-import-app">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">{{ __('Excel Marks Import') }}</h5>
                <small class="text-muted">{{ __('Upload the marks template, preview rows, then bulk save.') }}</small>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ route('my-courses.marks-entry', $courseAssignment) }}" class="btn btn-primary btn-sm">{{ __('Manual Marks Entry') }}</a>
                <a href="{{ route('my-courses.course-list') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back') }}</a>
            </div>
        </div>
        <div class="card-body">
            @include('content.my-courses.partials.assignment-context', [
                'courseAssignment' => $courseAssignment,
                'batchLabels' => $batchLabels ?? [],
            ])

            <div id="import-status-alert" class="alert d-none" role="alert"></div>
            <div id="import-zip-warning" class="alert alert-warning mb-3 d-none" role="alert"></div>

            @if (!empty($marksUnavailableMessage))
                <div class="alert alert-warning">{{ $marksUnavailableMessage }}</div>
            @else
                <div class="border rounded p-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="marks-import-file">{{ __('Marks Template File') }}</label>
                            <input type="file" id="marks-import-file" class="form-control form-control-sm"
                                accept=".xlsx,.xls,.csv">
                            <small class="text-muted">{{ __('Use the template from this page. Keep the header row unchanged.') }}</small>
                        </div>
                        <div class="col-md-6 d-flex gap-2 flex-wrap align-items-end">
                            <a href="{{ route('my-courses.download-template', $courseAssignment) }}" class="btn btn-outline-primary btn-sm">
                                {{ __('Excel Template') }}
                            </a>
                            <a href="{{ route('my-courses.download-template-csv', $courseAssignment) }}" class="btn btn-outline-secondary btn-sm">
                                {{ __('CSV Template') }}
                            </a>
                            <button type="button" id="import-upload-preview-btn" class="btn btn-success btn-sm">
                                <span class="import-btn-label">{{ __('Upload & Preview') }}</span>
                                <span class="spinner-border spinner-border-sm d-none ms-1 import-btn-spinner" role="status"></span>
                            </button>
                        </div>
                    </div>

                    <div id="import-upload-progress-wrap" class="mt-3 d-none">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="import-upload-progress-label">{{ __('Uploading...') }}</span>
                            <span id="import-upload-progress-pct">0%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div id="import-upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div id="import-preview-section" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <h6 class="mb-0">{{ __('Preview Import Data') }}</h6>
                        <button type="button" id="import-bulk-save-btn" class="btn btn-primary btn-sm" disabled>
                            <span class="import-btn-label">{{ __('Bulk Save All') }}</span>
                            <span class="spinner-border spinner-border-sm d-none ms-1 import-btn-spinner" role="status"></span>
                        </button>
                    </div>

                    <div id="import-save-progress-wrap" class="mb-3 d-none">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="import-save-progress-label">{{ __('Saving...') }}</span>
                            <span id="import-save-progress-pct">0%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div id="import-save-progress-bar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>
                    </div>

                    <div id="import-preview-summary" class="alert alert-info small py-2 d-none"></div>

                    <div class="table-responsive border rounded" style="max-height: 55vh;">
                        <table class="table table-sm table-bordered table-striped mb-0" id="import-preview-table">
                            <thead class="table-light"></thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div id="import-failed-section" class="mt-3 d-none">
                        <h6 class="text-danger">{{ __('Failed Rows') }}</h6>
                        <ul id="import-failed-list" class="small text-danger mb-0"></ul>
                    </div>

                    <div id="import-result-summary" class="alert mt-3 d-none"></div>
                </div>
            @endif
        </div>
    </div>

    <script>
        window.__myCourseImportConfig = {
            markColumns: @json($markColumns),
            markColumnLabels: @json($markColumnLabels ?? []),
            maxMarks: @json($maxMarks ?? 100),
            excelImportReady: @json($excelImportReady ?? true),
            capabilitiesRoute: @json(route('my-courses.import.capabilities', $courseAssignment)),
            previewRoute: @json(route('my-courses.import.preview', $courseAssignment)),
            bulkSaveRoute: @json(route('my-courses.import.bulk-save', $courseAssignment)),
            csrfToken: @json(csrf_token())
        };
    </script>
    @php
        $importJsPath = public_path('assets/js/my-courses-import.js');
        $importJsVersion = file_exists($importJsPath) ? filemtime($importJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/my-courses-import.js') }}?v={{ $importJsVersion }}"></script>
@endsection
