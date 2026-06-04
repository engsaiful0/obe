<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentMarksGradeCalculator
{
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
            ->sum('marks');

        return $max > 0 ? $max : 100.0;
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
            if ($floatValue > $maxMarks) {
                throw ValidationException::withMessages([
                    $column => [__('Marks cannot exceed the maximum allowed marks (:max).', ['max' => $maxMarks])],
                ]);
            }
            $normalized[$column] = round($floatValue, 2);
        }

        $total = round(array_sum($normalized), 2);
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
