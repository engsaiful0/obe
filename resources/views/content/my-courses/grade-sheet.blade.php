@extends('layouts/layoutMaster')

@section('title', __('Grade Sheet'))

@section('content')
    @php
        $report = $report ?? [];
        $summary = $report['summary'] ?? [];
        $rows = $report['rows'] ?? [];
        $gradeScale = $report['grade_scale'] ?? collect();
    @endphp
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">{{ __('Grade Sheet') }}</h5>
                <small class="text-muted">{{ $courseAssignment->course?->course_code }} — {{ $courseAssignment->course?->course_title }}</small>
            </div>
            <div class="d-flex flex-wrap gap-1">
                <a href="{{ route('my-courses.grade-sheet.pdf', ['courseAssignment' => $courseAssignment, 'student_id' => $studentFilter]) }}" class="btn btn-danger btn-sm">{{ __('Download PDF') }}</a>
                <a href="{{ route('my-courses.grade-sheet.excel', ['courseAssignment' => $courseAssignment, 'student_id' => $studentFilter]) }}" class="btn btn-success btn-sm">{{ __('Download Excel') }}</a>
                <a href="{{ route('my-courses.grade-sheet.print', ['courseAssignment' => $courseAssignment, 'student_id' => $studentFilter]) }}" class="btn btn-outline-secondary btn-sm" target="_blank">{{ __('Print') }}</a>
                <a href="{{ route('my-courses.marks-entry', $courseAssignment) }}" class="btn btn-primary btn-sm">{{ __('Marks Entry') }}</a>
                <a href="{{ route('my-courses.course-list') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back') }}</a>
            </div>
        </div>
        <div class="card-body">
            @include('content.my-courses.partials.assignment-context', [
                'courseAssignment' => $courseAssignment,
                'batchLabels' => $report['batch_labels'] ?? [],
            ])

            <form method="GET" class="row g-2 mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">{{ __('Student Filter') }}</label>
                    <select name="student_id" class="form-select form-select-sm">
                        <option value="">{{ __('All Students') }}</option>
                        @foreach ($studentOptions as $student)
                            <option value="{{ $student->id }}" @selected((int) $studentFilter === (int) $student->id)>
                                {{ $student->student_code }} — {{ $student->student_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary btn-sm">{{ __('Apply') }}</button>
                </div>
            </form>

            <h6 class="mb-2">{{ __('Grade Table') }}</h6>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered w-auto">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Grade') }}</th>
                            <th>{{ __('Min Marks') }}</th>
                            <th>{{ __('Max Marks') }}</th>
                            <th>{{ __('Grade Point') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gradeScale as $grade)
                            <tr>
                                <td>{{ $grade->grade_name }}</td>
                                <td>{{ $grade->from_marks }}</td>
                                <td>{{ $grade->to_marks }}</td>
                                <td>{{ $grade->grade_point }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('No grades configured.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <h6 class="mb-2">{{ __('Student Results') }}</h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered" id="grade-sheet-table">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Student ID') }}</th>
                            <th>{{ __('Student Code') }}</th>
                            <th>{{ __('Registration No') }}</th>
                            <th>{{ __('Student Name') }}</th>
                            <th>{{ __('Total Marks') }}</th>
                            <th>{{ __('Percentage') }}</th>
                            <th>{{ __('Letter Grade') }}</th>
                            <th>{{ __('Grade Point') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row['student_id'] }}</td>
                                <td>{{ $row['student_code'] }}</td>
                                <td>{{ $row['registration_no'] ?: '-' }}</td>
                                <td>{{ $row['student_name'] }}</td>
                                <td>{{ number_format((float) $row['total_marks'], 2) }}</td>
                                <td>{{ number_format((float) $row['total_marks_percentage'], 2) }}</td>
                                <td>{{ $row['total_marks_grade_name'] ?? '-' }}</td>
                                <td>{{ $row['total_marks_grade_points'] !== null ? number_format((float) $row['total_marks_grade_points'], 2) : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">{{ __('No student marks found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row mt-4 g-3">
                <div class="col-md-6">
                    <h6>{{ __('Summary') }}</h6>
                    <ul class="list-unstyled mb-0">
                        <li>{{ __('Total Students') }}: <strong>{{ $summary['total_students'] ?? 0 }}</strong></li>
                        <li>{{ __('Passed Students') }}: <strong>{{ $summary['passed_students'] ?? 0 }}</strong></li>
                        <li>{{ __('Failed Students') }}: <strong>{{ $summary['failed_students'] ?? 0 }}</strong></li>
                        <li>{{ __('Highest Marks') }}: <strong>{{ number_format((float) ($summary['highest_marks'] ?? 0), 2) }}</strong></li>
                        <li>{{ __('Lowest Marks') }}: <strong>{{ number_format((float) ($summary['lowest_marks'] ?? 0), 2) }}</strong></li>
                        <li>{{ __('Average Marks') }}: <strong>{{ number_format((float) ($summary['average_marks'] ?? 0), 2) }}</strong></li>
                        <li>{{ __('Average GPA') }}: <strong>{{ number_format((float) ($summary['average_gpa'] ?? 0), 2) }}</strong></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>{{ __('Grade Distribution') }}</h6>
                    <table class="table table-sm table-bordered w-auto">
                        <thead><tr><th>{{ __('Grade') }}</th><th>{{ __('Count') }}</th></tr></thead>
                        <tbody>
                            @foreach ($summary['grade_distribution'] ?? [] as $gradeName => $count)
                                <tr>
                                    <td>{{ $gradeName }}</td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && jQuery.fn.DataTable && document.getElementById('grade-sheet-table')) {
                jQuery('#grade-sheet-table').DataTable({ pageLength: 25, order: [[3, 'asc']] });
            }
        });
    </script>
@endsection
