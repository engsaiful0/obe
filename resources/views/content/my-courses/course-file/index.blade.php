@extends('layouts/layoutMaster')

@section('title', __('Course File'))

@section('content')
    @php
        $dashboard = $dashboard ?? [];
        $checklist = $checklist ?? [];
        $completionPercent = $completionPercent ?? 0;
        $canManage = $canManage ?? false;
        $markFields = ['columns' => $markDistribution ? collect($markDistribution)->pluck('column')->all() : [], 'labels' => collect($markDistribution ?? [])->pluck('label', 'column')->all()];
    @endphp
    <div class="card mb-3" id="course-file-app"
        data-upload-url="{{ route('my-courses.course-file.documents.upload', $courseAssignment) }}"
        data-cqi-url="{{ route('my-courses.course-file.cqi.save', $courseAssignment) }}"
        data-csrf="{{ csrf_token() }}"
        data-can-manage="{{ $canManage ? '1' : '0' }}">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">{{ __('Course File') }}</h5>
                <small class="text-muted">{{ $courseAssignment->course?->course_code }} — {{ $courseAssignment->course?->course_title }}</small>
            </div>
            <div class="d-flex flex-wrap gap-1">
                <a href="{{ route('my-courses.course-file.pdf', $courseAssignment) }}" class="btn btn-danger btn-sm">{{ __('Full PDF') }}</a>
                <a href="{{ route('my-courses.course-file.print', $courseAssignment) }}" class="btn btn-outline-secondary btn-sm" target="_blank">{{ __('Print') }}</a>
                <a href="{{ route('my-courses.marks-entry', $courseAssignment) }}" class="btn btn-primary btn-sm">{{ __('Marks') }}</a>
                <a href="{{ route('my-courses.grade-sheet', $courseAssignment) }}" class="btn btn-info btn-sm">{{ __('Grade Sheet') }}</a>
                <a href="{{ route('my-courses.course-list') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back') }}</a>
            </div>
        </div>
        <div class="card-body">
            @include('content.my-courses.partials.assignment-context', [
                'courseAssignment' => $courseAssignment,
                'batchLabels' => $batchLabels ?? [],
            ])

            <div class="mb-4">
                <div class="d-flex justify-content-between small mb-1">
                    <span>{{ __('Course File Completion') }}: <strong id="cf-completion-label">{{ $completionPercent }}%</strong></span>
                </div>
                <div class="progress" style="height: 14px;">
                    <div id="cf-completion-bar" class="progress-bar bg-success" style="width: {{ min(100, $completionPercent) }}%"></div>
                </div>
                <ul class="list-unstyled small mt-2 mb-0 row g-1">
                    @foreach ($checklist as $item)
                        <li class="col-md-6">
                            @if ($item['done'])
                                <span class="text-success">✓</span>
                            @else
                                <span class="text-danger">✗</span>
                            @endif
                            {{ $item['label'] }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="row g-2 mb-4">
                @foreach ([
                    ['label' => __('Students'), 'value' => $dashboard['total_students'] ?? 0],
                    ['label' => __('CLOs'), 'value' => $dashboard['total_clos'] ?? 0],
                    ['label' => __('Assessments'), 'value' => $dashboard['total_assessments'] ?? 0],
                    ['label' => __('Avg Marks'), 'value' => $dashboard['average_marks'] ?? 0],
                    ['label' => __('Avg GPA'), 'value' => $dashboard['average_gpa'] ?? 0],
                    ['label' => __('Pass Rate %'), 'value' => $dashboard['pass_rate'] ?? 0],
                    ['label' => __('Course Attainment %'), 'value' => $dashboard['course_attainment'] ?? 0],
                    ['label' => __('Documents'), 'value' => $dashboard['uploaded_documents'] ?? 0],
                ] as $card)
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center h-100">
                            <div class="text-muted small">{{ $card['label'] }}</div>
                            <div class="fw-bold fs-5">{{ $card['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <ul class="nav nav-pills flex-wrap mb-3" id="course-file-tabs" role="tablist">
                @foreach ([
                    'info' => __('1. Course Info'),
                    'outline' => __('2. Outline'),
                    'clos' => __('3. CLOs'),
                    'assessments' => __('4. Assessments'),
                    'papers' => __('5. Papers'),
                    'marks-dist' => __('6. Mark Dist.'),
                    'marks' => __('7. Marks'),
                    'grades' => __('8. Grades'),
                    'clo-att' => __('9. CLO Att.'),
                    'plo-att' => __('10. PLO Att.'),
                    'course-att' => __('11. Course Att.'),
                    'scripts' => __('12. Scripts'),
                    'attendance' => __('13. Attendance'),
                    'materials' => __('14. Materials'),
                    'cqi' => __('15. CQI'),
                    'feedback' => __('16. Feedback'),
                    'docs' => __('17. Documents'),
                ] as $key => $label)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#cf-{{ $key }}" type="button">{{ $label }}</button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" id="course-file-tab-content">
                <div class="tab-pane fade show active" id="cf-info">
                    @include('content.my-courses.course-file.sections.info', ['courseInfo' => $courseInfo, 'courseAssignment' => $courseAssignment])
                </div>
                <div class="tab-pane fade" id="cf-outline">
                    @include('content.my-courses.course-file.sections.documents', [
                        'courseAssignment' => $courseAssignment,
                        'sectionKey' => 'course_outline',
                        'types' => $documentSections['course_outline'] ?? [],
                        'documentsByType' => $documentsByType,
                        'documentTypes' => $documentTypes,
                        'canManage' => $canManage,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-clos">
                    @include('content.my-courses.course-file.sections.clos', ['clos' => $clos])
                </div>
                <div class="tab-pane fade" id="cf-assessments">
                    @include('content.my-courses.course-file.sections.assessments', ['assessments' => $assessments])
                </div>
                <div class="tab-pane fade" id="cf-papers">
                    @include('content.my-courses.course-file.sections.documents', [
                        'courseAssignment' => $courseAssignment,
                        'sectionKey' => 'question_papers',
                        'types' => $documentSections['question_papers'] ?? [],
                        'documentsByType' => $documentsByType,
                        'documentTypes' => $documentTypes,
                        'canManage' => $canManage,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-marks-dist">
                    @include('content.my-courses.course-file.sections.mark-distribution', ['markDistribution' => $markDistribution])
                </div>
                <div class="tab-pane fade" id="cf-marks">
                    @include('content.my-courses.course-file.sections.student-marks', [
                        'marksRows' => $marksRows,
                        'markDistribution' => $markDistribution,
                        'courseAssignment' => $courseAssignment,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-grades">
                    @include('content.my-courses.course-file.sections.grade-sheet', [
                        'gradeReport' => $gradeReport,
                        'courseAssignment' => $courseAssignment,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-clo-att">
                    @include('content.my-courses.course-file.sections.clo-attainment', ['cloAttainment' => $cloAttainment])
                </div>
                <div class="tab-pane fade" id="cf-plo-att">
                    @include('content.my-courses.course-file.sections.plo-attainment', ['ploAttainment' => $ploAttainment])
                </div>
                <div class="tab-pane fade" id="cf-course-att">
                    @include('content.my-courses.course-file.sections.course-attainment', ['courseAttainment' => $courseAttainment, 'gradeReport' => $gradeReport])
                </div>
                <div class="tab-pane fade" id="cf-scripts">
                    @include('content.my-courses.course-file.sections.documents', [
                        'courseAssignment' => $courseAssignment,
                        'sectionKey' => 'student_scripts',
                        'types' => $documentSections['student_scripts'] ?? [],
                        'documentsByType' => $documentsByType,
                        'documentTypes' => $documentTypes,
                        'canManage' => $canManage,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-attendance">
                    @include('content.my-courses.course-file.sections.documents', [
                        'courseAssignment' => $courseAssignment,
                        'sectionKey' => 'attendance',
                        'types' => $documentSections['attendance'] ?? [],
                        'documentsByType' => $documentsByType,
                        'documentTypes' => $documentTypes,
                        'canManage' => $canManage,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-materials">
                    @include('content.my-courses.course-file.sections.documents', [
                        'courseAssignment' => $courseAssignment,
                        'sectionKey' => 'teaching_materials',
                        'types' => $documentSections['teaching_materials'] ?? [],
                        'documentsByType' => $documentsByType,
                        'documentTypes' => $documentTypes,
                        'canManage' => $canManage,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-cqi">
                    @include('content.my-courses.course-file.sections.cqi', [
                        'cqi' => $courseFile->cqi,
                        'canManage' => $canManage,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-feedback">
                    @include('content.my-courses.course-file.sections.documents', [
                        'courseAssignment' => $courseAssignment,
                        'sectionKey' => 'feedback',
                        'types' => $documentSections['feedback'] ?? [],
                        'documentsByType' => $documentsByType,
                        'documentTypes' => $documentTypes,
                        'canManage' => $canManage,
                    ])
                </div>
                <div class="tab-pane fade" id="cf-docs">
                    @include('content.my-courses.course-file.sections.documents', [
                        'courseAssignment' => $courseAssignment,
                        'sectionKey' => 'additional_documents',
                        'types' => $documentSections['additional_documents'] ?? [],
                        'documentsByType' => $documentsByType,
                        'documentTypes' => $documentTypes,
                        'canManage' => $canManage,
                    ])
                </div>
            </div>
        </div>
    </div>

    <div id="cf-status-alert" class="alert d-none position-fixed bottom-0 end-0 m-3 shadow" style="z-index: 1080; max-width: 420px;"></div>
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        window.__courseFileCharts = {
            clo: @json($cloAttainment['chart'] ?? []),
            plo: @json($ploAttainment['chart'] ?? []),
            grades: @json($gradeReport['grade_distribution'] ?? []),
        };
    </script>
    @php
        $cfJsRel = 'assets/js/course-file.js';
        $cfJsPath = file_exists(base_path($cfJsRel)) ? base_path($cfJsRel) : public_path($cfJsRel);
        $cfJsVersion = file_exists($cfJsPath) ? filemtime($cfJsPath) : time();
    @endphp
    <script src="{{ asset($cfJsRel) }}?v={{ $cfJsVersion }}"></script>
@endsection
