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
use App\Models\Gender;
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
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StudentMarkController extends Controller
{
    use RespondsWithJsonForAjax;

    /** Excel column after Student Name; plain label (no “(max)”); stored on each student_marks row for result use. */
    private const STUDENT_MARKS_EXCEL_ATTENDANCE_HEADER = 'Attendance marks';

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
                'questionsByCourseApi' => route('student-marks.api.questions-course'),
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

        if (isset($data['component_marks']) && is_array($data['component_marks'])) {
            return $this->storeMultiComponentMarks($request, $data);
        }

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

    /**
     * @param  array<string, mixed>  $data
     */
    protected function storeMultiComponentMarks(StoreStudentMarkRequest $request, array $data): JsonResponse|RedirectResponse
    {
        $base = [
            'academic_session_id' => $data['academic_session_id'],
            'program_id' => $data['program_id'],
            'course_id' => $data['course_id'],
        ];

        $ctxErr = $this->validateStudentInMinimalContext((int) $data['student_id'], $base);
        if ($ctxErr) {
            return response()->json(['message' => __('Validation failed.'), 'errors' => ['form' => [$ctxErr]]], 422);
        }

        $narrowSection = $this->narrowSectionForBulkMarks($data);

        $errors = [];
        foreach ($data['component_marks'] as $block) {
            $compId = (int) ($block['assessment_component_id'] ?? 0);
            $component = AssessmentComponent::query()->findOrFail($compId);
            $mappings = $this->questionMappingsForBulkEntry(
                (int) $data['academic_session_id'],
                (int) $data['course_id'],
                $compId,
                $narrowSection
            )->keyBy(fn ($m) => (int) $m->getKey());

            $errors = array_merge($errors, MarkRules::validateCompleteQuestionSet($block['questions'], $mappings));
            $errors = array_merge($errors, MarkRules::validateQuestionsAgainstMappings(
                $block['questions'],
                $mappings,
                (float) $block['total_marks'],
                $component
            ));
        }

        if ($errors !== []) {
            return response()->json(['message' => __('Validation failed.'), 'errors' => ['form' => $errors]], 422);
        }

        try {
            DB::transaction(function () use ($data, $base) {
                $student = Student::query()->findOrFail((int) $data['student_id']);
                $batchId = (int) $student->batch_id;
                foreach ($data['component_marks'] as $block) {
                    $ctx = array_merge($base, [
                        'batch_id' => $batchId,
                        'section_id' => null,
                        'assessment_component_id' => (int) $block['assessment_component_id'],
                    ]);
                    if (array_key_exists('attendance_marks', $data)) {
                        $ctx['attendance_marks'] = $data['attendance_marks'];
                    }
                    $this->persistStudentMark(
                        $ctx,
                        (int) $data['student_id'],
                        (float) $block['total_marks'],
                        (int) $data['status_id'],
                        $block['questions']
                    );
                }
            });
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
        $sections = Section::query()->orderBy('section_name')->get(['id', 'section_name', 'section_code', 'program_id', 'batch_id']);
        return view('content.student-marks.bulk', array_merge($this->indexLookups(), compact('sections')));
    }

    public function saveBulkMarks(SaveBulkStudentMarksRequest $request): JsonResponse
    {
        $data = $request->validated();

        $base = [
            'academic_session_id' => (int) $data['academic_session_id'],
            'program_id' => (int) $data['program_id'],
            'course_id' => (int) $data['course_id'],
            'batch_id' => (int) $data['batch_id'],
            'section_id' => $data['section_id'] ?? null,
        ];

        $narrowSection = $this->narrowSectionForBulkMarks($data);

        $batchErrors = [];
        foreach ($data['rows'] as $i => $row) {
            $errs = [];

            $ctxErr = $this->validateStudentInContext((int) $row['student_id'], $base);
            if ($ctxErr) {
                $errs[] = $ctxErr;
            }

            foreach ($row['component_marks'] ?? [] as $block) {
                $compId = (int) ($block['assessment_component_id'] ?? 0);
                $component = AssessmentComponent::query()->find($compId);
                if ($component === null) {
                    continue;
                }

                $mappings = $this->questionMappingsForBulkEntry(
                    (int) $data['academic_session_id'],
                    (int) $data['course_id'],
                    $compId,
                    $narrowSection
                )->keyBy(fn ($m) => (int) $m->getKey());

                $errs = array_merge($errs, MarkRules::validateCompleteQuestionSet($block['questions'], $mappings));
                $errs = array_merge($errs, MarkRules::validateQuestionsAgainstMappings(
                    $block['questions'],
                    $mappings,
                    (float) $block['total_marks'],
                    $component
                ));
            }

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
            DB::transaction(function () use ($data, $base) {
                foreach ($data['rows'] as $row) {
                    $student = Student::query()->find((int) $row['student_id']);
                    if ($student === null) {
                        continue;
                    }
                    foreach ($row['component_marks'] ?? [] as $block) {
                        $ctx = array_merge($base, [
                            'assessment_component_id' => (int) $block['assessment_component_id'],
                        ]);
                        if (array_key_exists('attendance_marks', $row)) {
                            $ctx['attendance_marks'] = $row['attendance_marks'];
                        }
                        $this->persistStudentMark(
                            $ctx,
                            (int) $row['student_id'],
                            (float) $block['total_marks'],
                            (int) $data['status_id'],
                            $block['questions']
                        );
                    }
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
        $allComponents = $request->boolean('all_components');
        $hasBatch = $request->filled('batch_id');

        if (! $hasBatch) {
            $rules = array_merge($this->inlineContextRulesMinimal(), [
                'with_marks' => ['sometimes'],
                'all_components' => ['sometimes'],
            ]);
            if (! $allComponents) {
                $courseId = (int) $request->input('course_id');
                $rules['assessment_component_id'] = [
                    'required',
                    'integer',
                    Rule::exists('assessment_components', 'id')->where(fn ($q) => $q->where('course_id', $courseId)),
                ];
            }
            $validated = Validator::make($request->all(), $rules)->validate();

            $students = $this->studentsQueryForMinimalContext($validated)
                ->orderBy('student_name')
                ->get(['id', 'student_code', 'student_name']);

            $existingByStudentSingle = collect();
            $existingByStudentMulti = collect();

            if ($request->boolean('with_marks')) {
                $marksQuery = StudentMark::query()
                    ->where('academic_session_id', (int) $validated['academic_session_id'])
                    ->where('program_id', (int) $validated['program_id'])
                    ->where('course_id', (int) $validated['course_id'])
                    ->with('studentQuestionMarks');

                if ($allComponents) {
                    $existingByStudentMulti = $marksQuery->get()->groupBy('student_id');
                } else {
                    $existingByStudentSingle = $marksQuery
                        ->where('assessment_component_id', (int) $validated['assessment_component_id'])
                        ->get()
                        ->keyBy('student_id');
                }
            }

            $component = $allComponents
                ? null
                : AssessmentComponent::query()->find((int) $validated['assessment_component_id']);

            return response()->json([
                'students' => $students->map(function (Student $s) use ($allComponents, $existingByStudentSingle, $existingByStudentMulti) {
                    $row = [
                        'id' => $s->id,
                        'student_code' => $s->student_code,
                        'student_name' => $s->student_name,
                    ];

                    if ($allComponents) {
                        $bundle = $existingByStudentMulti->get($s->id);
                        if ($bundle !== null && $bundle->isNotEmpty()) {
                            /** @var \Illuminate\Support\Collection<int, StudentMark> $bundle */
                            $row['existing_by_component'] = $bundle->mapWithKeys(fn (StudentMark $mark) => [
                                (int) $mark->assessment_component_id => [
                                    'total_marks' => (float) $mark->total_marks,
                                    'status_id' => (int) $mark->status_id,
                                    'question_marks' => $mark->studentQuestionMarks->mapWithKeys(
                                        fn (StudentQuestionMark $qm) => [(int) $qm->question_clo_mapping_id => (float) $qm->obtained_marks]
                                    ),
                                ],
                            ]);
                            $first = $bundle->first();
                            if ($first instanceof StudentMark) {
                                $row['attendance_marks'] = $first->getAttribute('attendance_marks');
                            }
                        }
                    } else {
                        $mark = $existingByStudentSingle->get($s->id);
                        if ($mark) {
                            $row['existing'] = [
                                'total_marks' => (float) $mark->total_marks,
                                'status_id' => (int) $mark->status_id,
                                'question_marks' => $mark->studentQuestionMarks->mapWithKeys(
                                    fn (StudentQuestionMark $qm) => [(int) $qm->question_clo_mapping_id => (float) $qm->obtained_marks]
                                ),
                            ];
                        }
                    }

                    return $row;
                }),
                'component' => $component ? ['marks' => (float) $component->marks] : null,
            ]);
        }

        $validated = Validator::make(
            $request->all(),
            array_merge($this->inlineContextRules($request->all(), ! $allComponents), [
                'with_marks' => ['sometimes'],
                'all_components' => ['sometimes'],
            ])
        )->validate();

        $sectionModel = ! empty($validated['section_id'])
            ? Section::query()->find((int) $validated['section_id'])
            : null;

        $students = $this->studentsQueryForContext($validated, $sectionModel)
            ->orderBy('student_name')
            ->get(['id', 'student_code', 'student_name']);

        $existingByStudentSingle = collect();
        $existingByStudentMulti = collect();

        if ($request->boolean('with_marks')) {
            $marksQuery = StudentMark::query()
                ->where('academic_session_id', (int) $validated['academic_session_id'])
                ->where('program_id', (int) $validated['program_id'])
                ->where('course_id', (int) $validated['course_id'])
                ->where('batch_id', (int) $validated['batch_id'])
                ->when(
                    empty($validated['section_id']),
                    fn ($q) => $q->whereNull('section_id'),
                    fn ($q) => $q->where('section_id', (int) $validated['section_id'])
                )
                ->with('studentQuestionMarks');

            if ($allComponents) {
                $existingByStudentMulti = $marksQuery->get()->groupBy('student_id');
            } else {
                $existingByStudentSingle = $marksQuery
                    ->where('assessment_component_id', (int) $validated['assessment_component_id'])
                    ->get()
                    ->keyBy('student_id');
            }
        }

        $component = $allComponents
            ? null
            : AssessmentComponent::query()->find((int) $validated['assessment_component_id']);

        return response()->json([
            'students' => $students->map(function (Student $s) use ($allComponents, $existingByStudentSingle, $existingByStudentMulti) {
                $row = [
                    'id' => $s->id,
                    'student_code' => $s->student_code,
                    'student_name' => $s->student_name,
                ];

                if ($allComponents) {
                    $bundle = $existingByStudentMulti->get($s->id);
                    if ($bundle !== null && $bundle->isNotEmpty()) {
                        /** @var \Illuminate\Support\Collection<int, StudentMark> $bundle */
                        $row['existing_by_component'] = $bundle->mapWithKeys(fn (StudentMark $mark) => [
                            (int) $mark->assessment_component_id => [
                                'total_marks' => (float) $mark->total_marks,
                                'status_id' => (int) $mark->status_id,
                                'question_marks' => $mark->studentQuestionMarks->mapWithKeys(
                                    fn (StudentQuestionMark $qm) => [(int) $qm->question_clo_mapping_id => (float) $qm->obtained_marks]
                                ),
                            ],
                        ]);
                        $first = $bundle->first();
                        if ($first instanceof StudentMark) {
                            $row['attendance_marks'] = $first->getAttribute('attendance_marks');
                        }
                    }
                } else {
                    $mark = $existingByStudentSingle->get($s->id);
                    if ($mark) {
                        $row['existing'] = [
                            'total_marks' => (float) $mark->total_marks,
                            'status_id' => (int) $mark->status_id,
                            'question_marks' => $mark->studentQuestionMarks->mapWithKeys(
                                fn (StudentQuestionMark $qm) => [(int) $qm->question_clo_mapping_id => (float) $qm->obtained_marks]
                            ),
                        ];
                    }
                }

                return $row;
            }),
            'component' => $component ? ['marks' => (float) $component->marks] : null,
        ]);
    }

    public function getQuestionsForCourse(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), ImportStudentMarksRequest::bulkMarksContextRules($request->all()))
            ->validate();

        $narrowSection = $this->narrowSectionForBulkMarks($validated);

        $components = AssessmentComponent::query()
            ->where('course_id', (int) $validated['course_id'])
            ->orderBy('component_name')
            ->get(['id', 'component_name', 'marks']);

        $payload = [];
        foreach ($components as $ac) {
            $mappings = $this->questionMappingsForBulkEntry(
                (int) $validated['academic_session_id'],
                (int) $validated['course_id'],
                (int) $ac->getKey(),
                $narrowSection
            );
            $payload[] = [
                'id' => (int) $ac->getKey(),
                'component_name' => $ac->component_name,
                'marks' => (float) $ac->marks,
                'questions' => $mappings->map(fn (QuestionCloMapping $m) => [
                    'id' => (int) $m->getKey(),
                    'question_label' => $m->question_label,
                    'marks' => (float) $m->marks,
                ])->values()->all(),
            ];
        }

        return response()->json(['components' => $payload]);
    }

    public function getQuestionsByComponent(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), $this->inlineContextRules($request->all(), true))->validate();

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

        $bulkAll = $request->boolean('bulk_all');
        $rules = $bulkAll
            ? ImportStudentMarksRequest::bulkMarksContextRules($queryInput)
            : $this->inlineContextRules($queryInput, true);
        $validator = Validator::make($queryInput, $rules);
        if ($validator->fails()) {
            return redirect()->route('student-marks.bulk')->withErrors($validator);
        }

        /** @var array<string, mixed> $validated */
        $validated = $validator->validated();
        $validated['section_id'] = $validated['section_id'] ?? null;
        $narrowSection = $this->narrowSectionForBulkMarks($validated);

        $mappings = $bulkAll
            ? $this->flattenedQuestionMappingsForBulkTemplate(
                (int) $validated['academic_session_id'],
                (int) $validated['course_id'],
                $narrowSection
            )
            : $this->questionMappingsForContext(
                (int) $validated['academic_session_id'],
                (int) $validated['program_id'],
                (int) $validated['course_id'],
                (int) $validated['assessment_component_id'],
                ['section_id' => $validated['section_id'] ?? null]
            );

        if ($mappings->isEmpty()) {
            return redirect()->route('student-marks.bulk')->with(
                'error',
                $bulkAll
                    ? __('No question–CLO mappings exist for any component in this session.')
                    : __('No question–CLO mappings exist for this session and component.')
            );
        }

        $students = $bulkAll
            ? $this->studentsQueryForContext(
                $validated,
                ! empty($validated['section_id']) ? Section::query()->find((int) $validated['section_id']) : null
            )
                ->orderBy('student_name')
                ->get(['student_code', 'student_name'])
            : $this->studentsQueryForContext(
                $validated,
                ! empty($validated['section_id']) ? Section::query()->find((int) $validated['section_id']) : null
            )
                ->orderBy('student_name')
                ->get(['student_code', 'student_name']);

        $headingParts = [
            ['Student ID', 'Student Name', self::STUDENT_MARKS_EXCEL_ATTENDANCE_HEADER],
            $mappings->map(function (QuestionCloMapping $m) use ($bulkAll) {
                $max = rtrim(rtrim(number_format((float) $m->marks, 2, '.', ''), '0'), '.');

                $label = $bulkAll ? $this->bulkExcelQuestionHeading($m) : (string) $m->question_label;

                return $label.' ('.$max.')';
            })->all(),
        ];
        if (! $bulkAll) {
            $headingParts[] = ['Total'];
        }
        $headings = array_merge(...$headingParts);

        /** @var \Illuminate\Support\Collection<int, array<int, string>> $rows */
        $rows = $students->map(function (Student $s) use ($mappings, $bulkAll) {
            $base = [(string) $s->student_code, (string) $s->student_name, ''];
            foreach ($mappings as $_) {
                $base[] = '';
            }
            if (! $bulkAll) {
                $base[] = '';
            }

            return $base;
        });

        if ($bulkAll) {
            $qCount = $mappings->count();
            $target = max(25, $students->count() + 20);
            $padTo = max(0, $target - $rows->count());
            for ($r = 0; $r < $padTo; $r++) {
                $blank = ['', '', ''];
                for ($c = 0; $c < $qCount; $c++) {
                    $blank[] = '';
                }
                $rows->push($blank);
            }
        }

        $fileName = 'student_marks_template_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new StudentMarksTemplateExport($headings, $rows), $fileName);
    }

    public function importExcel(ImportStudentMarksRequest $request): JsonResponse
    {
        $data = $request->validated();
        $bulkAll = $request->boolean('bulk_all');

        if ($bulkAll) {
            $rawBatch = $data['batch_id'] ?? null;
            $explicitBatch = $rawBatch !== null && $rawBatch !== '' && (int) $rawBatch > 0;
            $resolvedBatchId = $explicitBatch
                ? (int) $rawBatch
                : (int) Batch::query()
                    ->where('program_id', (int) $data['program_id'])
                    ->orderBy('id')
                    ->value('id');
            if ($resolvedBatchId < 1) {
                return response()->json([
                    'message' => __('Cannot import marks: add at least one batch for this program, or choose a batch when importing.'),
                ], 422);
            }
            $data['batch_id'] = $resolvedBatchId;
            if (! $explicitBatch) {
                $data['section_id'] = null;
            }
        }

        if ($bulkAll) {
            $mappingsFlat = $this->flattenedQuestionMappingsForBulkTemplate(
                (int) $data['academic_session_id'],
                (int) $data['course_id'],
                $this->narrowSectionForBulkMarks($data)
            );
            $mappingsById = $mappingsFlat->keyBy(fn ($m) => (int) $m->getKey());
        } else {
            $component = AssessmentComponent::query()->findOrFail((int) $data['assessment_component_id']);
            $mappingsById = $this->questionMappingsForContext(
                (int) $data['academic_session_id'],
                (int) $data['program_id'],
                (int) $data['course_id'],
                (int) $component->getKey(),
                ['section_id' => $data['section_id'] ?? null]
            )->keyBy(fn ($m) => (int) $m->getKey());
        }

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

        $parsed = $this->parseMarksHeaderColumns($header, $mappingsById, $bulkAll);

        if ($parsed['errors'] !== []) {
            return response()->json([
                'message' => __('Invalid worksheet headers.'),
                'row_errors' => $parsed['errors'],
            ], 422);
        }

        $labelToMappingId = $parsed['labels_to_mapping_id'];
        $baseContext = [
            'academic_session_id' => (int) $data['academic_session_id'],
            'program_id' => (int) $data['program_id'],
            'course_id' => (int) $data['course_id'],
            'batch_id' => (int) $data['batch_id'],
            'section_id' => $data['section_id'] ?? null,
        ];

        $rowErrors = [];
        $preparedRows = [];
        $importCreatedStudents = 0;
        $importExistingStudents = 0;

        foreach ($sheet as $excelRowIdx => $row) {
            $rowNumber = (int) $excelRowIdx + 2;
            /** @var array<int, mixed> */
            $cells = $row->values()->all();
            $idxSid = $parsed['indexes']['student_id'];
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

            try {
                [$student, $createdForImport] = $this->resolveStudentForMarksImport(
                    $studentCode,
                    $studentName !== '' ? $studentName : null,
                    $data
                );
            } catch (\Throwable $e) {
                report($e);
                $rowErrors[] = __('Row :row: Could not create student :code.', ['row' => $rowNumber, 'code' => $studentCode]);

                continue;
            }

            if ($createdForImport) {
                $importCreatedStudents++;
            } else {
                $importExistingStudents++;
            }

            $ctxErr = $this->validateStudentInContext((int) $student->getKey(), $baseContext);
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

            $idxAtt = $parsed['indexes']['attendance_marks'] ?? null;
            $attendanceMarks = null;
            $hasAttendanceColumn = $idxAtt !== null;
            if ($hasAttendanceColumn) {
                $rawAtt = $cells[$idxAtt] ?? '';
                $attStr = trim((string) $rawAtt);
                if ($attStr !== '') {
                    if (! is_numeric($rawAtt)) {
                        $rowErrors[] = __('Row :row, Student :code: Attendance marks must be a number or empty.', [
                            'row' => $rowNumber,
                            'code' => $studentCode,
                        ]);

                        continue;
                    }
                    $attendanceMarks = round((float) $rawAtt, 2);
                }
            }

            if ($bulkAll) {
                $totalMarks = round(array_sum(array_column($questions, 'obtained_marks')), 2);
            } else {
                $idxTotal = $parsed['indexes']['total'];
                if ($idxTotal === null) {
                    $rowErrors[] = __('Row :row, Student :code: Total column is missing from the sheet.', [
                        'row' => $rowNumber,
                        'code' => $studentCode,
                    ]);

                    continue;
                }

                $totalRaw = $cells[$idxTotal] ?? '';
                $totalMarks = is_numeric($totalRaw) ? round((float) $totalRaw, 2) : null;
                if ($totalMarks === null) {
                    $rowErrors[] = __('Row :row, Student :code: Total is missing or invalid.', ['row' => $rowNumber, 'code' => $studentCode]);

                    continue;
                }
            }

            if ($bulkAll) {
                $byComponent = [];
                foreach ($questions as $q) {
                    $mid = (int) $q['question_clo_mapping_id'];
                    $map = $mappingsById->get($mid);
                    if (! $map) {
                        continue;
                    }
                    $cid = (int) $map->assessment_component_id;
                    $byComponent[$cid][] = $q;
                }

                $rowErrsCombined = [];
                foreach ($byComponent as $cid => $qList) {
                    $componentRow = AssessmentComponent::query()->find($cid);
                    if ($componentRow === null) {
                        continue;
                    }
                    $mapSubset = $mappingsById
                        ->filter(fn (QuestionCloMapping $mm) => (int) $mm->assessment_component_id === (int) $cid)
                        ->keyBy(fn (QuestionCloMapping $mm) => (int) $mm->getKey());
                    $partialTotal = round(array_sum(array_column($qList, 'obtained_marks')), 2);

                    $rowErrsCombined = array_merge($rowErrsCombined, MarkRules::validateCompleteQuestionSet($qList, $mapSubset));
                    $rowErrsCombined = array_merge($rowErrsCombined, MarkRules::validateQuestionsAgainstMappings(
                        $qList,
                        $mapSubset,
                        $partialTotal,
                        $componentRow
                    ));
                }

                if ($rowErrsCombined !== []) {
                    foreach ($rowErrsCombined as $msg) {
                        $rowErrors[] = __('Row :row, Student :code: :msg', ['row' => $rowNumber, 'code' => $studentCode, 'msg' => $msg]);
                    }

                    continue;
                }

                $componentBlocks = [];
                foreach ($byComponent as $cid => $qList) {
                    $componentBlocks[] = [
                        'assessment_component_id' => (int) $cid,
                        'total_marks' => round(array_sum(array_column($qList, 'obtained_marks')), 2),
                        'questions' => $qList,
                    ];
                }
                $prepared = [
                    'student_id' => (int) $student->getKey(),
                    'component_marks' => $componentBlocks,
                ];
                if ($hasAttendanceColumn) {
                    $prepared['attendance_marks'] = $attendanceMarks;
                }
                $preparedRows[] = $prepared;
            } else {
                /** @var AssessmentComponent $singleComp */
                $singleComp = AssessmentComponent::query()->findOrFail((int) $data['assessment_component_id']);

                $rowErrs = array_merge(
                    MarkRules::validateCompleteQuestionSet($questions, $mappingsById),
                    MarkRules::validateQuestionsAgainstMappings($questions, $mappingsById, $totalMarks, $singleComp)
                );

                if ($rowErrs !== []) {
                    foreach ($rowErrs as $msg) {
                        $rowErrors[] = __('Row :row, Student :code: :msg', ['row' => $rowNumber, 'code' => $studentCode, 'msg' => $msg]);
                    }

                    continue;
                }

                $preparedSingle = [
                    'student_id' => (int) $student->getKey(),
                    'total_marks' => $totalMarks,
                    'questions' => $questions,
                ];
                if ($hasAttendanceColumn) {
                    $preparedSingle['attendance_marks'] = $attendanceMarks;
                }
                $preparedRows[] = $preparedSingle;
            }
        }

        if ($rowErrors !== []) {
            return response()->json([
                'message' => __('Import failed.'),
                'row_errors' => $rowErrors,
            ], 422);
        }

        $marksWritten = 0;

        try {
            DB::transaction(function () use ($data, $baseContext, $preparedRows, $bulkAll, &$marksWritten) {
                foreach ($preparedRows as $row) {
                    if ($bulkAll) {
                        foreach ($row['component_marks'] as $block) {
                            $ctx = array_merge($baseContext, [
                                'assessment_component_id' => (int) $block['assessment_component_id'],
                            ]);
                            if (array_key_exists('attendance_marks', $row)) {
                                $ctx['attendance_marks'] = $row['attendance_marks'];
                            }
                            $this->persistStudentMark(
                                $ctx,
                                (int) $row['student_id'],
                                (float) $block['total_marks'],
                                (int) $data['status_id'],
                                $block['questions']
                            );
                            $marksWritten++;
                        }
                    } else {
                        $ctx = array_merge($baseContext, ['assessment_component_id' => (int) $data['assessment_component_id']]);
                        if (array_key_exists('attendance_marks', $row)) {
                            $ctx['attendance_marks'] = $row['attendance_marks'];
                        }
                        $this->persistStudentMark(
                            $ctx,
                            (int) $row['student_id'],
                            (float) $row['total_marks'],
                            (int) $data['status_id'],
                            $row['questions']
                        );
                        $marksWritten++;
                    }
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
            'created_students' => $importCreatedStudents,
            'existing_students' => $importExistingStudents,
            'marks_inserted' => $marksWritten,
        ]);
    }

    public function resetMarks(ResetStudentMarksRequest $request): JsonResponse
    {
        $data = $request->validated();

        $bulkAll = $request->boolean('bulk_all');

        $query = StudentMark::query()
            ->where('academic_session_id', (int) $data['academic_session_id'])
            ->where('program_id', (int) $data['program_id'])
            ->where('course_id', (int) $data['course_id']);

        if ($bulkAll) {
            $query->where('batch_id', (int) $data['batch_id']);
            if (! empty($data['section_id'])) {
                $query->where('section_id', (int) $data['section_id']);
            } else {
                $query->whereNull('section_id');
            }
        } else {
            $query->where('batch_id', (int) $data['batch_id'])
                ->where('assessment_component_id', (int) $data['assessment_component_id']);

            if (! empty($data['section_id'])) {
                $query->where('section_id', (int) $data['section_id']);
            } else {
                $query->whereNull('section_id');
            }
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
     * @param  array<string, mixed>  $validatedImport  Import / template context (requires program_id, batch_id, academic_session_id; optional section_id)
     * @return array{0: Student, 1: bool}  [student, created_this_request]
     */
    protected function resolveStudentForMarksImport(string $studentCode, ?string $preferredName, array $validatedImport): array
    {
        $studentCode = trim($studentCode);

        $existing = Student::query()->where('student_code', $studentCode)->first();
        if ($existing instanceof Student) {
            return [$existing, false];
        }

        $genderId = Gender::query()->orderBy('id')->value('id');
        if ($genderId === null) {
            throw new \RuntimeException(__('No gender records found; cannot create students from import.'));
        }

        $studentRelatedToId = RelatedTo::query()->where('name', 'Student')->value('id');
        $activeStatusId = null;
        if ($studentRelatedToId !== null) {
            $activeStatusId = Status::query()
                ->where('related_to_id', $studentRelatedToId)
                ->whereRaw('LOWER(status_name) = ?', ['active'])
                ->value('id');
        }
        if ($activeStatusId === null && $studentRelatedToId !== null) {
            $activeStatusId = Status::query()
                ->where('related_to_id', $studentRelatedToId)
                ->orderBy('id')
                ->value('id');
        }

        $displayName = ($preferredName !== null && trim($preferredName) !== '') ? trim($preferredName) : $studentCode;

        $payload = [
            'program_id' => (int) $validatedImport['program_id'],
            'batch_id' => (int) $validatedImport['batch_id'],
            'academic_session_id' => (int) $validatedImport['academic_session_id'],
            'student_name' => $displayName,
            'father_name' => '—',
            'gender_id' => (int) $genderId,
            'status_id' => $activeStatusId,
        ];

        $sidRaw = $validatedImport['section_id'] ?? null;
        if (Schema::hasColumn('students', 'section_id')) {
            $payload['section_id'] = ($sidRaw !== null && $sidRaw !== '') ? (int) $sidRaw : null;
        } else {
            $sectionLabel = null;
            if ($sidRaw !== null && $sidRaw !== '') {
                $sec = Section::query()->find((int) $sidRaw);
                if ($sec instanceof Section) {
                    $code = trim((string) $sec->section_code);
                    $sectionLabel = $code !== '' ? $code : trim((string) $sec->section_name);
                    if ($sectionLabel === '') {
                        $sectionLabel = null;
                    }
                }
            }
            $payload['section'] = $sectionLabel;
        }

        try {
            $student = Student::query()->create(array_merge(['student_code' => $studentCode], $payload));

            return [$student, true];
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? '';
            $driverCode = $e->errorInfo[1] ?? null;
            $dup = $sqlState === '23000'
                || $sqlState === '23505'
                || $driverCode === 1062
                || $driverCode === 19;

            if ($dup) {
                $retry = Student::query()->where('student_code', $studentCode)->first();
                if ($retry instanceof Student) {
                    return [$retry, false];
                }
            }

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array<string, mixed>>  $questions
     */
    protected function persistStudentMark(array $context, int $studentId, float $totalMarks, int $statusId, array $questions): void
    {
        $sectionId = $context['section_id'] ?? null;
        $sectionId = $sectionId !== null && $sectionId !== '' ? (int) $sectionId : null;

        $lookup = [
            'academic_session_id' => (int) $context['academic_session_id'],
            'student_id' => $studentId,
            'assessment_component_id' => (int) $context['assessment_component_id'],
        ];

        $attributes = [
            'program_id' => (int) $context['program_id'],
            'course_id' => (int) $context['course_id'],
            'batch_id' => (int) $context['batch_id'],
            'section_id' => $sectionId,
            'total_marks' => $totalMarks,
            'status_id' => $statusId,
        ];

        if (Schema::hasColumn('student_marks', 'attendance_marks') && array_key_exists('attendance_marks', $context)) {
            $attributes['attendance_marks'] = $context['attendance_marks'];
        }

        /** @var StudentMark $mark */
        $mark = StudentMark::withTrashed()->updateOrCreate($lookup, $attributes);

        if ($mark->trashed()) {
            $mark->restore();
        }

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
     *
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
     * Bulk / multi-component marks grids: all CLO mapping rows for session + course + component.
     * Scoped by course_id (not program_id) so rows are not dropped when program_id on the mapping is wrong.
     * When section_id exists on the table and a narrow section is set, include course-wide (null) rows or that section.
     *
     * @return Collection<int, QuestionCloMapping>
     */
    protected function questionMappingsForBulkEntry(
        int $academicSessionId,
        int $courseId,
        int $assessmentComponentId,
        ?int $narrowSectionId
    ): Collection {
        $q = QuestionCloMapping::query()
            ->where('academic_session_id', $academicSessionId)
            ->where('course_id', $courseId)
            ->where('assessment_component_id', $assessmentComponentId);

        // program_id omitted: mappings are keyed by course; strict program equality hid rows saved under a mismatched program_id.

        $hasSectionColumn = Schema::hasColumn('question_clo_mappings', 'section_id');

        if ($narrowSectionId !== null && $hasSectionColumn) {
            $q->where(function ($w) use ($narrowSectionId) {
                $w->whereNull('question_clo_mappings.section_id')
                    ->orWhere('question_clo_mappings.section_id', $narrowSectionId);
            });
        }

        if ($hasSectionColumn) {
            $q->orderBy('question_clo_mappings.section_id');
        }

        return $q->orderBy('main_question_no')
            ->orderBy('question_part')
            ->orderBy('question_label')
            ->get();
    }

    /** @param  array<string, mixed>  $data */
    protected function narrowSectionForBulkMarks(array $data): ?int
    {
        $raw = $data['section_id'] ?? null;

        return $raw !== null && $raw !== '' ? (int) $raw : null;
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
                if (Schema::hasColumn('students', 'section_id')) {
                    $q->where('students.section_id', (int) $section->getKey());

                    return;
                }

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
     * All students in the program for the academic session (no batch/section filter).
     *
     * @param  array<string, mixed>  $context  academic_session_id, program_id, course_id (course used only for related validations elsewhere)
     */
    protected function studentsQueryForMinimalContext(array $context): \Illuminate\Database\Eloquent\Builder
    {
        return Student::query()
            ->where('academic_session_id', (int) $context['academic_session_id'])
            ->where('program_id', (int) $context['program_id']);
    }

    /**
     * @param  array<string, mixed>  $context  academic_session_id, program_id, course_id
     */
    protected function validateStudentInMinimalContext(int $studentId, array $context): ?string
    {
        $exists = $this->studentsQueryForMinimalContext($context)
            ->whereKey($studentId)
            ->exists();

        return $exists ? null : __('Student is not in the selected academic session and program.');
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
     * Ordered list of all question–CLO mappings for a course (every component) used for bulk template.
     *
     * @return Collection<int, QuestionCloMapping>
     */
    protected function flattenedQuestionMappingsForBulkTemplate(
        int $academicSessionId,
        int $courseId,
        ?int $sectionId
    ): Collection {
        $flat = collect();
        $components = AssessmentComponent::query()
            ->where('course_id', $courseId)
            ->orderBy('component_name')
            ->get();

        foreach ($components as $ac) {
            $maps = $this->questionMappingsForBulkEntry(
                $academicSessionId,
                $courseId,
                (int) $ac->getKey(),
                $sectionId
            );
            foreach ($maps as $m) {
                $m->loadMissing('assessmentComponent');
                $flat->push($m);
            }
        }

        return $flat;
    }

    protected function bulkExcelQuestionHeading(QuestionCloMapping $mapping): string
    {
        $mapping->loadMissing('assessmentComponent');
        $name = trim((string) ($mapping->assessmentComponent?->component_name ?? ''));

        return $name !== '' ? $name.' · '.$mapping->question_label : (string) $mapping->question_label;
    }

    /**
     * @param  Collection<int, QuestionCloMapping>  $mappingsById  keyed by int id
     * @return array{errors: array<int, string>, indexes: array{student_id: int|null, student_name: int|null, attendance_marks: int|null, total: int|null}, labels_to_mapping_id: array<string, int>, column_index_by_mapping_id: array<int, int>}
     *         For bulk compound sheets, Total is optional; indexes.total may be null.
     */
    protected function parseMarksHeaderColumns(array $header, Collection $mappingsById, bool $compoundHeaders = false): array
    {
        $errors = [];

        $idxStudentId = null;
        $idxStudentName = null;
        $idxAttendance = null;
        $idxTotal = null;
        $attendanceNorm = strtolower(trim(self::STUDENT_MARKS_EXCEL_ATTENDANCE_HEADER));

        foreach ($header as $i => $label) {
            $norm = strtolower(trim((string) $label));

            if (in_array($norm, ['student id', 'student_id'], true)) {
                $idxStudentId = $i;
            }
            if (in_array($norm, ['student name', 'name'], true)) {
                $idxStudentName = $i;
            }
            if ($norm === $attendanceNorm || $norm === 'attendance_marks') {
                $idxAttendance = $i;
            }
            if ($norm === 'total') {
                $idxTotal = $i;
            }
        }

        if ($idxStudentId === null) {
            $errors[] = __('The header row must contain a Student ID column.');
        }
        if ($idxTotal === null && ! $compoundHeaders) {
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
            $found = $mappingsById->first(function (QuestionCloMapping $row) use ($qLabel, $compoundHeaders) {
                if ($compoundHeaders) {
                    return $this->bulkExcelQuestionHeading($row) === $qLabel;
                }

                return $row->question_label === $qLabel;
            });
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
                $expected = $compoundHeaders
                    ? $this->bulkExcelQuestionHeading($mapping)
                    : $mapping->question_label;
                $errors[] = __('Missing Excel column for question :label.', ['label' => $expected]);
            }
        }

        return [
            'errors' => $errors,
            'indexes' => [
                'student_id' => $idxStudentId,
                'student_name' => $idxStudentName,
                'attendance_marks' => $idxAttendance,
                'total' => $idxTotal,
            ],
            'labels_to_mapping_id' => $labelToMapping,
            'column_index_by_mapping_id' => $colByMapId,
        ];
    }

    /**
     * Session + program + course only (used for bulk Excel and APIs without batch/section).
     *
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    protected function inlineContextRulesMinimal(?array $payload = null): array
    {
        $payload ??= request()->all();
        $programId = (int) ($payload['program_id'] ?? 0);

        return [
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id')->where(fn ($q) => $q->where('program_id', $programId)),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    protected function inlineContextRules(?array $payload = null, bool $requireAssessmentComponent = true): array
    {
        $payload ??= request()->all();
        $programId = (int) ($payload['program_id'] ?? 0);
        $courseId = (int) ($payload['course_id'] ?? 0);
        $batchId = (int) ($payload['batch_id'] ?? 0);

        $rules = [
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
        ];

        if ($requireAssessmentComponent) {
            $rules['assessment_component_id'] = [
                'required',
                'integer',
                Rule::exists('assessment_components', 'id')->where(fn ($q) => $q->where('course_id', $courseId)),
            ];
        }

        return $rules;
    }
}
