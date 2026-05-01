<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\StoreMissionRequest;
use App\Http\Requests\UpdateMissionRequest;
use App\Models\Department;
use App\Models\Mission;
use App\Models\University;
use App\Support\ObeStatus;
use App\Models\RelatedTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MissionController extends Controller
{
    use RespondsWithJsonForAjax;

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
        $obeStatuses = Status::query()
        ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
        ->orderBy('status_name')
        ->get(['id', 'status_name']);

        return view('content.missions.create', [
            'obeStatuses' => $obeStatuses,
        ]);
    }

    protected function formLookups(): array
    {
        return [
            'universities' => University::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => ObeStatus::forDropdown(),
        ];
    }

    public function store(StoreMissionRequest $request): JsonResponse|RedirectResponse
    {
        Mission::create($request->validated());

        return $this->respondSaved($request, __('Mission saved successfully.'), 'missions.index');
    }

    public function show(Mission $mission): View
    {
        $mission->load(['university', 'department', 'status']);

        return view('content.missions.show', compact('mission'));
    }

    public function edit(Mission $mission): View
    {
        $obeStatuses = Status::query()
        ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
        ->orderBy('status_name')
        ->get(['id', 'status_name']);
        return view('content.missions.edit', [
            'obeStatuses' => $obeStatuses,
            'mission' => $mission,
        ]);
    }

    public function update(UpdateMissionRequest $request, Mission $mission): JsonResponse|RedirectResponse
    {
        $mission->update($request->validated());

        return $this->respondSaved($request, __('Mission updated successfully.'), 'missions.index');
    }

    public function destroy(Request $request, Mission $mission): JsonResponse|RedirectResponse
    {
        $mission->delete();

        return $this->respondDeleted($request, __('Mission removed successfully.'), 'missions.index');
    }
}
