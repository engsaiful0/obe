@extends('layouts/layoutMaster')

@section('title', __('Excel Marks Import'))

@section('content')
    <div class="card" id="marks-import-app"
        data-preview-route="{{ route('my-courses.import.preview', $courseAssignment) }}"
        data-bulk-save-route="{{ route('my-courses.import.bulk-save', $courseAssignment) }}"
        data-capabilities-route="{{ route('my-courses.import.capabilities', $courseAssignment) }}"
        data-csrf-token="{{ csrf_token() }}"
        data-max-marks="{{ $maxMarks ?? 100 }}"
        data-excel-import-ready="{{ ($excelImportReady ?? true) ? '1' : '0' }}">
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

            <div id="import-status-alert" class="alert alert-info" role="alert">
                {{ __('Select your file, then click Upload & Preview.') }}
            </div>
            <div id="import-zip-warning" class="alert alert-warning mb-3 {{ ($excelImportReady ?? true) ? 'd-none' : '' }}" role="alert">
                @if (empty($excelImportReady))
                    {{ __('Excel (.xlsx) needs the PHP zip extension in Apache. Restart XAMPP after enabling extension=zip, or upload a .csv file.') }}
                @endif
            </div>

            @if (!empty($marksUnavailableMessage))
                <div class="alert alert-warning">{{ $marksUnavailableMessage }}</div>
            @else
                <div class="border rounded p-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="marks-import-file">{{ __('Marks Template File') }}</label>
                            <input type="file" id="marks-import-file" class="form-control form-control-sm"
                                accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv">
                            <small class="text-muted">{{ __('Use the template from this page. Keep the header row unchanged.') }}</small>
                        </div>
                        <div class="col-md-6 d-flex gap-2 flex-wrap align-items-end">
                            <a href="{{ route('my-courses.download-template', $courseAssignment) }}" class="btn btn-outline-primary btn-sm">
                                {{ __('Excel Template') }}
                            </a>
                            <a href="{{ route('my-courses.download-template-csv', $courseAssignment) }}" class="btn btn-outline-secondary btn-sm">
                                {{ __('CSV Template') }}
                            </a>
                            <button type="button" id="import-upload-preview-btn" class="btn btn-success btn-sm"
                                onclick="if (window.__myCourseImportUploadClick) { window.__myCourseImportUploadClick(event); } else { var a=document.getElementById('import-status-alert'); if(a){a.className='alert alert-danger';a.textContent='Import script failed to load. Hard-refresh (Ctrl+F5).';} }">
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
                        <button type="button" id="import-bulk-save-btn" class="btn btn-primary btn-sm" disabled
                            onclick="if (window.__myCourseImportBulkSaveClick) { window.__myCourseImportBulkSaveClick(event); }">
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

    @if (empty($marksUnavailableMessage))
        @php
            $importConfig = [
                'markColumns' => $markColumns ?? [],
                'markColumnLabels' => $markColumnLabels ?? [],
                'maxMarks' => $maxMarks ?? 100,
                'excelImportReady' => $excelImportReady ?? true,
                'previewRoute' => route('my-courses.import.preview', $courseAssignment),
                'bulkSaveRoute' => route('my-courses.import.bulk-save', $courseAssignment),
                'capabilitiesRoute' => route('my-courses.import.capabilities', $courseAssignment),
                'csrfToken' => csrf_token(),
            ];
            $importJsRel = 'assets/js/my-courses-import.js';
            $importJsPath = file_exists(base_path($importJsRel))
                ? base_path($importJsRel)
                : public_path($importJsRel);
            $importJsVersion = file_exists($importJsPath) ? filemtime($importJsPath) : time();
        @endphp
        <script type="application/json" id="my-course-import-config-json">{!! json_encode($importConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
        <script>
            window.__myCourseImportConfig = @json($importConfig);
        </script>
        <script src="{{ asset('assets/js/my-courses-import.js') }}?v={{ $importJsVersion }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (!window.__myCourseImportBooted) {
                        var el = document.getElementById('import-status-alert');
                        if (el) {
                            el.className = 'alert alert-danger';
                            el.textContent = '{{ __('Import script did not load. Press Ctrl+F5 to hard-refresh, or check that assets/js/my-courses-import.js is reachable.') }}';
                        }
                    }
                }, 1500);
            });
        </script>
    @endif
@endsection
