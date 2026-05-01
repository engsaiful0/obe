<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePeoRequest;
use App\Http\Requests\UpdatePeoRequest;
use App\Models\Department;
use App\Models\Peo;
use App\Models\Program;
use App\Support\ObeStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Peo::query()->with([
            'program:id,program_name,program_code,department_id',
            'program.department:id,name',
            'status:id,status_name',
        ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->where('peo_code', 'like', $term)
                    ->orWhere('peo_title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', (int) $request->input('program_id'));
        }

        if ($request->filled('department_id')) {
            $query->whereHas('program', fn ($q) => $q->where('department_id', (int) $request->input('department_id')));
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', (int) $request->input('status_id'));
        }

        $peos = $query->latest('id')->paginate(15)->withQueryString();
        $programs = Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code', 'department_id']);
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $statuses = ObeStatus::forDropdown();

        return view('content.peos.index', compact('peos', 'programs', 'departments', 'statuses'));
    }

    public function create(): View
    {
        return view('content.peos.create', $this->formLookups());
    }

    protected function formLookups(): array
    {
        return [
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code', 'department_id']),
            'statuses' => ObeStatus::forDropdown(),
        ];
    }

    public function store(StorePeoRequest $request): RedirectResponse
    {
        Peo::create($request->validated());

        return redirect()->route('peos.index')->with('success', __('PEO saved successfully.'));
    }

    public function show(Peo $peo): View
    {
        $peo->load(['program.department', 'status']);

        return view('content.peos.show', compact('peo'));
    }

    public function edit(Peo $peo): View
    {
        return view('content.peos.edit', array_merge($this->formLookups(), compact('peo')));
    }

    public function update(UpdatePeoRequest $request, Peo $peo): RedirectResponse
    {
        $peo->update($request->validated());

        return redirect()->route('peos.index')->with('success', __('PEO updated successfully.'));
    }

    public function destroy(Peo $peo): RedirectResponse
    {
        $peo->delete();

        return redirect()->route('peos.index')->with('success', __('PEO removed successfully.'));
    }
}
