@extends('layouts/layoutMaster')

@section('title', __('Marks Entry'))

@section('content')
    <style>
        .marks-excel-wrap {
            overflow: auto;
            max-height: 75vh;
            background: #fff;
        }

        .marks-excel-table {
            border-collapse: collapse;
            width: max-content;
            min-width: 100%;
            font-size: 12px;
            margin-bottom: 0;
        }

        .marks-excel-table th,
        .marks-excel-table td {
            border: 1px solid #c5cdd5;
            padding: 0;
            vertical-align: middle;
            white-space: nowrap;
        }

        .marks-excel-table thead th {
            background: #e8eef4;
            font-weight: 600;
            text-align: center;
            padding: 6px 8px;
            position: sticky;
            top: 0;
            z-index: 3;
        }

        .marks-excel-table .col-sl {
            width: 2.5rem;
            text-align: center;
            background: #f4f6f8;
            color: #566a7f;
            font-weight: 600;
            position: sticky;
            left: 0;
            z-index: 2;
        }

        .marks-excel-table thead .col-sl {
            z-index: 4;
        }

        .marks-excel-table .col-id {
            min-width: 7.5rem;
            background: #fafbfc;
            padding: 4px 8px;
            position: sticky;
            left: 2.5rem;
            z-index: 2;
        }

        .marks-excel-table thead .col-id {
            z-index: 4;
        }

        .marks-excel-table .col-name {
            min-width: 11rem;
            max-width: 14rem;
            white-space: normal;
            background: #fafbfc;
            padding: 4px 8px;
            position: sticky;
            left: calc(2.5rem + 7.5rem);
            z-index: 2;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.06);
        }

        .marks-excel-table thead .col-name {
            z-index: 4;
        }

        .marks-excel-table .col-mark {
            min-width: 4.25rem;
            text-align: center;
        }

        .marks-excel-table .col-calc {
            min-width: 3.5rem;
            text-align: right;
            padding: 4px 8px;
            background: #f4f6f8;
            font-weight: 600;
            color: #384551;
        }

        .marks-excel-table .excel-cell-input {
            display: block;
            width: 100%;
            min-width: 4.25rem;
            border: none;
            background: transparent;
            padding: 5px 6px;
            font-size: 12px;
            text-align: center;
            line-height: 1.3;
        }

        .marks-excel-table .excel-cell-input:focus {
            outline: 2px solid #696cff;
            outline-offset: -2px;
            background: #fff;
            z-index: 1;
            position: relative;
        }

        .marks-excel-table tbody tr:hover td.col-mark {
            background: #f8fafc;
        }

        .marks-excel-table tbody tr:nth-child(even) td.col-mark {
            background: #fbfcfd;
        }

        .marks-excel-table tbody tr:nth-child(even):hover td.col-mark {
            background: #f0f4f8;
        }

        .marks-excel-table .excel-cell-input.is-over-max {
            background: #fff5f5;
            color: #d63939;
        }
    </style>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">{{ __('Marks Entry') }}</h5>
                <small class="text-muted">{{ __('Enter marks in the grid below — same layout as the Excel template.') }}</small>
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
            @else
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-5">
                        <input type="text" id="marks-student-search" class="form-control form-control-sm"
                            placeholder="{{ __('Search by name, code, or registration no...') }}">
                    </div>
                    <div class="col-md-7 text-md-end">
                        <a href="{{ route('my-courses.download-template', $courseAssignment) }}" class="btn btn-outline-primary btn-sm">
                            {{ __('Download Excel Template') }}
                        </a>
                        <a href="{{ route('my-courses.import', $courseAssignment) }}" class="btn btn-success btn-sm">
                            {{ __('Import from Excel') }}
                        </a>
                    </div>
                </div>

                <p class="small text-muted mb-2">
                    {{ __(':count student(s) — use Tab to move between cells. Total, %, and Grade update as you type.', ['count' => $students['pagination']['total'] ?? 0]) }}
                </p>

                <div class="marks-excel-wrap border rounded position-relative">
                    <div id="marks-loading"
                        class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center"
                        style="z-index: 10;">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('Loading') }}</span></div>
                    </div>
                    <table class="marks-excel-table" id="marks-excel-table">
                        <thead>
                            <tr>
                                <th class="col-sl">#</th>
                                <th class="col-id">{{ __('Student Code') }}</th>
                                <th class="col-name">{{ __('Student Name') }}</th>
                                @foreach ($markColumns as $column)
                                    <th class="col-mark">{{ $markColumnLabels[$column] ?? ucwords(str_replace('_', ' ', $column)) }}</th>
                                @endforeach
                                <th class="col-calc">{{ __('Total') }}</th>
                                <th class="col-calc">{{ __('%') }}</th>
                                <th class="col-calc">{{ __('Grade') }}</th>
                            </tr>
                        </thead>
                        <tbody id="marks-student-body">
                            @include('content.my-courses.partials.marks-entry-rows', [
                                'students' => $students,
                                'markColumns' => $markColumns,
                                'markColumnLabels' => $markColumnLabels ?? [],
                                'markColumnMax' => $markColumnMax ?? [],
                                'maxMarks' => $maxMarks ?? 100,
                            ])
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <div id="marks-pagination" class="small text-muted">
                        @if (($students['pagination']['total'] ?? 0) > 0)
                            {{ __(':total student(s) loaded', ['total' => $students['pagination']['total']]) }}
                        @endif
                    </div>
                    <button type="button" id="marks-save-btn" class="btn btn-primary">
                        {{ __('Save All Marks') }}
                    </button>
                </div>
                <p class="small text-muted mt-2 mb-0">
                    {{ __('Course maximum marks: :max', ['max' => $maxMarks ?? 100]) }}
                </p>
            @endif
        </div>
    </div>

    @if (empty($marksUnavailableMessage))
        <script>
            window.__myCourseMarksConfig = {
                columns: @json($markColumns),
                columnLabels: @json($markColumnLabels ?? []),
                maxByColumn: @json($markColumnMax ?? []),
                maxMarks: @json($maxMarks ?? 100),
                gradeScale: @json($gradeScale ?? []),
                initialStudents: @json($students),
                studentsRoute: @json(route('my-courses.students', $courseAssignment)),
                saveRoute: @json(route('my-courses.save-marks', $courseAssignment)),
                saveSingleRoute: @json(route('my-courses.save-single-mark', $courseAssignment))
            };
        </script>
        @php
            $marksJsPath = public_path('assets/js/my-courses.js');
            $marksJsVersion = file_exists($marksJsPath) ? filemtime($marksJsPath) : time();
        @endphp
        <script src="{{ asset('assets/js/my-courses.js') }}?v={{ $marksJsVersion }}"></script>
    @endif
@endsection
