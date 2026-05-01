<?php

namespace App\Http\Controllers;

use App\Exports\StudentMarksTemplateExport;
use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\ImportStudentMarksRequest;
use App\Http\Requests\ResetStudentMarksRequest;
use App\Http\Requests\SaveBulkStudentMarksRequest;
use App\Http\Requests\StoreStudentMarkRequest;
use App\Http\Requests\UpdateStudentMarkRequest;
use App\Imports\StudentMarksWorksheetImport;
use App\Models\AcademicSession;
use App\Models\AssessmentComponent;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Program;
use App\Models\QuestionCloMapping;
use App\Models\RelatedTo;
use App\Models\Section;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentQuestionMark;
use App\Services\StudentMarks\MarkRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StudentMarkController extends Controller
{
    use RespondsWithJsonForAjax;

    /** @return \Illuminate\Support\Collection<int, Status> */
    protected function obeStatuses(): Collection
    {
        return Status::query()
            ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function indexLookups(): array
    {
        return [
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => Course::query()->orderBy('course_code')->get(['id', 'course_code', 'course_title', 'program_id']),
            'batches' => Batch::query()->orderBy('batch_name')->get(['id', 'batch_name', 'batch_code', 'program_id']),
            'sections' => Section::query()->orderBy('section_name')->get(['id', 'section_name', 'section_code', 'program_id', 'batch_id']),
            'assessmentComponents' => AssessmentComponent::query()->orderBy('component_name')->get(['id', 'component_name', 'marks', 'course_id']),
            'academicSessions' => AcademicSession::query()->orderByDesc('academic_year')->orderBy('session_name')->get(['id', 'session_name', 'academic_year']),
            'statuses' => $this->obeStatuses(),
            'routes' => [
                'studentsApi' => route('student-marks.api.students'),
                'questionsApi' => route('student-marks.api.questions'),
                'bulkSave' => route('student-marks.bulk-save'),
                'template' => route('student-marks.template'),
                'import' => route('student-marks.import'),
                'reset' => route('student-marks.reset'),
            ],
            'cascadeUrls' => [
                'batches' => url('/ajax/program/__PROGRAM_ID__/batches'),
                'courses' => url('/ajax/program/__PROGRAM_ID__/courses'),
                'sections' => url('/ajax/batch/__BATCH_ID__/sections'),
                'assessmentComponents' => url('/ajax/question-clo/course/__COURSE_ID__/assessment-components'),
            ],
        ];
    }

    protected function filteredQuery(Request $request)
    {
        $query = StudentMark::query()->with([
            'academicSession:id,session_name,academic_year',
            'program:id,program_name,program_code',
            'course:id,course_code,course_title',
            'batch:id,batch_name,batch_code',
            'section:id,section_name,section_code',
            'assessmentComponent:id,component_name,marks,course_id',
            'student:id,student_code,student_name',
            'status:id,status_name',
        ]);

        foreach (['academic_session_id', 'program_id', 'course_id', 'batch_id', 'section_id', 'assessment_component_id', 'status_id'] as $field) {
            if ($request->filled($field)) {
                $query->where('student_marks.'.$field, (int) $request->input($field));
            }
        }

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->whereHas('student', function ($w) use ($term) {
                $w->where('student_name', 'like', $term)
                    ->orWhere('student_code', 'like', $term);
            });
        }

        return $query->latest('student_marks.id');
    }

    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $marks = $this->filteredQuery($request)->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->view('content.student-marks._table', compact('marks'));
        }

        return view('content.student-marks.index', array_merge($this->indexLookups(), compact('marks')));
    }

    public function create(): View
    {
        return view('content.student-marks.create', $this->indexLookups());
    }

    public function store(StoreStudentMarkRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $component = AssessmentComponent::query()->findOrFail((int) $data['assessment_component_id']);
        $mappings = $this->questionMappingsForContext(
            (int) $data['academic_session_id'],
            (int) $data['program_id'],
            (int) $data['course_id'],
            (int) $data['assessment_component_id'],
            ['section_id' => $data['section_id'] ?? null]
        )->keyBy(fn ($m) => (int) $m->getKey());

        $errors = [];
        $errors = array_merge($errors, MarkRules::validateCompleteQuestionSet($data['questions'], $mappings));
        $errors = array_merge($errors, MarkRules::validateQuestionsAgainstMappings(
            $data['questions'],
            $mappings,
            (float) $data['total_marks'],
            $component
        ));

        $ctxErr = $this->validateStudentInContext((int) $data['student_id'], $data);
        if ($ctxErr) {
            $errors[] = $ctxErr;
        }

        if ($errors !== []) {
            return response()->json(['message' => __('Validation failed.'), 'errors' => ['form' => $errors]], 422);
        }

        try {
            DB::transaction(fn () => $this->persistStudentMark($data, (int) $data['student_id'], (float) $data['total_marks'], (int) $data['status_id'], $data['questions']));
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => __('Could not save marks.')], 500);
        }

        return $this->respondSaved($request, __('Marks saved.'), 'student-marks.index');
    }

    public function show(StudentMark $studentMark): View
    {
        $studentMark->load([
            'academicSession',
            'program',
            'course',
            'batch',
            'section',
            'assessmentComponent',
            'student',
            'status',
            'studentQuestionMarks.questionCloMapping',
        ]);

        return view('content.student-marks.show', ['mark' => $studentMark]);
    }

    public function edit(StudentMark $student_mark): View
    {
        $student_mark->load([
            'academicSession',
            'program',
            'course',
            'batch',
            'section',
            'assessmentComponent',
            'student',
            'status',
            'studentQuestionMarks',
        ]);

        $mappings = $this->questionMappingsForContext(
            (int) $student_mark->academic_session_id,
            (int) $student_mark->program_id,
            (int) $student_mark->course_id,
            (int) $student_mark->assessment_component_id,
            ['section_id' => $student_mark->section_id]
        )->keyBy(fn ($m) => (int) $m->getKey());

        return view('content.student-marks.edit', array_merge($this->indexLookups(), [
            'mark' => $student_mark,
            'mappings' => $mappings,
        ]));
    }

    public function update(UpdateStudentMarkRequest $request, StudentMark $student_mark): JsonResponse|RedirectResponse
    {
        $component = AssessmentComponent::query()->findOrFail((int) $student_mark->assessment_component_id);

        $mappings = $this->questionMappingsForContext(
            (int) $student_mark->academic_session_id,
            (int) $student_mark->program_id,
            (int) $student_mark->course_id,
            (int) $student_mark->assessment_component_id,
            ['section_id' => $student_mark->section_id]
        )->keyBy(fn ($m) => (int) $m->getKey());

        $data = $request->validated();
        $errors = [];
        $errors = array_merge($errors, MarkRules::validateCompleteQuestionSet($data['questions'], $mappings));
        $errors = array_merge($errors, MarkRules::validateQuestionsAgainstMappings(
            $data['questions'],
            $mappings,
            (float) $data['total_marks'],
            $component
        ));

        if ($errors !== []) {
            return response()->json(['message' => __('Validation failed.'), 'errors' => ['form' => $errors]], 422);
        }

        try {
            DB::transaction(function () use ($student_mark, $data) {
                $student_mark->update([
                    'total_marks' => $data['total_marks'],
                    'status_id' => $data['status_id'],
                ]);
                $this->syncQuestionMarks($student_mark, $data['questions']);
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => __('Could not update marks.')], 500);
        }

        return $this->respondSaved($request, __('Marks updated.'), 'student-marks.index');
    }

    public function destroy(Request $request, StudentMark $student_mark): JsonResponse|RedirectResponse
    {
        $student_mark->delete();

        return $this->respondDeleted($request, __('Marks deleted.'), 'student-marks.index');
    }

    public function bulkEntry(): View
    {
        return view('content.student-marks.bulk', $this->indexLookups());
    }

    public function saveBulkMarks(SaveBulkStudentMarksRequest $request): JsonResponse
    {
        $data = $request->validated();
        $component = AssessmentComponent::query()->findOrFail((int) $data['assessment_component_id']);

        $mappings = $this->questionMappingsForContext(
            (int) $data['academic_session_id'],
            (int) $data['program_id'],
            (int) $data['course_id'],
            (int) $data['assessment_component_id'],
            ['section_id' => $data['section_id'] ?? null]
        )->keyBy(fn ($m) => (int) $m->getKey());

        $batchErrors = [];
        foreach ($data['rows'] as $i => $row) {
            $errs = [];

            $ctxErr = $this->validateStudentInContext((int) $row['student_id'], $data);
            if ($ctxErr) {
                $errs[] = $ctxErr;
            }

            $errs = array_merge($errs, MarkRules::validateCompleteQuestionSet($row['questions'], $mappings));
            $errs = array_merge($errs, MarkRules::validateQuestionsAgainstMappings(
                $row['questions'],
                $mappings,
                (float) $row['total_marks'],
                $component
            ));

            if ($errs !== []) {
                $batchErrors['rows.'.$i] = $errs;
            }
        }

        if ($batchErrors !== []) {
            return response()->json([
                'message' => __('Please fix invalid rows.'),
                'errors' => $batchErrors,
            ], 422);
        }

        try {
            DB::transaction(function () use ($data) {
                foreach ($data['rows'] as $row) {
                    $this->persistStudentMark(
                        $data,
                        (int) $row['student_id'],
                        (float) $row['total_marks'],
                        (int) $data['status_id'],
                        $row['questions']
                    );
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => __('Could not save bulk marks.')], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('Bulk marks saved.'),
            'redirect' => route('student-marks.bulk'),
        ]);
    }

    public function getStudentsByFilter(Request $request): JsonResponse
    {
        $validated = Validator::make(
            $request->all(),
            array_merge($this->inlineContextRules($request->all()), [
                'with_marks' => ['sometimes', 'boolean'],
            ])
        )->validate();

        $sectionModel = ! empty($validated['section_id'])
            ? Section::query()->find((int) $validated['section_id'])
            : null;

        $students = $this->studentsQueryForContext($validated, $sectionModel)
            ->orderBy('student_name')
            ->get(['id', 'student_code', 'student_name']);

        $existing = collect();
        if ($request->boolean('with_marks')) {
            $existingMarks = StudentMark::query()
                ->where('academic_session_id', (int) $validated['academic_session_id'])
                ->where('program_id', (int) $validated['program_id'])
                ->where('course_id', (int) $validated['course_id'])
                ->where('batch_id', (int) $validated['batch_id'])
                ->where('assessment_component_id', (int) $validated['assessment_component_id'])
                ->when(
                    empty($validated['section_id']),
                    fn ($q) => $q->whereNull('section_id'),
                    fn ($q) => $q->where('section_id', (int) $validated['section_id'])
                )
                ->with('studentQuestionMarks')
                ->get()
                ->keyBy('student_id');

            $existing = $existingMarks;
        }

        $component = AssessmentComponent::query()->find((int) $validated['assessment_component_id']);

        return response()->json([
            'students' => $students->map(function (Student $s) use ($existing) {
                $row = [
                    'id' => $s->id,
                    'student_code' => $s->student_code,
                    'student_name' => $s->student_name,
                ];

                $mark = $existing->get($s->id);
                if ($mark) {
                    $row['existing'] = [
                        'total_marks' => (float) $mark->total_marks,
                        'status_id' => (int) $mark->status_id,
                        'question_marks' => $mark->studentQuestionMarks->mapWithKeys(
                            fn (StudentQuestionMark $qm) => [(int) $qm->question_clo_mapping_id => (float) $qm->obtained_marks]
                        ),
                    ];
                }

                return $row;
            }),
            'component' => $component ? ['marks' => (float) $component->marks] : null,
        ]);
    }

    public function getQuestionsByComponent(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), $this->inlineContextRules($request->all()))->validate();

        $mappings = $this->questionMappingsForContext(
            (int) $validated['academic_session_id'],
            (int) $validated['program_id'],
            (int) $validated['course_id'],
            (int) $validated['assessment_component_id'],
            ['section_id' => $validated['section_id'] ?? null]
        );

        $component = AssessmentComponent::query()->find((int) $validated['assessment_component_id']);

        return response()->json([
            'questions' => $mappings->map(fn (QuestionCloMapping $m) => [
                'id' => (int) $m->getKey(),
                'question_label' => $m->question_label,
                'marks' => (float) $m->marks,
            ]),
            'component' => $component ? ['marks' => (float) $component->marks] : null,
        ]);
    }

    public function downloadTemplate(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $queryInput = collect($request->query())
            ->map(function ($v, $key) {
                if ($key === 'section_id' && ($v === '' || $v === false)) {
                    return null;
                }

                return $v;
            })
            ->all();

        $validator = Validator::make($queryInput, $this->inlineContextRules($queryInput));
        if ($validator->fails()) {
            return redirect()->route('student-marks.bulk')->withErrors($validator);
        }

        /** @var array<string, mixed> $validated */
        $validated = $validator->validated();
        $validated['section_id'] = $validated['section_id'] ?? null;

        $mappings = $this->questionMappingsForContext(
            (int) $validated['academic_session_id'],
            (int) $validated['program_id'],
            (int) $validated['course_id'],
            (int) $validated['assessment_component_id'],
            ['section_id' => $validated['section_id'] ?? null]
        );

        if ($mappings->isEmpty()) {
            return redirect()->route('student-marks.bulk')->with('error', __('No question–CLO mappings exist for this session and component.'));
        }

        $sectionModel = ! empty($validated['section_id'])
            ? Section::query()->find((int) $validated['section_id'])
            : null;

        $students = $this->studentsQueryForContext($validated, $sectionModel)
            ->orderBy('student_name')
            ->get(['student_code', 'student_name']);

        $headings = array_merge(
            ['Student ID', 'Student Name'],
            $mappings->map(function (QuestionCloMapping $m) {
                $max = rtrim(rtrim(number_format((float) $m->marks, 2, '.', ''), '0'), '.');

                return $m->question_label.' ('.$max.')';
            })->all(),
            ['Total']
        );

        $rows = $students->map(function (Student $s) use ($mappings) {
            $base = [(string) $s->student_code, (string) $s->student_name];
            foreach ($mappings as $_) {
                $base[] = '';
            }
            $base[] = '';

            return $base;
        });

        $fileName = 'student_marks_template_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new StudentMarksTemplateExport($headings, $rows), $fileName);
    }

    public function importExcel(ImportStudentMarksRequest $request): JsonResponse
    {
        $data = $request->validated();
        $component = AssessmentComponent::query()->findOrFail((int) $data['assessment_component_id']);

        $mappingsById = $this->questionMappingsForContext(
            (int) $data['academic_session_id'],
            (int) $data['program_id'],
            (int) $data['course_id'],
            (int) $data['assessment_component_id'],
            ['section_id' => $data['section_id'] ?? null]
        )->keyBy(fn ($m) => (int) $m->getKey());

        if ($mappingsById->isEmpty()) {
            return response()->json(['message' => __('No question–CLO mappings for this selection.')], 422);
        }

        $reader = new StudentMarksWorksheetImport;
        Excel::import($reader, $request->file('file'));

        $sheet = $reader->rows;
        if ($sheet->isEmpty()) {
            return response()->json(['message' => __('The workbook is empty.')], 422);
        }

        $headerRow = $sheet->shift();
        $header = $headerRow->map(fn ($c) => $c !== null ? trim((string) $c) : '')->values()->all();

        $parsed = $this->parseMarksHeaderColumns($header, $mappingsById);

        if ($parsed['errors'] !== []) {
            return response()->json([
                'message' => __('Invalid worksheet headers.'),
                'row_errors' => $parsed['errors'],
            ], 422);
        }

        $labelToMappingId = $parsed['labels_to_mapping_id'];
        $rowErrors = [];
        $preparedRows = [];

        foreach ($sheet as $excelRowIdx => $row) {
            $rowNumber = (int) $excelRowIdx + 2;
            /** @var array<int, mixed> */
            $cells = $row->values()->all();
            $idxSid = $parsed['indexes']['student_id'];
            $idxTotal = $parsed['indexes']['total'];
            $studentCode = isset($cells[$idxSid]) ? trim((string) $cells[$idxSid]) : '';
            $studentName = '';

            $idxName = $parsed['indexes']['student_name'];
            if ($idxName !== null && isset($cells[$idxName])) {
                $studentName = trim((string) $cells[$idxName]);
            }

            if ($studentCode === '' && $studentName === '' && $this->excelRowLooksEmpty($cells)) {
                continue;
            }

            if ($studentCode === '') {
                $rowErrors[] = __('Row :row: Student ID is required.', ['row' => $rowNumber]);

                continue;
            }

            $student = Student::query()->where('student_code', $studentCode)->first();
            if (! $student instanceof Student) {
                $rowErrors[] = __('Row :row: Student :code not found.', ['row' => $rowNumber, 'code' => $studentCode]);

                continue;
            }

            $ctxErr = $this->validateStudentInContext((int) $student->getKey(), $data);
            if ($ctxErr) {
                $rowErrors[] = __('Row :row, Student :code: :detail', [
                    'row' => $rowNumber,
                    'code' => $studentCode,
                    'detail' => $ctxErr,
                ]);

                continue;
            }

            $questions = [];
            foreach ($labelToMappingId as $label => $mapId) {
                $idx = $parsed['column_index_by_mapping_id'][$mapId] ?? null;
                if ($idx === null) {
                    continue;
                }
                $raw = $cells[$idx] ?? '';
                $rawStr = trim((string) $raw);
                $val = is_numeric($raw) ? round((float) $raw, 2) : null;
                if ($val === null && $rawStr !== '') {
                    $rowErrors[] = __('Row :row, Student :code: :label is not a valid number.', [
                        'row' => $rowNumber,
                        'code' => $studentCode,
                        'label' => $label,
                    ]);

                    continue 2;
                }
                $questions[] = [
                    'question_clo_mapping_id' => $mapId,
                    'obtained_marks' => $val ?? 0.0,
                ];
            }

            $totalRaw = $cells[$idxTotal] ?? '';
            $totalMarks = is_numeric($totalRaw) ? round((float) $totalRaw, 2) : null;
            if ($totalMarks === null) {
                $rowErrors[] = __('Row :row, Student :code: Total is missing or invalid.', ['row' => $rowNumber, 'code' => $studentCode]);

                continue;
            }

            $rowErrs = array_merge(
                MarkRules::validateCompleteQuestionSet($questions, $mappingsById),
                MarkRules::validateQuestionsAgainstMappings($questions, $mappingsById, $totalMarks, $component)
            );

            if ($rowErrs !== []) {
                foreach ($rowErrs as $msg) {
                    $rowErrors[] = __('Row :row, Student :code: :msg', ['row' => $rowNumber, 'code' => $studentCode, 'msg' => $msg]);
                }

                continue;
            }

            $preparedRows[] = [
                'student_id' => (int) $student->getKey(),
                'total_marks' => $totalMarks,
                'questions' => $questions,
            ];
        }

        if ($rowErrors !== []) {
            return response()->json([
                'message' => __('Import failed.'),
                'row_errors' => $rowErrors,
            ], 422);
        }

        try {
            DB::transaction(function () use ($data, $preparedRows) {
                foreach ($preparedRows as $row) {
                    $this->persistStudentMark(
                        $data,
                        (int) $row['student_id'],
                        (float) $row['total_marks'],
                        (int) $data['status_id'],
                        $row['questions']
                    );
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => __('Could not import marks.')], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('Marks imported successfully.'),
            'redirect' => route('student-marks.index'),
        ]);
    }

    public function resetMarks(ResetStudentMarksRequest $request): JsonResponse
    {
        $data = $request->validated();

        $query = StudentMark::query()
            ->where('academic_session_id', (int) $data['academic_session_id'])
            ->where('program_id', (int) $data['program_id'])
            ->where('course_id', (int) $data['course_id'])
            ->where('batch_id', (int) $data['batch_id'])
            ->where('assessment_component_id', (int) $data['assessment_component_id']);

        if (! empty($data['section_id'])) {
            $query->where('section_id', (int) $data['section_id']);
        }

        $marks = $query->get();

        DB::transaction(function () use ($marks) {
            foreach ($marks as $mark) {
                $mark->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => __('Removed :count mark sheet(s).', ['count' => $marks->count()]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array<string, mixed>>  $questions
     */
    protected function persistStudentMark(array $context, int $studentId, float $totalMarks, int $statusId, array $questions): void
    {
        $sectionId = $context['section_id'] ?? null;
        $sectionId = $sectionId !== null && $sectionId !== '' ? (int) $sectionId : null;

        $mark = StudentMark::withTrashed()
            ->where('academic_session_id', (int) $context['academic_session_id'])
            ->where('student_id', $studentId)
            ->where('assessment_component_id', (int) $context['assessment_component_id'])
            ->first();

        if ($mark === null) {
            $mark = new StudentMark;
        } elseif ($mark->trashed()) {
            $mark->restore();
        }

        $mark->fill([
            'academic_session_id' => (int) $context['academic_session_id'],
            'program_id' => (int) $context['program_id'],
            'course_id' => (int) $context['course_id'],
            'batch_id' => (int) $context['batch_id'],
            'section_id' => $sectionId,
            'assessment_component_id' => (int) $context['assessment_component_id'],
            'student_id' => $studentId,
            'total_marks' => $totalMarks,
            'status_id' => $statusId,
        ]);

        $mark->save();

        $this->syncQuestionMarks($mark, $questions);
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    protected function syncQuestionMarks(StudentMark $mark, array $questions): void
    {
        $ids = [];
        foreach ($questions as $q) {
            $id = (int) $q['question_clo_mapping_id'];
            $ids[] = $id;
            StudentQuestionMark::query()->updateOrCreate(
                [
                    'student_mark_id' => $mark->getKey(),
                    'question_clo_mapping_id' => $id,
                ],
                ['obtained_marks' => round((float) ($q['obtained_marks'] ?? 0), 2)]
            );
        }

        $orphan = StudentQuestionMark::query()->where('student_mark_id', $mark->getKey());
        if ($ids !== []) {
            $orphan->whereNotIn('question_clo_mapping_id', $ids);
        }
        $orphan->delete();
    }

    /**
     * @return Collection<int, QuestionCloMapping>
     */
    /**
     * @param  array<string, mixed>|null  $sectionContext  Pass ['section_id' => ?int] for mapping scope; null section_id = mappings with no section.
     */
    protected function questionMappingsForContext(
        int $academicSessionId,
        int $programId,
        int $courseId,
        int $assessmentComponentId,
        ?array $sectionContext = null
    ): Collection {
        $sectionId = null;
        if (is_array($sectionContext)) {
            $raw = $sectionContext['section_id'] ?? null;
            $sectionId = $raw !== null && $raw !== '' ? (int) $raw : null;
        }

        return QuestionCloMapping::query()
            ->where('academic_session_id', $academicSessionId)
            ->where('program_id', $programId)
            ->where('course_id', $courseId)
            ->where('assessment_component_id', $assessmentComponentId)
            ->when($sectionId === null, fn ($q) => $q->whereNull('question_clo_mappings.section_id'))
            ->when($sectionId !== null, fn ($q) => $q->where('question_clo_mappings.section_id', $sectionId))
            ->orderBy('main_question_no')
            ->orderBy('question_part')
            ->orderBy('question_label')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function studentsQueryForContext(array $context, ?Section $section): \Illuminate\Database\Eloquent\Builder
    {
        return Student::query()
            ->where('academic_session_id', (int) $context['academic_session_id'])
            ->where('program_id', (int) $context['program_id'])
            ->where('batch_id', (int) $context['batch_id'])
            ->when($section, function ($q) use ($section) {
                $code = trim((string) $section->section_code);
                $name = trim((string) $section->section_name);
                $q->where(function ($w) use ($code, $name) {
                    if ($code !== '') {
                        $w->where('students.section', $code);
                    }
                    if ($name !== '') {
                        $w->orWhere('students.section', $name);
                    }
                    if ($code === '' && $name === '') {
                        $w->whereRaw('1 = 0');
                    }
                });
            });
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function validateStudentInContext(int $studentId, array $context): ?string
    {
        $sectionModel = ! empty($context['section_id'])
            ? Section::query()->find((int) $context['section_id'])
            : null;

        $exists = $this->studentsQueryForContext($context, $sectionModel)
            ->whereKey($studentId)
            ->exists();

        return $exists ? null : __('Student is not in the selected academic session / program / batch / section.');
    }

    /** @param  array<mixed>  $cells */
    protected function excelRowLooksEmpty(array $cells): bool
    {
        foreach ($cells as $c) {
            if ($c !== null && trim((string) $c) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Collection<int, QuestionCloMapping>  $mappingsById  keyed by int id
     * @return array{errors: array<int, string>, indexes: array{student_id: int|null, student_name: int|null, total: int|null}, labels_to_mapping_id: array<string, int>, column_index_by_mapping_id: array<int, int>}
     */
    protected function parseMarksHeaderColumns(array $header, Collection $mappingsById): array
    {
        $errors = [];

        $idxStudentId = null;
        $idxStudentName = null;
        $idxTotal = null;
        foreach ($header as $i => $label) {
            $norm = strtolower(trim((string) $label));

            if (in_array($norm, ['student id', 'student_id'], true)) {
                $idxStudentId = $i;
            }
            if (in_array($norm, ['student name', 'name'], true)) {
                $idxStudentName = $i;
            }
            if ($norm === 'total') {
                $idxTotal = $i;
            }
        }

        if ($idxStudentId === null) {
            $errors[] = __('The header row must contain a Student ID column.');
        }
        if ($idxTotal === null) {
            $errors[] = __('The header row must contain a Total column.');
        }

        /** @var array<string, int> $labelToMapping */
        $labelToMapping = [];
        /** @var array<int, int> $colByMapId */
        $colByMapId = [];

        foreach ($header as $i => $cell) {
            if (! preg_match('/^(.+)\s*\(([\d.]+)\)\s*$/u', (string) $cell, $m)) {
                continue;
            }
            $qLabel = trim($m[1]);
            /** @var QuestionCloMapping|null $found */
            $found = $mappingsById->first(fn (QuestionCloMapping $row) => $row->question_label === $qLabel);
            if ($found === null) {
                $errors[] = __('Unknown question column ":col". Generate a fresh template.', ['col' => (string) $cell]);

                continue;
            }

            $id = (int) $found->getKey();
            if (isset($colByMapId[$id])) {
                $errors[] = __('Duplicate Excel column for :label.', ['label' => $qLabel]);

                continue;
            }

            $labelToMapping[$qLabel] = $id;
            $colByMapId[$id] = $i;
        }

        foreach ($mappingsById as $mapping) {
            $id = (int) $mapping->getKey();
            if (! isset($colByMapId[$id])) {
                $errors[] = __('Missing Excel column for question :label.', ['label' => $mapping->question_label]);
            }
        }

        return [
            'errors' => $errors,
            'indexes' => [
                'student_id' => $idxStudentId,
                'student_name' => $idxStudentName,
                'total' => $idxTotal,
            ],
            'labels_to_mapping_id' => $labelToMapping,
            'column_index_by_mapping_id' => $colByMapId,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    protected function inlineContextRules(?array $payload = null): array
    {
        $payload ??= request()->all();
        $programId = (int) ($payload['program_id'] ?? 0);
        $courseId = (int) ($payload['course_id'] ?? 0);
        $batchId = (int) ($payload['batch_id'] ?? 0);

        return [
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id')->where(fn ($q) => $q->where('program_id', $programId)),
            ],
            'batch_id' => [
                'required',
                'integer',
                Rule::exists('batches', 'id')->where(fn ($q) => $q->where('program_id', $programId)),
            ],
            'section_id' => [
                'nullable',
                'integer',
                Rule::exists('sections', 'id')->where(function ($q) use ($programId, $batchId) {
                    return $q->where('program_id', $programId)->where('batch_id', $batchId);
                }),
            ],
            'assessment_component_id' => [
                'required',
                'integer',
                Rule::exists('assessment_components', 'id')->where(fn ($q) => $q->where('course_id', $courseId)),
            ],
        ];
    }
}
