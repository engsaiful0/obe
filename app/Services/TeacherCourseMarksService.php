<?php

namespace App\Services;

use App\Models\CourseAssignment;
use App\Models\RelatedTo;
use App\Models\Section;
use App\Models\Status;
use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherCourseMarksService
{
    /**
     * @return array<int, string>
     */
    public function markColumns(): array
    {
        $columns = Schema::getColumnListing('student_marks');
        $excluded = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'course_id',
            'student_id',
            'academic_session_id',
            'program_id',
            'batch_id',
            'section_id',
            'assessment_component_id',
            'status_id',
        ];

        return array_values(array_filter($columns, fn (string $column) => ! in_array($column, $excluded, true)));
    }

    public function studentsForAssignment(CourseAssignment $assignment, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::query()
            ->where('academic_session_id', (int) $assignment->academic_session_id)
            ->where('program_id', (int) $assignment->program_id);

        if ((int) $assignment->section_id > 0) {
            if (Schema::hasColumn('students', 'section_id')) {
                $query->where('section_id', (int) $assignment->section_id);
            } elseif (Schema::hasColumn('students', 'section')) {
                $section = $assignment->relationLoaded('section')
                    ? $assignment->section
                    : Section::query()->find((int) $assignment->section_id);

                $code = trim((string) ($section?->section_code ?? ''));
                $name = trim((string) ($section?->section_name ?? ''));
                $query->where(function ($sub) use ($code, $name) {
                    if ($code !== '') {
                        $sub->where('section', $code);
                    }
                    if ($name !== '') {
                        $sub->orWhere('section', $name);
                    }
                });
            }
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($sub) use ($term) {
                $sub->where('student_name', 'like', '%'.$term.'%')
                    ->orWhere('student_code', 'like', '%'.$term.'%');
            });
        }

        return $query->orderBy('student_name')->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<int, array{student_id:int, marks:array<string, mixed>}>  $rows
     * @return array{updated:int}
     */
    public function saveMarks(CourseAssignment $assignment, array $rows): array
    {
        $markColumns = $this->markColumns();
        $defaultStatusId = $this->defaultObeStatusId();
        $componentId = $this->defaultAssessmentComponentId((int) $assignment->course_id);

        $studentIds = collect($rows)
            ->map(fn (array $row) => (int) ($row['student_id'] ?? 0))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
        $batchByStudentId = $studentIds === []
            ? []
            : Student::query()
                ->whereIn('id', $studentIds)
                ->pluck('batch_id', 'id')
                ->all();

        DB::transaction(function () use ($rows, $assignment, $markColumns, $defaultStatusId, $componentId, $batchByStudentId): void {
            foreach ($rows as $row) {
                $studentId = (int) $row['student_id'];
                $batchId = (int) ($batchByStudentId[$studentId] ?? 0);
                if ($batchId < 1) {
                    abort(422, __('Student :id must have a batch assigned to save marks.', ['id' => $studentId]));
                }

                $marks = [];
                $total = 0.0;

                foreach ($markColumns as $column) {
                    $value = (float) ($row['marks'][$column] ?? 0);
                    $marks[$column] = round($value, 2);

                    if ($column !== 'total_marks') {
                        $total += $value;
                    }
                }

                if (in_array('total_marks', $markColumns, true)) {
                    $marks['total_marks'] = round($total, 2);
                }

                DB::table('student_marks')->updateOrInsert(
                    [
                        'academic_session_id' => (int) $assignment->academic_session_id,
                        'student_id' => $studentId,
                        'assessment_component_id' => $componentId,
                    ],
                    array_merge($marks, [
                        'program_id' => (int) $assignment->program_id,
                        'course_id' => (int) $assignment->course_id,
                        'batch_id' => $batchId,
                        'section_id' => $assignment->section_id ? (int) $assignment->section_id : null,
                        'status_id' => $defaultStatusId,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );
            }
        });

        return ['updated' => count($rows)];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function existingMarksByStudent(CourseAssignment $assignment, Collection $students): array
    {
        if ($students->isEmpty()) {
            return [];
        }

        $componentId = $this->defaultAssessmentComponentId((int) $assignment->course_id);
        $markColumns = $this->markColumns();
        $selectColumns = array_merge(['student_id'], $markColumns);

        return DB::table('student_marks')
            ->where('academic_session_id', (int) $assignment->academic_session_id)
            ->where('course_id', (int) $assignment->course_id)
            ->where('assessment_component_id', $componentId)
            ->whereIn('student_id', $students->pluck('id')->all())
            ->get($selectColumns)
            ->mapWithKeys(fn ($row) => [(int) $row->student_id => (array) $row])
            ->all();
    }

    private function defaultAssessmentComponentId(int $courseId): int
    {
        $componentId = (int) DB::table('assessment_components')
            ->where('course_id', $courseId)
            ->orderBy('id')
            ->value('id');

        if ($componentId < 1) {
            abort(422, 'No assessment component found for this course.');
        }

        return $componentId;
    }

    private function defaultObeStatusId(): int
    {
        $relatedToId = RelatedTo::query()->where('name', 'OBE')->value('id');
        $statusId = (int) Status::query()
            ->where('related_to_id', $relatedToId)
            ->orderBy('id')
            ->value('id');

        if ($statusId < 1) {
            abort(422, 'No OBE status found. Please configure statuses first.');
        }

        return $statusId;
    }
}
