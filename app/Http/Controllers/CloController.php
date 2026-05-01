<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\StoreCloRequest;
use App\Http\Requests\UpdateCloRequest;
use App\Models\Bloom;
use App\Models\Course;
use App\Models\Clo;
use App\Models\Program;
use App\Models\Status;
use App\Models\RelatedTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CloController extends Controller
{
    use RespondsWithJsonForAjax;

    protected function statusesForForms(): \Illuminate\Support\Collection
    {
        $obe = Status::query()
            ->whereHas('relatedTo', fn ($r) => $r->where('name', 'OBE'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);

        return $obe->isNotEmpty()
            ? $obe
            : Status::query()->orderBy('status_name')->get(['id', 'status_name']);
    }

    protected function bloomsForForms(): \Illuminate\Support\Collection
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

    protected function filteredQuery(Request $request)
    {
        $query = Clo::query()->with([
            'program:id,program_name,program_code',
            'course:id,course_code,course_title,program_id',
            'bloom:id,name,level_order',
            'status:id,status_name',
        ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->where('clos.clo_code', 'like', $term)
                    ->orWhere('clos.title', 'like', $term)
                    ->orWhere('clos.description', 'like', $term)
                    ->orWhereHas('course', function ($cq) use ($term) {
                        $cq->where('course_code', 'like', $term)
                            ->orWhere('course_title', 'like', $term);
                    });
            });
        }

        foreach (['program_id', 'course_id', 'bloom_id', 'status_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, (int) $request->input($field));
            }
        }

        return $query->latest('clos.id');
    }

    public function index(Request $request): View
    {
        $clos = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('content.clos.index', [
            'clos' => $clos,
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => Course::query()->orderBy('course_code')->get(['id', 'course_code', 'course_title', 'program_id']),
            'blooms' => $this->bloomsForForms(),
            'filterStatuses' => $this->statusesForForms(),
        ]);
    }

    public function create(): View
    {
        return view('content.clos.create', $this->formLookups(null));
    }

    protected function formLookups(?Clo $clo): array
    {
        $programId = $clo?->program_id
            ?? (old('program_id') !== null && old('program_id') !== '' ? (int) old('program_id') : null);
        $courses = $programId
            ? Course::query()->where('program_id', $programId)->orderBy('course_code')->get()
            : collect();

        return [
            'clo' => $clo,
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => $courses,
            'blooms' => $this->bloomsForForms(),
            'statuses' => $this->statusesForForms(),
            'coursesAjaxUrlPattern' => str_replace('/0/', '/__PROGRAM_ID__/', route('ajax.clo.program.courses', ['program' => 0])),
        ];
    }

    public function coursesByProgram(Program $program): JsonResponse
    {
        $items = Course::query()
            ->where('program_id', $program->id)
            ->orderBy('course_code')
            ->get(['id', 'course_code', 'course_title']);

        return response()->json($items->map(fn (Course $c) => [
            'id' => $c->id,
            'course_code' => $c->course_code,
            'course_title' => $c->course_title,
        ]));
    }

    public function store(StoreCloRequest $request): JsonResponse|RedirectResponse
    {
        Clo::create($request->validated());

        return $this->respondSaved($request, __('CLO saved successfully.'), 'clos.index');
    }

    public function show(Clo $clo): View
    {
        $clo->load(['program', 'course', 'bloom', 'status']);

        return view('content.clos.show', compact('clo'));
    }

    public function edit(Clo $clo): View
    {
        return view('content.clos.edit', $this->formLookups($clo));
    }

    public function update(UpdateCloRequest $request, Clo $clo): JsonResponse|RedirectResponse
    {
        $clo->update($request->validated());

        return $this->respondSaved($request, __('CLO updated successfully.'), 'clos.index');
    }

    public function destroy(Request $request, Clo $clo): JsonResponse|RedirectResponse
    {
        $clo->delete();

        return $this->respondDeleted($request, __('CLO deleted successfully.'), 'clos.index');
    }
}
