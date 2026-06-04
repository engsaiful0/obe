<?php

namespace App\Services;

use App\Support\SpreadsheetImportSupport;
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

    /**
     * @return array<int, string>
     */
    public function markColumnsForAssignment(CourseAssignment $assignment): array
    {
        return $this->markFieldsForAssignment($assignment)['columns'];
    }

    /**
     * All student_marks table columns for manual entry and Excel import.
     *
     * @return array{
     *     columns: array<int, string>,
     *     labels: array<string, string>,
     *     max_by_column: array<string, float>
     * }
     */
    public function markFieldsForAssignment(CourseAssignment $assignment): array
    {
        $courseMax = $this->gradeCalculator->courseMaxMarks((int) $assignment->course_id);

        return $this->gradeCalculator->allMarkFields($courseMax);
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
    public function excelTemplateHeadings(CourseAssignment $assignment): array
    {
        $fields = $this->markFieldsForAssignment($assignment);

        return array_merge(
            ['Student Code'],
            array_map(fn (string $column) => $fields['labels'][$column] ?? $column, $fields['columns'])
        );
    }

    /**
     * @param  array<int, string>  $rawHeader
     * @return array<int, string>
     */
    public function normalizeImportHeader(array $rawHeader): array
    {
        return array_map(fn ($cell) => $this->normalizeHeaderKey((string) $cell), $rawHeader);
    }

    /**
     * @param  array<int, string>  $rawHeader
     * @return array<int, string>
     */
    public function normalizeImportHeaderForAssignment(array $rawHeader, CourseAssignment $assignment): array
    {
        $fields = $this->markFieldsForAssignment($assignment);
        $labelToColumn = [
            'student id' => 'student_code',
            'student_code' => 'student_code',
            'student code' => 'student_code',
            'student name' => 'student_name',
            'student_name' => 'student_name',
            'name' => 'student_name',
            'total' => '__summary_total__',
            'percentage' => '__summary_percentage__',
            '%' => '__summary_percentage__',
            'grade' => '__summary_grade__',
        ];

        foreach ($fields['columns'] as $column) {
            $labelToColumn[$column] = $column;
            $label = $fields['labels'][$column] ?? $column;
            $labelToColumn[strtolower(trim($label))] = $column;
            $labelToColumn[strtolower(trim($this->normalizeHeaderKey($label)))] = $column;
        }

        return array_map(function (string $cell) use ($labelToColumn, $fields): string {
            $trimmed = trim($cell);
            $normalized = strtolower(trim($this->normalizeHeaderKey($trimmed)));

            if (isset($labelToColumn[$normalized])) {
                return $labelToColumn[$normalized];
            }

            if (isset($labelToColumn[strtolower($trimmed)])) {
                return $labelToColumn[strtolower($trimmed)];
            }

            if (preg_match('/^(.+?)\s*\(([\d.]+)\)\s*$/u', $trimmed, $headerMatch)) {
                $headerName = strtolower(trim($headerMatch[1]));
                foreach ($fields['labels'] as $column => $label) {
                    if (preg_match('/^(.+?)\s*\(([\d.]+)\)\s*$/u', (string) $label, $labelMatch)) {
                        if (strtolower(trim($labelMatch[1])) === $headerName) {
                            return $column;
                        }
                    }
                }
            }

            return $normalized;
        }, $rawHeader);
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
     * @return array{processed:int, inserted:int, updated:int, failed:int, errors:array<int, string>}
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

        $inserted = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::transaction(function () use (
            $rows,
            $assignment,
            $defaultStatusId,
            $componentId,
            $courseId,
            $allowedStudentIds,
            $batchByStudentId,
            &$inserted,
            &$updated,
            &$failed,
            &$errors
        ): void {
            foreach ($rows as $row) {
                $studentId = (int) ($row['student_id'] ?? 0);
                if ($studentId < 1 || ! in_array($studentId, $allowedStudentIds, true)) {
                    $failed++;
                    $errors[] = __('Student does not belong to this course assignment.');

                    continue;
                }

                $batchId = (int) ($batchByStudentId[$studentId] ?? 0);
                if ($batchId < 1) {
                    $failed++;
                    $errors[] = __('Student :id must have a batch assigned.', ['id' => $studentId]);

                    continue;
                }

                $built = $this->gradeCalculator->tryBuildPersistedMarks($row['marks'] ?? [], $courseId);
                if (! $built['success']) {
                    $failed++;
                    $errors[] = (string) ($built['error'] ?? __('Invalid marks.'));

                    continue;
                }

                $existing = $this->findExistingMarkRow($assignment, $courseId, $studentId, $batchId, $componentId);

                $payload = array_merge($built['marks'], [
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
                    $updated++;
                } else {
                    DB::table('student_marks')->insert(array_merge($payload, [
                        'academic_session_id' => (int) $assignment->academic_session_id,
                        'student_id' => $studentId,
                        'created_at' => now(),
                    ]));
                    $inserted++;
                }
            }
        });

        return [
            'processed' => count($rows),
            'inserted' => $inserted,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{preview: array<int, array<string, mixed>>, rows: array<int, array<string, mixed>>, summary: array<string, int>}
     */
    public function parseImportSheetForPreview(CourseAssignment $assignment, array $header, Collection $sheetRows): array
    {
        $header = $this->normalizeImportHeaderForAssignment($header, $assignment);
        $markColumns = $this->markColumnsForAssignment($assignment);
        $courseId = (int) $assignment->course_id;

        if (! in_array('student_code', $header, true)) {
            throw ValidationException::withMessages([
                'file' => [__('Missing required column: Student Code. Use the downloaded template.')],
            ]);
        }

        $index = array_flip($header);
        $columnWidth = count($header);
        $studentsByCode = $this->allStudentsForAssignment($assignment)
            ->keyBy(fn ($s) => strtolower(trim((string) $s->student_code)));

        $preview = [];
        $saveRows = [];
        $failed = 0;
        $valid = 0;

        foreach ($sheetRows as $rowIndex => $row) {
            $cells = SpreadsheetImportSupport::padCellsToWidth($row->values()->all(), $columnWidth);
            if ($this->rowCellsEmpty($cells)) {
                continue;
            }
            $rawCode = trim((string) ($cells[$index['student_code']] ?? ''));
            $rawCode = preg_replace('/^\xEF\xBB\xBF/u', '', $rawCode) ?? $rawCode;
            if ($rawCode === '') {
                continue;
            }

            $line = $rowIndex + 2;
            $studentCodeKey = strtolower($rawCode);
            $student = $studentsByCode->get($studentCodeKey);

            $marks = [];
            $rowErrors = [];

            foreach ($markColumns as $column) {
                if (! array_key_exists($column, $index)) {
                    $marks[$column] = 0;

                    continue;
                }
                $value = $cells[$index[$column]] ?? 0;
                if ($value === '' || $value === null) {
                    $marks[$column] = 0;

                    continue;
                }
                if (! is_numeric($value)) {
                    $rowErrors[] = __(':column must be numeric.', ['column' => $column]);

                    continue;
                }
                $marks[$column] = (float) $value;
            }

            if (! $student) {
                $failed++;
                $preview[] = $this->previewRowPayload($line, $rawCode, $marks, 'failed', __('Student code not found in this course.'));
                continue;
            }

            if ($rowErrors !== []) {
                $failed++;
                $preview[] = $this->previewRowPayload($line, $rawCode, $marks, 'failed', implode(' ', $rowErrors));
                continue;
            }

            $built = $this->gradeCalculator->tryBuildPersistedMarks($marks, $courseId);
            if (! $built['success']) {
                $failed++;
                $preview[] = $this->previewRowPayload($line, $rawCode, $marks, 'failed', (string) $built['error']);
                continue;
            }

            $valid++;
            $preview[] = $this->previewRowPayload($line, $rawCode, $marks, 'ok', null);
            $saveRows[] = [
                'student_id' => (int) $student->id,
                'student_code' => (string) $student->student_code,
                'marks' => $marks,
            ];
        }

        if ($preview === []) {
            throw ValidationException::withMessages([
                'file' => [__('No student rows found in the uploaded file.')],
            ]);
        }

        return [
            'preview' => $preview,
            'rows' => $saveRows,
            'summary' => [
                'total_rows' => count($preview),
                'valid_rows' => $valid,
                'failed_rows' => $failed,
            ],
        ];
    }

    /**
     * @param  array<int, array{student_id:int, marks:array<string, mixed>}>  $rows
     * @return array{processed:int, inserted:int, updated:int, failed:int, errors:array<int, string>}
     */
    public function bulkSaveImportedMarks(CourseAssignment $assignment, array $rows): array
    {
        if ($rows === []) {
            throw ValidationException::withMessages([
                'rows' => [__('No valid rows to save.')],
            ]);
        }

        return $this->saveMarks($assignment, $rows);
    }

    private function findExistingMarkRow(
        CourseAssignment $assignment,
        int $courseId,
        int $studentId,
        int $batchId,
        int $componentId
    ): ?object {
        return DB::table('student_marks')
            ->where('academic_session_id', (int) $assignment->academic_session_id)
            ->where('program_id', (int) $assignment->program_id)
            ->where('course_id', $courseId)
            ->where('batch_id', $batchId)
            ->where('student_id', $studentId)
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
    }

    /**
     * @param  array<string, float>  $marks
     * @return array<string, mixed>
     */
    /**
     * @param  array<int, mixed>  $cells
     */
    private function rowCellsEmpty(array $cells): bool
    {
        foreach ($cells as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function previewRowPayload(
        int $line,
        string $studentCode,
        array $marks,
        string $status,
        ?string $error
    ): array {
        return array_merge(
            ['row' => $line, 'student_code' => $studentCode, 'status' => $status, 'error' => $error],
            $marks
        );
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
        $header = $this->normalizeImportHeaderForAssignment($header, $assignment);
        $markColumns = $this->markColumnsForAssignment($assignment);

        if (! in_array('student_code', $header, true)) {
            throw ValidationException::withMessages([
                'file' => [__('Missing required column: Student Code. Use the downloaded template.')],
            ]);
        }

        $index = array_flip($header);
        $columnWidth = count($header);
        $studentsByCode = $this->allStudentsForAssignment($assignment)
            ->keyBy(fn ($s) => strtolower(trim((string) $s->student_code)));

        $rows = [];
        $errors = [];

        foreach ($sheetRows as $rowIndex => $row) {
            $cells = SpreadsheetImportSupport::padCellsToWidth($row->values()->all(), $columnWidth);
            $studentCode = strtolower(trim((string) ($cells[$index['student_code']] ?? '')));
            $studentCode = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/u', '', $studentCode) ?? $studentCode));
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
