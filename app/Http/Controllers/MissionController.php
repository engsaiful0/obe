<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMissionRequest;
use App\Http\Requests\UpdateMissionRequest;
use App\Models\Department;
use App\Models\Mission;
use App\Models\University;
use App\Support\ObeStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MissionController extends Controller
{
    protected function filteredQuery(Request $request)
    {
        $query = Mission::query()->with([
            'university:id,name',
            'department:id,name',
            'status:id,status_name',
        ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('university_id')) {
            $query->where('university_id', (int) $request->input('university_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->input('department_id'));
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', (int) $request->input('status_id'));
        }

        return $query->latest('id');
    }

    public function index(Request $request): View
    {
        $missions = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $universities = University::query()->orderBy('name')->get(['id', 'name']);
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $statuses = ObeStatus::forDropdown();

        return view('content.missions.index', compact('missions', 'universities', 'departments', 'statuses'));
    }

    public function create(): View
    {
        return view('content.missions.create', $this->formLookups());
    }

    protected function formLookups(): array
    {
        return [
            'universities' => University::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => ObeStatus::forDropdown(),
        ];
    }

    public function store(StoreMissionRequest $request): RedirectResponse
    {
        Mission::create($request->validated());

        return redirect()->route('missions.index')->with('success', __('Mission saved successfully.'));
    }

    public function show(Mission $mission): View
    {
        $mission->load(['university', 'department', 'status']);

        return view('content.missions.show', compact('mission'));
    }

    public function edit(Mission $mission): View
    {
        return view('content.missions.edit', array_merge($this->formLookups(), compact('mission')));
    }

    public function update(UpdateMissionRequest $request, Mission $mission): RedirectResponse
    {
        $mission->update($request->validated());

        return redirect()->route('missions.index')->with('success', __('Mission updated successfully.'));
    }

    public function destroy(Mission $mission): RedirectResponse
    {
        $mission->delete();

        return redirect()->route('missions.index')->with('success', __('Mission removed successfully.'));
    }
}
