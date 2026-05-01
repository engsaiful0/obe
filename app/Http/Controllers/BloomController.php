<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\StoreBloomRequest;
use App\Http\Requests\UpdateBloomRequest;
use App\Models\Bloom;
use App\Models\Status;
use App\Models\RelatedTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BloomController extends Controller
{
    use RespondsWithJsonForAjax;

    protected function filteredQuery(Request $request)
    {
        $query = Bloom::query()->with(['status:id,status_name']);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', (int) $request->input('status_id'));
        }

        return $query->orderBy('level_order');
    }

    public function index(Request $request): View
    {
        $blooms = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $obeStatuses = Status::query()
        ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
        ->orderBy('status_name')
        ->get(['id', 'status_name']);

        return view('content.blooms.index', [
            'blooms' => $blooms,
            'statuses' => $obeStatuses,
        ]);
    }

    public function create(): View
    {
        $obeStatuses = Status::query()
        ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
        ->orderBy('status_name')
        ->get(['id', 'status_name']);

        return view('content.blooms.create', [
            'statuses' => $obeStatuses,
        ]);
    }

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

    protected function formLookups(): array
    {
        return [
            'statuses' => $this->statusesForForms(),
        ];
    }

    public function store(StoreBloomRequest $request): JsonResponse|RedirectResponse
    {
        Bloom::create($request->validated());

        return $this->respondSaved($request, __('Bloom saved successfully.'), 'blooms.index');
    }

    public function show(Bloom $bloom): View
    {
        $bloom->load(['status']);

        return view('content.blooms.show', compact('bloom'));
    }

    public function edit(Bloom $bloom): View
    {
        $obeStatuses = Status::query()
        ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
        ->orderBy('status_name')
        ->get(['id', 'status_name']);
        return view('content.blooms.edit', [
            'statuses' => $obeStatuses,
            'bloom' => $bloom,
        ]);
    }

    public function update(UpdateBloomRequest $request, Bloom $bloom): JsonResponse|RedirectResponse
    {
        $bloom->update($request->validated());

        return $this->respondSaved($request, __('Bloom updated successfully.'), 'blooms.index');
    }

    public function destroy(Request $request, Bloom $bloom): JsonResponse|RedirectResponse
    {
        $bloom->delete();

        return $this->respondDeleted($request, __('Bloom deleted successfully.'), 'blooms.index');
    }
}
