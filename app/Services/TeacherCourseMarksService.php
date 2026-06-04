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
use Illuminate\Validation\ValidationException;

class TeacherCourseMarksService
{
    public function __construct(
        protected StudentMarksGradeCalculator $gradeCalculator
    ) {}

    /**
     * @return array<int, string>
     */
    public function markColumns(): array
    {
        return $this->gradeCalculator->editableMarkColumns();
    }

    public function studentsForAssignment(CourseAssignment $assignment, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::query()
            ->select([
                'id',
                'student_code',
                'registration_no',
                'student_name',
                'batch_id',
                'section_id',
            ])
            ->with('batch:id,batch_name,batch_code')
            ->where('students.academic_session_id', (int) $assignment->academic_session_id)
            ->where('students.program_id', (int) $assignment->program_id);

        if ((int) $assignment->section_id > 0) {
            if (Schema::hasColumn('students', 'section_id')) {
                $query->where('students.section_id', (int) $assignment->section_id);
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
                    ->orWhere('student_code', 'like', '%'.$term.'%')
                    ->orWhere('registration_no', 'like', '%'.$term.'%');
            });
        }

        return $query->orderBy('students.student_name')->paginate($perPage)->withQueryString();
    }

    /**
     * All students for this assignment (for Excel template export).
     *
     * @return Collection<int, Student>
     */
    public function allStudentsForAssignment(CourseAssignment $assignment): Collection
    {
        $query = Student::query()
            ->select([
                'students.id',
                'students.student_code',
                'students.registration_no',
                'students.student_name',
            ])
            ->where('students.academic_session_id', (int) $assignment->academic_session_id)
            ->where('students.program_id', (int) $assignment->program_id);

        if ((int) $assignment->section_id > 0) {
            if (Schema::hasColumn('students', 'section_id')) {
                $query->where('students.section_id', (int) $assignment->section_id);
            } elseif (Schema::hasColumn('students', 'section')) {
                $section = Section::query()->find((int) $assignment->section_id);
                $code = trim((string) ($section?->section_code ?? ''));
                $name = trim((string) ($section?->section_name ?? ''));
                $query->where(function ($sub) use ($code, $name) {
                    if ($code !== '') {
                        $sub->where('students.section', $code);
                    }
                    if ($name !== '') {
                        $sub->orWhere('students.section', $name);
                    }
                });
            }
        }

        return $query->orderBy('students.student_code')->get();
    }

    /**
     * @return array<int, string>
     */
    public function excelTemplateHeadings(): array
    {
        $markLabels = array_map(
            fn (string $column) => ucwords(str_replace('_', ' ', $column)),
            $this->markColumns()
        );

        return array_merge(['Student Code'], $markLabels);
    }

    /**
     * @param  array<int, string>  $rawHeader
     * @return array<int, string>
     */
    public function normalizeImportHeader(array $rawHeader): array
    {
        return array_map(fn ($cell) => $this->normalizeHeaderKey((string) $cell), $rawHeader);
    }

    public function normalizeHeaderKey(string $header): string
    {
        $key = strtolower(trim(preg_replace('/\s+/', '_', $header) ?? $header));

        if (in_array($key, $this->markColumns(), true)) {
            return $key;
        }

        return match ($key) {
            'student_code', 'student_id' => 'student_code',
            'student_name', 'name' => 'student_name',
            'registration_no', 'registration_number', 'reg_no' => 'registration_no',
            default => $key,
        };
    }

    /**
     * @return array<int, string>
     */
    public function batchLabelsForAssignment(CourseAssignment $assignment): array
    {
        return Student::query()
            ->where('students.academic_session_id', (int) $assignment->academic_session_id)
            ->where('students.program_id', (int) $assignment->program_id)
            ->when((int) $assignment->section_id > 0 && Schema::hasColumn('students', 'section_id'), function ($q) use ($assignment) {
                $q->where('students.section_id', (int) $assignment->section_id);
            })
            ->join('batches', 'batches.id', '=', 'students.batch_id')
            ->distinct()
            ->orderBy('batches.batch_name')
            ->pluck('batches.batch_name')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{student_id?:int, student_code?:string, marks:array<string, mixed>}>  $rows
     * @return array{updated:int}
     */
    public function saveMarks(CourseAssignment $assignment, array $rows): array
    {
        $defaultStatusId = $this->defaultObeStatusId();
        $componentId = $this->defaultAssessmentComponentId((int) $assignment->course_id);
        $courseId = (int) $assignment->course_id;

        $studentIds = collect($rows)
            ->map(fn (array $row) => (int) ($row['student_id'] ?? 0))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $allowedStudentIds = $this->allowedStudentIds($assignment, $studentIds);
        $batchByStudentId = $studentIds === []
            ? []
            : Student::query()
                ->whereIn('id', $studentIds)
                ->pluck('batch_id', 'id')
                ->all();

        DB::transaction(function () use ($rows, $assignment, $defaultStatusId, $componentId, $courseId, $allowedStudentIds, $batchByStudentId): void {
            foreach ($rows as $row) {
                $studentId = (int) ($row['student_id'] ?? 0);
                if ($studentId < 1 || ! in_array($studentId, $allowedStudentIds, true)) {
                    throw ValidationException::withMessages([
                        'student_id' => [__('One or more students do not belong to this course assignment.')],
                    ]);
                }

                $batchId = (int) ($batchByStudentId[$studentId] ?? 0);
                if ($batchId < 1) {
                    throw ValidationException::withMessages([
                        'student_id' => [__('Student :id must have a batch assigned to save marks.', ['id' => $studentId])],
                    ]);
                }

                $marks = $this->gradeCalculator->buildPersistedMarks($row['marks'] ?? [], $courseId);

                $existing = DB::table('student_marks')
                    ->where('academic_session_id', (int) $assignment->academic_session_id)
                    ->where('course_id', $courseId)
                    ->where('student_id', $studentId)
                    ->where('batch_id', $batchId)
                    ->when(
                        $assignment->section_id,
                        fn ($q) => $q->where('section_id', (int) $assignment->section_id),
                        fn ($q) => $q->whereNull('section_id')
                    )
                    ->first()
                    ?? DB::table('student_marks')
                        ->where('academic_session_id', (int) $assignment->academic_session_id)
                        ->where('student_id', $studentId)
                        ->where('assessment_component_id', $componentId)
                        ->first();

                $payload = array_merge($marks, [
                    'program_id' => (int) $assignment->program_id,
                    'course_id' => $courseId,
                    'batch_id' => $batchId,
                    'section_id' => $assignment->section_id ? (int) $assignment->section_id : null,
                    'assessment_component_id' => $componentId,
                    'status_id' => $defaultStatusId,
                    'updated_at' => now(),
                ]);

                if ($existing) {
                    DB::table('student_marks')
                        ->where('id', (int) $existing->id)
                        ->update($payload);
                } else {
                    DB::table('student_marks')->insert(array_merge($payload, [
                        'academic_session_id' => (int) $assignment->academic_session_id,
                        'student_id' => $studentId,
                        'created_at' => now(),
                    ]));
                }
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

        $markColumns = $this->markColumns();
        $selectColumns = array_merge(
            ['student_id', 'total_marks', 'total_marks_percentage', 'total_marks_grade_name', 'total_marks_grade_points'],
            $markColumns
        );

        return DB::table('student_marks')
            ->where('academic_session_id', (int) $assignment->academic_session_id)
            ->where('course_id', (int) $assignment->course_id)
            ->whereIn('student_id', $students->pluck('id')->all())
            ->get($selectColumns)
            ->mapWithKeys(fn ($row) => [(int) $row->student_id => (array) $row])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildGradeSheetReport(CourseAssignment $assignment, ?int $studentId = null): array
    {
        $assignment->load([
            'course:id,course_code,course_title',
            'program.department:id,name',
            'academicSession:id,session_name,academic_year',
            'section:id,section_code,section_name',
            'teacher:id,teacher_name',
            'semester:id,semester_name',
        ]);

        $students = $this->studentsForAssignment($assignment, null, 5000);
        $studentCollection = collect($students->items());

        if ($studentId !== null && $studentId > 0) {
            $studentCollection = $studentCollection->where('id', $studentId)->values();
        }

        $existing = $this->existingMarksByStudent($assignment, $studentCollection);
        $gradeScale = $this->gradeCalculator->gradeScale();

        $rows = $studentCollection->map(function ($student) use ($existing) {
            $marks = $existing[(int) $student->id] ?? [];

            return [
                'student_id' => (int) $student->id,
                'student_code' => (string) $student->student_code,
                'registration_no' => (string) ($student->registration_no ?? ''),
                'student_name' => (string) $student->student_name,
                'batch_name' => (string) ($student->batch?->batch_name ?? ''),
                'total_marks' => isset($marks['total_marks']) ? (float) $marks['total_marks'] : 0.0,
                'total_marks_percentage' => isset($marks['total_marks_percentage']) ? (float) $marks['total_marks_percentage'] : 0.0,
                'total_marks_grade_name' => $marks['total_marks_grade_name'] ?? null,
                'total_marks_grade_points' => isset($marks['total_marks_grade_points']) ? (float) $marks['total_marks_grade_points'] : null,
            ];
        })->values();

        $totals = $rows->pluck('total_marks')->filter(fn ($v) => $v > 0);
        $percentages = $rows->pluck('total_marks_percentage')->filter(fn ($v) => $v > 0);
        $gradePoints = $rows->pluck('total_marks_grade_points')->filter(fn ($v) => $v !== null);

        $passed = $rows->filter(fn (array $row) => $this->gradeCalculator->isPassingGrade(
            $row['total_marks_grade_name'],
            $row['total_marks_grade_points']
        ))->count();

        $distribution = $gradeScale
            ->mapWithKeys(fn ($grade) => [$grade->grade_name => 0])
            ->all();

        foreach ($rows as $row) {
            $gradeName = (string) ($row['total_marks_grade_name'] ?? '');
            if ($gradeName === '') {
                $gradeName = 'N/A';
            }
            $distribution[$gradeName] = ($distribution[$gradeName] ?? 0) + 1;
        }

        return [
            'assignment' => $assignment,
            'batch_labels' => $this->batchLabelsForAssignment($assignment),
            'grade_scale' => $gradeScale,
            'rows' => $rows->all(),
            'summary' => [
                'total_students' => $rows->count(),
                'passed_students' => $passed,
                'failed_students' => max(0, $rows->count() - $passed),
                'highest_marks' => $totals->isEmpty() ? 0 : round((float) $totals->max(), 2),
                'lowest_marks' => $totals->isEmpty() ? 0 : round((float) $totals->min(), 2),
                'average_marks' => $totals->isEmpty() ? 0 : round((float) $totals->avg(), 2),
                'average_gpa' => $gradePoints->isEmpty() ? 0 : round((float) $gradePoints->avg(), 2),
                'average_percentage' => $percentages->isEmpty() ? 0 : round((float) $percentages->avg(), 2),
                'grade_distribution' => $distribution,
            ],
        ];
    }

    /**
     * @param  array<int, int>  $studentIds
     * @return array<int, int>
     */
    public function allowedStudentIds(CourseAssignment $assignment, array $studentIds): array
    {
        if ($studentIds === []) {
            return [];
        }

        return Student::query()
            ->where('academic_session_id', (int) $assignment->academic_session_id)
            ->where('program_id', (int) $assignment->program_id)
            ->whereIn('id', $studentIds)
            ->when((int) $assignment->section_id > 0 && Schema::hasColumn('students', 'section_id'), function ($q) use ($assignment) {
                $q->where('section_id', (int) $assignment->section_id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array<int, array{student_id:int, marks:array<string, mixed>}>
     */
    public function parseImportRows(CourseAssignment $assignment, array $header, Collection $sheetRows): array
    {
        $header = $this->normalizeImportHeader($header);
        $markColumns = $this->markColumns();
        $requiredHeader = array_merge(['student_code'], $markColumns);

        foreach ($requiredHeader as $required) {
            if (! in_array($required, $header, true)) {
                throw ValidationException::withMessages([
                    'file' => [__('Missing required column: :column', ['column' => $required])],
                ]);
            }
        }

        $index = array_flip($header);
        $studentsByCode = $this->allStudentsForAssignment($assignment)
            ->keyBy(fn ($s) => strtolower(trim((string) $s->student_code)));

        $rows = [];
        $errors = [];

        foreach ($sheetRows as $rowIndex => $row) {
            $cells = $row->values()->all();
            $studentCode = strtolower(trim((string) ($cells[$index['student_code']] ?? '')));
            if ($studentCode === '') {
                continue;
            }

            $student = $studentsByCode->get($studentCode);
            if (! $student) {
                $errors[] = __('Row :row: student code :code is not enrolled in this course.', [
                    'row' => $rowIndex + 2,
                    'code' => $cells[$index['student_code']] ?? '',
                ]);

                continue;
            }

            $marks = [];
            foreach ($markColumns as $column) {
                $value = $cells[$index[$column]] ?? 0;
                if ($value === '' || $value === null) {
                    $marks[$column] = 0;

                    continue;
                }
                if (! is_numeric($value)) {
                    $errors[] = __('Row :row has invalid value for :column.', ['row' => $rowIndex + 2, 'column' => $column]);

                    continue 2;
                }
                $marks[$column] = (float) $value;
            }

            $rows[] = [
                'student_id' => (int) $student->id,
                'student_code' => (string) $student->student_code,
                'student_name' => (string) $student->student_name,
                'registration_no' => (string) ($student->registration_no ?? ''),
                'marks' => $marks,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => $errors]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => [__('No valid student rows found in the uploaded file.')],
            ]);
        }

        return $rows;
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
