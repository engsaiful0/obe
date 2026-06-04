<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('my-courses.course-file.print', ['courseAssignment' => $courseAssignment ?? request()->route('courseAssignment'), 'section' => 'info']) }}" class="btn btn-outline-secondary btn-sm" target="_blank">{{ __('Print') }}</a>
</div>
<table class="table table-sm table-bordered">
    <tbody>
        @foreach ([
            __('Course Code') => $courseInfo['course_code'] ?? '-',
            __('Course Title') => $courseInfo['course_title'] ?? '-',
            __('Credit Hours') => $courseInfo['credit_hours'] ?? '-',
            __('Theory/Lab') => $courseInfo['theory_lab'] ?? '-',
            __('Academic Session') => trim(($courseInfo['academic_session'] ?? '-').' '.($courseInfo['academic_year'] ?? '')),
            __('Program') => $courseInfo['program'] ?? '-',
            __('Batch') => $courseInfo['batch'] ?? '-',
            __('Section') => $courseInfo['section'] ?? '-',
            __('Semester') => $courseInfo['semester'] ?? '-',
            __('Instructor') => $courseInfo['instructor'] ?? '-',
        ] as $label => $value)
            <tr>
                <th class="bg-light" style="width: 30%">{{ $label }}</th>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
