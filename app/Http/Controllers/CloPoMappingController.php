<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\StoreCloPoMappingRequest;
use App\Http\Requests\UpdateCloPoMappingRequest;
use App\Models\Clo;
use App\Models\CloPoMapping;
use App\Models\Course;
use App\Models\Program;
use App\Models\ProgramOutcome;
use App\Models\RelatedTo;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CloPoMappingController extends Controller
{
    use RespondsWithJsonForAjax;

    /**
     * OBE-linked statuses only (explicit pattern requested).
     */
    protected function obeStatusesForForms(): \Illuminate\Support\Collection
    {
        return Status::query()
            ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);
    }

    protected function cascadeUrlPatterns(): array
    {
        return [
            'courses' => url('/ajax/clo-po/program/__PROGRAM_ID__/courses'),
            'programOutcomes' => url('/ajax/clo-po/program/__PROGRAM_ID__/program-outcomes'),
            'clos' => url('/ajax/clo-po/course/__COURSE_ID__/clos'),
        ];
    }

    protected function filteredQuery(Request $request)
    {
        $query = CloPoMapping::query()->with([
            'program:id,program_name,program_code',
            'course:id,course_code,course_title,program_id',
            'clo:id,clo_code,title,course_id',
            'programOutcome:id,outcome_code,title,outcome_type,program_id',
            'status:id,status_name',
        ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->whereHas('clo', function ($cq) use ($term) {
                    $cq->where('clo_code', 'like', $term)
                        ->orWhere('title', 'like', $term);
                })
                    ->orWhereHas('programOutcome', function ($pq) use ($term) {
                        $pq->where('outcome_code', 'like', $term)
                            ->orWhere('title', 'like', $term);
                    })
                    ->orWhereHas('course', function ($cr) use ($term) {
                        $cr->where('course_code', 'like', $term)
                            ->orWhere('course_title', 'like', $term);
                    });
            });
        }

        foreach (['program_id', 'course_id', 'clo_id', 'program_outcome_id', 'mapping_level', 'status_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, (int) $request->input($field));
            }
        }

        return $query->latest('clo_po_mappings.id');
    }

    protected function coursesQueryForLookup(int $programId)
    {
        $q = Course::query()->where('program_id', $programId)->orderBy('course_code');
        $active = (clone $q)->where('status', 'Active')->get();
        if ($active->isNotEmpty()) {
            return $active;
        }

        return $q->get();
    }

    protected function closQueryForLookup(int $courseId)
    {
        $base = Clo::query()->with('status')->where('course_id', $courseId)->orderBy('clo_code');
        $active = (clone $base)->whereHas('status', fn ($s) => $s->where('status_name', 'Active'))->get();
        if ($active->isNotEmpty()) {
            return $active;
        }

        return $base->get();
    }

    protected function outcomesQueryForLookup(int $programId)
    {
        $base = ProgramOutcome::query()->where('program_id', $programId)->orderBy('outcome_code');
        $active = (clone $base)->where('status', 'Active')->get();
        if ($active->isNotEmpty()) {
            return $active;
        }

        return $base->get();
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

    public function programOutcomesByProgram(Program $program): JsonResponse
    {
        $items = ProgramOutcome::query()
            ->where('program_id', $program->getKey())
            ->where('status', 'Active')
            ->orderBy('outcome_code')
            ->get(['id', 'outcome_code', 'title']);

        if ($items->isEmpty()) {
            $items = ProgramOutcome::query()
                ->where('program_id', $program->getKey())
                ->orderBy('outcome_code')
                ->get(['id', 'outcome_code', 'title']);
        }

        return response()->json($items->map(fn (ProgramOutcome $p) => [
            'id' => $p->id,
            'outcome_code' => $p->outcome_code,
            'title' => $p->title,
        ]));
    }

    public function closByCourse(Course $course): JsonResponse
    {
        $clos = Clo::query()
            ->where('course_id', $course->getKey())
            ->whereHas('status', fn ($s) => $s->where('status_name', 'Active'))
            ->orderBy('clo_code')
            ->get(['id', 'clo_code', 'title']);

        if ($clos->isEmpty()) {
            $clos = Clo::query()
                ->where('course_id', $course->getKey())
                ->orderBy('clo_code')
                ->get(['id', 'clo_code', 'title']);
        }

        return response()->json($clos->map(fn (Clo $c) => [
            'id' => $c->id,
            'clo_code' => $c->clo_code,
            'title' => $c->title,
        ]));
    }

    public function index(Request $request): View
    {
        $mappings = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('content.clo-po-mappings.index', [
            'mappings' => $mappings,
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => Course::query()->orderBy('course_code')->get(['id', 'course_code', 'course_title', 'program_id']),
            'clos' => Clo::query()->orderBy('clo_code')->get(['id', 'clo_code', 'title', 'course_id']),
            'programOutcomes' => ProgramOutcome::query()->orderBy('outcome_code')->get(['id', 'outcome_code', 'title', 'program_id']),
            'obeStatuses' => $this->obeStatusesForForms(),
        ]);
    }

    protected function formLookups(?CloPoMapping $mapping): array
    {
        $programId = $mapping?->program_id ?? (old('program_id') !== null && old('program_id') !== ''
            ? (int) old('program_id')
            : null);
        $courseId = $mapping?->course_id ?? (old('course_id') !== null && old('course_id') !== ''
            ? (int) old('course_id')
            : null);

        return array_merge([
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => $programId ? $this->coursesQueryForLookup($programId) : collect(),
            'programOutcomes' => $programId ? $this->outcomesQueryForLookup($programId) : collect(),
            'clos' => $courseId ? $this->closQueryForLookup($courseId) : collect(),
            'statuses' => $this->obeStatusesForForms(),
            'cascadeUrls' => $this->cascadeUrlPatterns(),
        ], $mapping ? ['mapping' => $mapping] : []);
    }

    public function create(): View
    {
        return view('content.clo-po-mappings.create', $this->formLookups(null));
    }

    public function store(StoreCloPoMappingRequest $request): JsonResponse|RedirectResponse
    {
        CloPoMapping::create($request->validated());

        return $this->respondSaved($request, __('CLO-PO mapping saved successfully.'), 'clo-po-mappings.index');
    }

    public function show(CloPoMapping $clo_po_mapping): View
    {
        $clo_po_mapping->load(['program', 'course', 'clo', 'programOutcome', 'status']);

        return view('content.clo-po-mappings.show', ['mapping' => $clo_po_mapping]);
    }

    public function edit(CloPoMapping $clo_po_mapping): View
    {
        return view('content.clo-po-mappings.edit', $this->formLookups($clo_po_mapping));
    }

    public function update(UpdateCloPoMappingRequest $request, CloPoMapping $clo_po_mapping): JsonResponse|RedirectResponse
    {
        $clo_po_mapping->update($request->validated());

        return $this->respondSaved($request, __('CLO-PO mapping updated successfully.'), 'clo-po-mappings.index');
    }

    public function destroy(Request $request, CloPoMapping $clo_po_mapping): JsonResponse|RedirectResponse
    {
        $clo_po_mapping->delete();

        return $this->respondDeleted($request, __('CLO-PO mapping deleted successfully.'), 'clo-po-mappings.index');
    }

    public function matrix(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $programs = Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']);

        $programId = (int) $request->query('program_id');
        $courseId = (int) $request->query('course_id');

        $clos = collect();
        $outcomes = collect();
        $levels = [];
        $coursesForMatrix = collect();

        if ($programId) {
            $coursesForMatrix = $this->coursesQueryForLookup($programId);
        }

        if ($programId && $courseId && $programs->contains('id', $programId)) {
            $course = Course::query()->whereKey($courseId)->where('program_id', $programId)->first();
            if (! $course) {
                return redirect()->route('clo-po-mappings.matrix', ['program_id' => $programId])
                    ->withErrors(['course_id' => __('The selected course does not belong to this program.')]);
            }

            $clos = $this->closQueryForLookup($courseId);
            $outcomes = $this->outcomesQueryForLookup($programId);

            $rows = CloPoMapping::query()
                ->where('program_id', $programId)
                ->where('course_id', $courseId)
                ->get(['clo_id', 'program_outcome_id', 'mapping_level']);

            foreach ($rows as $row) {
                $levels[$row->clo_id][$row->program_outcome_id] = $row->mapping_level;
            }
        }

        return view('content.clo-po-mappings.matrix', compact(
            'programs',
            'coursesForMatrix',
            'programId',
            'courseId',
            'clos',
            'outcomes',
            'levels'
        ));
    }
}
