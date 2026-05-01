<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\StoreBulkQuestionCloMappingRequest;
use App\Http\Requests\UpdateQuestionCloMappingRequest;
use App\Models\AcademicSession;
use App\Models\AssessmentComponent;
use App\Models\Bloom;
use App\Models\Clo;
use App\Models\Course;
use App\Models\Program;
use App\Models\QuestionCloMapping;
use App\Models\RelatedTo;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionCloMappingController extends Controller
{
    use RespondsWithJsonForAjax;

    protected function obeStatuses(): \Illuminate\Support\Collection
    {
        return Status::query()
            ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);
    }

    protected function bloomsForLookup(): \Illuminate\Support\Collection
    {
        $active = Bloom::query()
            ->with('status')
            ->whereHas('status', fn ($q) => $q->where('status_name', 'Active'))
            ->orderBy('level_order')
            ->get();

        return $active->isNotEmpty()
            ? $active
            : Bloom::query()->with('status')->orderBy('level_order')->get();
    }

    protected function cascadeUrls(): array
    {
        return [
            'courses' => url('/ajax/question-clo/program/__PROGRAM_ID__/courses'),
            'assessmentComponents' => url('/ajax/question-clo/course/__COURSE_ID__/assessment-components'),
            'clos' => url('/ajax/question-clo/course/__COURSE_ID__/clos'),
            'bloomByClo' => url('/ajax/question-clo/clo/__CLO_ID__/bloom'),
        ];
    }

    protected function filteredQuery(Request $request)
    {
        $query = QuestionCloMapping::query()->with([
            'program:id,program_name,program_code',
            'course:id,course_code,course_title,program_id',
            'assessmentComponent:id,component_name,marks,course_id',
            'clo:id,clo_code,title,course_id,bloom_id',
            'bloom:id,name,level_order',
            'status:id,status_name',
            'academicSession:id,session_name,academic_year',
        ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->where('question_clo_mappings.main_question_no', 'like', $term)
                    ->orWhere('question_clo_mappings.question_part', 'like', $term)
                    ->orWhere('question_clo_mappings.question_label', 'like', $term)
                    ->orWhere('question_clo_mappings.question_title', 'like', $term)
                    ->orWhere('question_clo_mappings.question_description', 'like', $term)
                    ->orWhereHas('course', function ($cq) use ($term) {
                        $cq->where('course_code', 'like', $term)
                            ->orWhere('course_title', 'like', $term);
                    })
                    ->orWhereHas('clo', function ($cq) use ($term) {
                        $cq->where('clo_code', 'like', $term)
                            ->orWhere('title', 'like', $term);
                    })
                    ->orWhereHas('bloom', function ($bq) use ($term) {
                        $bq->where('name', 'like', $term);
                    });
            });
        }

        foreach (['program_id', 'course_id', 'assessment_component_id', 'academic_session_id', 'clo_id', 'bloom_id', 'status_id'] as $field) {
            if ($request->filled($field)) {
                $query->where('question_clo_mappings.'.$field, (int) $request->input($field));
            }
        }

        return $query->latest('question_clo_mappings.id');
    }

    /** @return \Illuminate\Support\Collection<int, AcademicSession> */
    protected function academicSessionsForForms()
    {
        return AcademicSession::query()
            ->orderByDesc('academic_year')
            ->orderBy('session_name')
            ->get(['id', 'session_name', 'academic_year']);
    }

    protected function indexLookups(): array
    {
        return [
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => Course::query()->orderBy('course_code')->get(['id', 'course_code', 'course_title', 'program_id']),
            'assessmentComponents' => AssessmentComponent::query()->orderBy('component_name')->get([
                'id',
                'component_name',
                'course_id',
                'marks',
            ]),
            'clos' => Clo::query()->orderBy('clo_code')->get(['id', 'clo_code', 'title', 'course_id', 'bloom_id']),
            'blooms' => $this->bloomsForLookup(),
            'statuses' => $this->obeStatuses(),
            'cascadeUrls' => $this->cascadeUrls(),
            'academicSessions' => $this->academicSessionsForForms(),
        ];
    }

    public function coursesByProgram(Program $program): JsonResponse
    {
        $items = Course::query()
            ->where('program_id', $program->getKey())
            ->where('status', 'Active')
            ->orderBy('course_code')
            ->get(['id', 'course_code', 'course_title']);

        if ($items->isEmpty()) {
            $items = Course::query()
                ->where('program_id', $program->getKey())
                ->orderBy('course_code')
                ->get(['id', 'course_code', 'course_title']);
        }

        return response()->json($items->map(fn (Course $c) => [
            'id' => $c->id,
            'course_code' => $c->course_code,
            'course_title' => $c->course_title,
        ]));
    }

    public function assessmentComponentsByCourse(Course $course): JsonResponse
    {
        $items = AssessmentComponent::query()
            ->where('course_id', $course->getKey())
            ->whereHas('status', fn ($s) => $s->where('status_name', 'Active'))
            ->orderBy('component_name')
            ->get(['id', 'component_name', 'marks', 'has_multiple_questions']);

        if ($items->isEmpty()) {
            $items = AssessmentComponent::query()
                ->where('course_id', $course->getKey())
                ->orderBy('component_name')
                ->get(['id', 'component_name', 'marks', 'has_multiple_questions']);
        }

        return response()->json($items->map(fn (AssessmentComponent $ac) => [
            'id' => $ac->id,
            'component_name' => $ac->component_name,
            'marks' => (float) $ac->marks,
            'has_multiple_questions' => (bool) ($ac->has_multiple_questions ?? false),
        ]));
    }

    public function closByCourse(Course $course): JsonResponse
    {
        $clos = Clo::query()
            ->where('course_id', $course->getKey())
            ->whereHas('status', fn ($s) => $s->where('status_name', 'Active'))
            ->orderBy('clo_code')
            ->get(['id', 'clo_code', 'title', 'bloom_id']);

        if ($clos->isEmpty()) {
            $clos = Clo::query()
                ->where('course_id', $course->getKey())
                ->orderBy('clo_code')
                ->get(['id', 'clo_code', 'title', 'bloom_id']);
        }

        return response()->json($clos->map(fn (Clo $clo) => [
            'id' => $clo->id,
            'clo_code' => $clo->clo_code,
            'title' => $clo->title,
            'bloom_id' => $clo->bloom_id,
        ]));
    }

    public function bloomByClo(Clo $clo): JsonResponse
    {
        $clo->loadMissing('bloom:id,name');
        $b = $clo->bloom;

        return response()->json([
            'bloom_id' => $b?->id,
            'bloom_name' => $b?->name,
        ]);
    }

    public function index(Request $request): View|\Illuminate\Contracts\View\View
    {
        $mappings = $this->filteredQuery($request)->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('content.question-clo-mappings._table', compact('mappings'));
        }

        return view('content.question-clo-mappings.index', array_merge(
            compact('mappings'),
            $this->indexLookups()
        ));
    }

    protected function formLookups(?QuestionCloMapping $row): array
    {
        $programId = $row?->program_id ?? (old('program_id') !== null && old('program_id') !== ''
            ? (int) old('program_id')
            : null);
        $courseId = $row?->course_id ?? (old('course_id') !== null && old('course_id') !== ''
            ? (int) old('course_id')
            : null);

        $courses = collect();
        if ($programId) {
            $q = Course::query()->where('program_id', $programId)->orderBy('course_code');
            $active = (clone $q)->where('status', 'Active')->get();
            $courses = $active->isNotEmpty() ? $active : $q->get();
        }

        $assessmentComponents = collect();
        $clos = collect();
        if ($courseId) {
            $acQ = AssessmentComponent::query()->where('course_id', $courseId)->orderBy('component_name');
            $acActive = (clone $acQ)->whereHas('status', fn ($s) => $s->where('status_name', 'Active'))->get();
            $assessmentComponents = $acActive->isNotEmpty() ? $acActive : $acQ->get();

            $cloQ = Clo::query()->where('course_id', $courseId)->orderBy('clo_code');
            $cloActive = (clone $cloQ)->whereHas('status', fn ($s) => $s->where('status_name', 'Active'))->get();
            $clos = $cloActive->isNotEmpty() ? $cloActive : $cloQ->get();
        }

        $academicSessions = $this->academicSessionsForForms();
        if ($row && $row->academic_session_id && ! $academicSessions->pluck('id')->contains($row->academic_session_id)) {
            $missingSession = AcademicSession::query()->find($row->academic_session_id, ['id', 'session_name', 'academic_year']);
            if ($missingSession) {
                $academicSessions = $academicSessions->prepend($missingSession)
                    ->sortByDesc(fn (AcademicSession $s) => $s->academic_year)->values();
            }
        }

        return array_merge([
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => $courses,
            'assessmentComponents' => $assessmentComponents,
            'clos' => $clos,
            'blooms' => $this->bloomsForLookup(),
            'statuses' => $this->obeStatuses(),
            'cascadeUrls' => $this->cascadeUrls(),
            'academicSessions' => $academicSessions,
        ], $row ? ['mapping' => $row] : []);
    }

    public function create(): View
    {
        return view('content.question-clo-mappings.create', array_merge($this->formLookups(null), [
            'wizardBlooms' => $this->bloomsForLookup()->map(fn (Bloom $b) => [
                'id' => $b->id,
                'label' => $b->level_order.'. '.$b->name,
            ])->values()->all(),
            'wizardStatuses' => $this->obeStatuses()->map(fn (Status $s) => [
                'id' => $s->id,
                'status_name' => $s->status_name,
            ])->values()->all(),
        ]));
    }

    public function store(StoreBulkQuestionCloMappingRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            foreach ($validated['mains'] as $main) {
                foreach ($main['parts'] as $part) {
                    QuestionCloMapping::query()->create([
                        'program_id' => $validated['program_id'],
                        'course_id' => $validated['course_id'],
                        'assessment_component_id' => $validated['assessment_component_id'],
                        'academic_session_id' => $validated['academic_session_id'],
                        'main_question_no' => $main['main_question_no'],
                        'main_question_marks' => $main['main_question_marks'],
                        'has_multiple_questions' => $main['has_multiple_questions'],
                        'question_part' => $part['question_part'] ?? null,
                        'question_label' => $part['question_label'],
                        'marks' => $part['marks'],
                        'clo_id' => $part['clo_id'],
                        'bloom_id' => $part['bloom_id'] ?? null,
                        'status_id' => $part['status_id'],
                        'remarks' => $part['remarks'] ?? null,
                        'question_title' => null,
                        'question_description' => null,
                    ]);
                }
            }
        });

        return $this->respondSaved($request, __('Question-CLO mappings saved successfully.'), 'question-clo-mappings.index');
    }

    public function show(QuestionCloMapping $question_clo_mapping): View
    {
        $question_clo_mapping->load(['program', 'course', 'academicSession', 'assessmentComponent', 'clo', 'bloom', 'status']);

        return view('content.question-clo-mappings.show', ['mapping' => $question_clo_mapping]);
    }

    public function edit(QuestionCloMapping $question_clo_mapping): View
    {
        return view('content.question-clo-mappings.edit', $this->formLookups($question_clo_mapping));
    }

    public function update(UpdateQuestionCloMappingRequest $request, QuestionCloMapping $question_clo_mapping): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        $scopedAssessmentId = (int) $question_clo_mapping->assessment_component_id;
        $scopedMainNo = (string) $question_clo_mapping->main_question_no;
        $scopedSessionId = (int) $question_clo_mapping->academic_session_id;

        DB::transaction(function () use ($data, $question_clo_mapping, $scopedAssessmentId, $scopedMainNo, $scopedSessionId) {
            $question_clo_mapping->update($data);

            QuestionCloMapping::query()
                ->where('assessment_component_id', $scopedAssessmentId)
                ->where('main_question_no', $scopedMainNo)
                ->where('academic_session_id', $scopedSessionId)
                ->update([
                    'main_question_marks' => $data['main_question_marks'],
                    'has_multiple_questions' => $data['has_multiple_questions'],
                ]);
        });

        return $this->respondSaved($request, __('Question-CLO mapping updated successfully.'), 'question-clo-mappings.index');
    }

    public function destroy(Request $request, QuestionCloMapping $question_clo_mapping): JsonResponse|RedirectResponse
    {
        $question_clo_mapping->delete();

        return $this->respondDeleted($request, __('Question-CLO mapping deleted successfully.'), 'question-clo-mappings.index');
    }

    public function matrix(Request $request): View
    {
        $programs = Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']);
        $programId = (int) $request->query('program_id', 0);
        $courseId = (int) $request->query('course_id', 0);
        $assessmentComponentId = (int) $request->query('assessment_component_id', 0);
        $academicSessionId = (int) $request->query('academic_session_id', 0);

        $coursesForMatrix = $programId
            ? Course::query()->where('program_id', $programId)->orderBy('course_code')->get()
            : collect();

        $componentsForMatrix = $courseId
            ? AssessmentComponent::query()->where('course_id', $courseId)->orderBy('component_name')->get()
            : collect();

        $rows = collect();
        $selectedComponent = null;
        $componentCap = null;
        $mappedTotal = null;
        $remaining = null;

        if ($programId && $courseId) {
            $courseOk = Course::query()->whereKey($courseId)->where('program_id', $programId)->exists();
            if (! $courseOk) {
                return redirect()
                    ->route('question-clo-mappings.matrix', ['program_id' => $programId])
                    ->withErrors(['course_id' => __('The selected course does not belong to this program.')]);
            }

            $q = QuestionCloMapping::query()
                ->with(['assessmentComponent', 'clo', 'bloom', 'academicSession:id,session_name,academic_year'])
                ->where('program_id', $programId)
                ->where('course_id', $courseId)
                ->orderBy('assessment_component_id')
                ->orderBy('main_question_no')
                ->orderBy('question_part')
                ->orderBy('question_label');

            if ($assessmentComponentId) {
                $q->where('assessment_component_id', $assessmentComponentId);
            }

            if ($academicSessionId) {
                $q->where('academic_session_id', $academicSessionId);
            }

            $rows = $q->get();

            if ($assessmentComponentId) {
                $selectedComponent = AssessmentComponent::query()->whereKey($assessmentComponentId)->first();
                if ($selectedComponent && (int) $selectedComponent->course_id === $courseId) {
                    $componentCap = (float) $selectedComponent->marks;
                    if ($academicSessionId) {
                        $mappedTotal = QuestionCloMapping::sumMarksForComponent($assessmentComponentId, $academicSessionId, null);
                        $remaining = round((float) $componentCap - $mappedTotal, 2);
                    }
                }
            }
        }

        return view('content.question-clo-mappings.matrix', array_merge(
            compact(
                'programs',
                'coursesForMatrix',
                'componentsForMatrix',
                'programId',
                'courseId',
                'assessmentComponentId',
                'academicSessionId',
                'rows',
                'selectedComponent',
                'componentCap',
                'mappedTotal',
                'remaining'
            ),
            [
                'cascadeUrls' => $this->cascadeUrls(),
                'academicSessions' => $this->academicSessionsForForms(),
            ]
        ));
    }
}
