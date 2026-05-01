<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\StoreVisionRequest;
use App\Http\Requests\UpdateVisionRequest;
use App\Models\Department;
use App\Models\University;
use App\Models\Vision;
use App\Models\RelatedTo;
use App\Support\ObeStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisionController extends Controller
{
    use RespondsWithJsonForAjax;

    protected function filteredQuery(Request $request)
    {
        $query = Vision::query()->with([
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
        $visions = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $universities = University::query()->orderBy('name')->get(['id', 'name']);
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $statuses = ObeStatus::forDropdown();

        return view('content.visions.index', compact('visions', 'universities', 'departments', 'statuses'));
    }

    public function create(): View
    {
        return view('content.visions.create', $this->formLookups());
    }

    protected function formLookups(): array
    {
        return [
            'universities' => University::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => ObeStatus::forDropdown(),
        ];
    }

    public function store(StoreVisionRequest $request): JsonResponse|RedirectResponse
    {
        Vision::create($request->validated());

        return $this->respondSaved($request, __('Vision saved successfully.'), 'visions.index');
    }

    public function show(Vision $vision): View
    {
        $vision->load(['university', 'department', 'status']);

        return view('content.visions.show', compact('vision'));
    }

    public function edit(Vision $vision): View
    {
        return view('content.visions.edit', array_merge($this->formLookups(), compact('vision')));
    }

    public function update(UpdateVisionRequest $request, Vision $vision): JsonResponse|RedirectResponse
    {
        $vision->update($request->validated());

        return $this->respondSaved($request, __('Vision updated successfully.'), 'visions.index');
    }

    public function destroy(Request $request, Vision $vision): JsonResponse|RedirectResponse
    {
        $vision->delete();

        return $this->respondDeleted($request, __('Vision removed successfully.'), 'visions.index');
    }
}
