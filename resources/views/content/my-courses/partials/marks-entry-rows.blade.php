@foreach (($students['students'] ?? []) as $index => $student)
    <tr data-student-id="{{ $student['id'] }}">
        <td class="col-sl">{{ $index + 1 }}</td>
        <td class="col-id text-nowrap">{{ $student['student_code'] ?? '' }}</td>
        <td class="col-name">{{ $student['student_name'] ?? '' }}</td>
        @foreach ($markColumns as $column)
            @php
                $columnMax = $markColumnMax[$column] ?? ($maxMarks ?? 100);
            @endphp
            <td class="col-mark">
                <input type="text" inputmode="decimal"
                    class="mark-input excel-cell-input"
                    data-student-id="{{ $student['id'] }}"
                    data-column="{{ $column }}"
                    data-max="{{ $columnMax }}"
                    value="{{ $student['marks'][$column] ?? '' }}"
                    placeholder="0"
                    autocomplete="off">
            </td>
        @endforeach
        <td class="col-calc" data-field="total_marks" data-student-id="{{ $student['id'] }}">
            {{ number_format((float) ($student['total_marks'] ?? 0), 2) }}
        </td>
        <td class="col-calc" data-field="total_marks_percentage" data-student-id="{{ $student['id'] }}">
            {{ number_format((float) ($student['total_marks_percentage'] ?? 0), 2) }}
        </td>
        <td class="col-calc" data-field="total_marks_grade_name" data-student-id="{{ $student['id'] }}">
            {{ $student['total_marks_grade_name'] ?? '' }}
        </td>
    </tr>
@endforeach
