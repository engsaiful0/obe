<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentMarksGradeCalculator
{
    /**
     * @return array<string, array<int, string>>
     */
    public function markColumnPoolsByType(): array
    {
        return [
            'Attendance' => ['attendance_marks'],
            'Assignment' => ['assignment_1_marks', 'assignment_2_marks', 'assignment_3_marks'],
            'Quiz' => ['class_test_1_marks', 'class_test_2_marks', 'class_test_3_marks'],
            'Midterm' => [
                'midterm_1a_marks', 'midterm_1b_marks', 'midterm_1c_marks', 'midterm_1d_marks',
                'midterm_2a_marks', 'midterm_2b_marks', 'midterm_2c_marks', 'midterm_2d_marks',
                'midterm_3a_marks', 'midterm_3b_marks', 'midterm_3c_marks', 'midterm_3d_marks',
            ],
            'Final' => [
                'final_1a_marks', 'final_1b_marks', 'final_1c_marks', 'final_1d_marks',
                'final_2a_marks', 'final_2b_marks', 'final_2c_marks', 'final_2d_marks',
                'final_3a_marks', 'final_3b_marks', 'final_3c_marks', 'final_3d_marks',
                'final_4a_marks', 'final_4b_marks', 'final_4c_marks', 'final_4d_marks',
                'final_5a_marks', 'final_5b_marks', 'final_5c_marks', 'final_5d_marks',
                'final_6a_marks', 'final_6b_marks', 'final_6c_marks', 'final_6d_marks',
            ],
            'Lab' => ['lab_marks'],
            'Project' => ['project_marks'],
            'Viva' => ['viva_marks'],
            'Presentation' => ['presentation_marks'],
            'Other' => ['other_marks'],
        ];
    }

    /**
     * Mark columns and labels for a course based on its assessment components.
     *
     * @return array{
     *     columns: array<int, string>,
     *     labels: array<string, string>,
     *     max_by_column: array<string, float>
     * }
     */
    public function markFieldsForCourse(int $courseId): array
    {
        $components = DB::table('assessment_components')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['component_name', 'component_type', 'marks']);

        if ($components->isEmpty()) {
            throw ValidationException::withMessages([
                'course' => [__('No assessment components are configured for this course.')],
            ]);
        }

        $pools = $this->markColumnPoolsByType();
        $usedByType = [];
        $columns = [];
        $labels = [];
        $maxByColumn = [];

        foreach ($components as $component) {
            $type = (string) ($component->component_type ?? 'Other');
            $pool = $pools[$type] ?? $pools['Other'];
            $index = $usedByType[$type] ?? 0;

            if ($index >= count($pool)) {
                throw ValidationException::withMessages([
                    'course' => [__('Too many :type components for the available mark fields.', ['type' => $type])],
                ]);
            }

            $column = $pool[$index];
            $usedByType[$type] = $index + 1;
            $columns[] = $column;

            $cap = rtrim(rtrim(number_format((float) $component->marks, 2, '.', ''), '0'), '.');
            $labels[$column] = trim((string) $component->component_name).' ('.$cap.')';
            $maxByColumn[$column] = (float) $component->marks;
        }

        return [
            'columns' => $columns,
            'labels' => $labels,
            'max_by_column' => $maxByColumn,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function editableMarkColumns(): array
    {
        return [
            'attendance_marks',
            'assignment_1_marks',
            'assignment_2_marks',
            'assignment_3_marks',
            'class_test_1_marks',
            'class_test_2_marks',
            'class_test_3_marks',
            'midterm_1a_marks',
            'midterm_1b_marks',
            'midterm_1c_marks',
            'midterm_1d_marks',
            'midterm_2a_marks',
            'midterm_2b_marks',
            'midterm_2c_marks',
            'midterm_2d_marks',
            'midterm_3a_marks',
            'midterm_3b_marks',
            'midterm_3c_marks',
            'midterm_3d_marks',
            'final_1a_marks',
            'final_1b_marks',
            'final_1c_marks',
            'final_1d_marks',
            'final_2a_marks',
            'final_2b_marks',
            'final_2c_marks',
            'final_2d_marks',
            'final_3a_marks',
            'final_3b_marks',
            'final_3c_marks',
            'final_3d_marks',
            'final_4a_marks',
            'final_4b_marks',
            'final_4c_marks',
            'final_4d_marks',
            'final_5a_marks',
            'final_5b_marks',
            'final_5c_marks',
            'final_5d_marks',
            'final_6a_marks',
            'final_6b_marks',
            'final_6c_marks',
            'final_6d_marks',
            'lab_marks',
            'project_marks',
            'viva_marks',
            'presentation_marks',
            'other_marks',
        ];
    }

    public function courseMaxMarks(int $courseId): float
    {
        $max = (float) DB::table('assessment_components')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->sum('marks');

        return $max > 0 ? $max : 100.0;
    }

    /**
     * Grouped columns shown on the teacher grade sheet (sums per category).
     *
     * @return array<int, array{key: string, label: string, columns: array<int, string>}>
     */
    public function gradeSheetGroupedColumns(): array
    {
        $pools = $this->markColumnPoolsByType();

        return [
            [
                'key' => 'attendance_marks',
                'label' => 'Attendance',
                'columns' => $pools['Attendance'],
            ],
            [
                'key' => 'assignment_marks',
                'label' => 'Assignment',
                'columns' => $pools['Assignment'],
            ],
            [
                'key' => 'class_test_marks',
                'label' => 'Class Test',
                'columns' => $pools['Quiz'],
            ],
            [
                'key' => 'midterm_marks',
                'label' => 'Mid',
                'columns' => $pools['Midterm'],
            ],
            [
                'key' => 'final_marks',
                'label' => 'Final',
                'columns' => $pools['Final'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $marksRow
     * @param  array<int, string>  $columns
     */
    public function sumMarkColumns(array $marksRow, array $columns): float
    {
        $sum = 0.0;
        foreach ($columns as $column) {
            $sum += (float) ($marksRow[$column] ?? 0);
        }

        return round($sum, 2);
    }

    public function markColumnDisplayLabel(string $column): string
    {
        if ($column === 'attendance_marks') {
            return 'Attendance Marks';
        }

        $base = str_replace('_marks', '', $column);
        $label = ucwords(str_replace('_', ' ', $base));

        return $label.' Marks';
    }

    /**
     * All student_marks input columns with display labels (teacher entry / Excel).
     *
     * @return array{
     *     columns: array<int, string>,
     *     labels: array<string, string>,
     *     max_by_column: array<string, float>
     * }
     */
    public function allMarkFields(float $courseMaxMarks = 100.0): array
    {
        $columns = $this->editableMarkColumns();
        $labels = [];
        $maxByColumn = [];

        foreach ($columns as $column) {
            $labels[$column] = $this->markColumnDisplayLabel($column);
            $maxByColumn[$column] = $courseMaxMarks;
        }

        return [
            'columns' => $columns,
            'labels' => $labels,
            'max_by_column' => $maxByColumn,
        ];
    }

    /**
     * @param  array<string, mixed>  $rawMarks
     * @return array<string, float|null|string>
     */
    public function buildPersistedMarks(array $rawMarks, int $courseId): array
    {
        $columns = $this->editableMarkColumns();
        $normalized = [];
        $maxMarks = $this->courseMaxMarks($courseId);

        foreach ($columns as $column) {
            $value = $rawMarks[$column] ?? 0;
            if ($value === '' || $value === null) {
                $normalized[$column] = 0.0;

                continue;
            }
            if (! is_numeric($value)) {
                throw ValidationException::withMessages([
                    $column => [__(':field must be a numeric value.', ['field' => $column])],
                ]);
            }
            $floatValue = (float) $value;
            if ($floatValue < 0) {
                throw ValidationException::withMessages([
                    $column => [__('Marks cannot be negative.')],
                ]);
            }
            if ($floatValue > $maxMarks + 0.0001) {
                throw ValidationException::withMessages([
                    $column => [__('Marks cannot exceed the course maximum (:max).', ['max' => $maxMarks])],
                ]);
            }
            $normalized[$column] = round($floatValue, 2);
        }

        $total = round(collect($columns)->sum(fn (string $col) => $normalized[$col] ?? 0.0), 2);
        if ($total > $maxMarks) {
            throw ValidationException::withMessages([
                'total_marks' => [__('Total marks (:total) cannot exceed the maximum allowed marks (:max).', [
                    'total' => $total,
                    'max' => $maxMarks,
                ])],
            ]);
        }

        $percentage = $maxMarks > 0 ? round(($total / $maxMarks) * 100, 2) : 0.0;
        $grade = $this->resolveGrade($percentage);

        return array_merge($normalized, [
            'total_marks' => $total,
            'total_marks_percentage' => $percentage,
            'total_marks_grade_name' => $grade?->grade_name,
            'total_marks_grade_points' => $grade?->grade_point !== null ? round((float) $grade->grade_point, 2) : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $rawMarks
     * @return array{success: bool, marks: array<string, float|null|string>, error: ?string}
     */
    public function tryBuildPersistedMarks(array $rawMarks, int $courseId): array
    {
        try {
            return [
                'success' => true,
                'marks' => $this->buildPersistedMarks($rawMarks, $courseId),
                'error' => null,
            ];
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->filter()->first();

            return [
                'success' => false,
                'marks' => [],
                'error' => $message ? (string) $message : __('Invalid marks.'),
            ];
        }
    }

    public function resolveGrade(float $percentage): ?Grade
    {
        return Grade::query()
            ->where('from_marks', '<=', $percentage)
            ->where('to_marks', '>=', $percentage)
            ->orderByDesc('from_marks')
            ->first();
    }

    /**
     * @return Collection<int, Grade>
     */
    public function gradeScale(): Collection
    {
        return Grade::query()->orderByDesc('from_marks')->get();
    }

    public function isPassingGrade(?string $gradeName, ?float $gradePoints): bool
    {
        if ($gradeName === null || $gradeName === '') {
            return false;
        }

        if (strtoupper($gradeName) === 'F') {
            return false;
        }

        return ($gradePoints ?? 0) > 0;
    }
}
