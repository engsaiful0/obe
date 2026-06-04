<div class="d-flex gap-1 mb-2 flex-wrap">
    <a href="{{ route('my-courses.grade-sheet.excel', $courseAssignment) }}" class="btn btn-success btn-sm">{{ __('Excel') }}</a>
    <a href="{{ route('my-courses.grade-sheet.pdf', $courseAssignment) }}" class="btn btn-danger btn-sm">{{ __('PDF') }}</a>
    <a href="{{ route('my-courses.grade-sheet.print', $courseAssignment) }}" class="btn btn-outline-secondary btn-sm" target="_blank">{{ __('Print') }}</a>
</div>
<div class="table-responsive" style="max-height: 60vh;">
    <table class="table table-sm table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>{{ __('Student ID') }}</th>
                <th>{{ __('Student Name') }}</th>
                @foreach ($markDistribution as $col)
                    <th class="text-nowrap small">{{ $col['label'] }}</th>
                @endforeach
                <th>{{ __('Total') }}</th>
                <th>{{ __('%') }}</th>
                <th>{{ __('Grade') }}</th>
                <th>{{ __('GP') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($marksRows as $row)
                <tr>
                    <td>{{ $row['student_code'] }}</td>
                    <td>{{ $row['student_name'] }}</td>
                    @foreach ($markDistribution as $col)
                        <td>{{ $row['assessment_marks'][$col['column']] ?? '-' }}</td>
                    @endforeach
                    <td>{{ $row['total_marks'] }}</td>
                    <td>{{ $row['percentage'] }}</td>
                    <td>{{ $row['grade'] }}</td>
                    <td>{{ $row['grade_point'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="20" class="text-center text-muted">{{ __('No students or marks found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
