<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\StoreProgramOutcomeRequest;
use App\Http\Requests\UpdateProgramOutcomeRequest;
use App\Models\Program;
use App\Models\ProgramOutcome;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramOutcomeController extends Controller
{
    use RespondsWithJsonForAjax;

    protected function filteredQuery(Request $request)
    {
        $query = ProgramOutcome::query()->with([
            'program:id,program_name,program_code,department_id',
            'program.department:id,name',
        ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->where('outcome_code', 'like', $term)
                    ->orWhere('title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', (int) $request->input('program_id'));
        }

        if ($request->filled('outcome_type')) {
            $query->where('outcome_type', $request->input('outcome_type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query->latest('id');
    }

    public function index(Request $request): View
    {
        $outcomes = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $programs = Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code', 'department_id']);

        return view('content.program-outcomes.index', compact('outcomes', 'programs'));
    }

    public function create(): View
    {
        return view('content.program-outcomes.create', $this->formLookups());
    }

    protected function formLookups(): array
    {
        return [
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code', 'department_id']),
        ];
    }

    public function store(StoreProgramOutcomeRequest $request): JsonResponse|RedirectResponse
    {
        ProgramOutcome::create($request->validated());

        return $this->respondSaved($request, __('Program outcome saved successfully.'), 'program-outcomes.index');
    }

    public function show(ProgramOutcome $program_outcome): View
    {
        $program_outcome->load(['program.department']);

        return view('content.program-outcomes.show', ['programOutcome' => $program_outcome]);
    }

    public function edit(ProgramOutcome $program_outcome): View
    {
        return view('content.program-outcomes.edit', array_merge($this->formLookups(), [
            'programOutcome' => $program_outcome,
        ]));
    }

    public function update(UpdateProgramOutcomeRequest $request, ProgramOutcome $program_outcome): JsonResponse|RedirectResponse
    {
        $program_outcome->update($request->validated());

        return $this->respondSaved($request, __('Program outcome updated successfully.'), 'program-outcomes.index');
    }

    public function destroy(Request $request, ProgramOutcome $program_outcome): JsonResponse|RedirectResponse
    {
        $program_outcome->delete();

        return $this->respondDeleted($request, __('Program outcome deleted successfully.'), 'program-outcomes.index');
    }
}
