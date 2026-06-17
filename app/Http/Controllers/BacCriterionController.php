<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Models\BacCriterion;
use App\Models\BacStandard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BacCriterionController extends Controller
{
    use RespondsWithJsonForAjax;

    public function index(Request $request): View
    {
        $query = BacCriterion::query()
            ->with('standard:id,standard_no,title');

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('criterion_no', 'like', $term)
                    ->orWhere('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('required_evidence', 'like', $term);
            });
        }

        if ($request->filled('bac_standard_id')) {
            $query->where('bac_standard_id', (int) $request->input('bac_standard_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) (int) $request->input('is_active'));
        }

        $criteria = $query
            ->join('bac_standards', 'bac_standards.id', '=', 'bac_criteria.bac_standard_id')
            ->orderByRaw('bac_standards.sort_order IS NULL')
            ->orderBy('bac_standards.sort_order')
            ->orderBy('bac_standards.standard_no')
            ->orderByRaw('bac_criteria.sort_order IS NULL')
            ->orderBy('bac_criteria.sort_order')
            ->orderBy('bac_criteria.criterion_no')
            ->select('bac_criteria.*')
            ->withCount(['evidenceRequirements', 'evidenceLinks'])
            ->paginate(15)
            ->withQueryString();

        return view('content.bac.criteria.index', [
            'criteria' => $criteria,
            'standards' => $this->standardsForLookup(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('content.bac.criteria.create', [
            'criterion' => null,
            'standards' => $this->standardsForLookup(),
            'selectedStandardId' => $request->integer('bac_standard_id') ?: null,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        BacCriterion::create($this->validatedData($request));

        return $this->respondSaved($request, __('BAC criterion saved successfully.'), 'bac-criteria.index');
    }

    public function edit(BacCriterion $bac_criterion): View
    {
        return view('content.bac.criteria.edit', [
            'criterion' => $bac_criterion,
            'standards' => $this->standardsForLookup(),
            'selectedStandardId' => null,
        ]);
    }

    public function update(Request $request, BacCriterion $bac_criterion): JsonResponse|RedirectResponse
    {
        $bac_criterion->update($this->validatedData($request, $bac_criterion));

        return $this->respondSaved($request, __('BAC criterion updated successfully.'), 'bac-criteria.index');
    }

    public function destroy(Request $request, BacCriterion $bac_criterion): JsonResponse|RedirectResponse
    {
        $hasLinks = $bac_criterion->evidenceRequirements()->exists()
            || $bac_criterion->evidenceLinks()->exists()
            || $bac_criterion->complianceStatuses()->exists();

        if ($hasLinks) {
            $bac_criterion->update(['is_active' => false]);

            return $this->respondDeleted($request, __('BAC criterion has linked records, so it was deactivated.'), 'bac-criteria.index');
        }

        $bac_criterion->delete();

        return $this->respondDeleted($request, __('BAC criterion deleted successfully.'), 'bac-criteria.index');
    }

    protected function standardsForLookup()
    {
        return BacStandard::query()
            ->orderByRaw('sort_order IS NULL')
            ->orderBy('sort_order')
            ->orderBy('standard_no')
            ->get(['id', 'standard_no', 'title', 'is_active']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request, ?BacCriterion $criterion = null): array
    {
        return $request->validate([
            'bac_standard_id' => ['required', 'integer', Rule::exists('bac_standards', 'id')],
            'criterion_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bac_criteria', 'criterion_no')
                    ->where(fn ($query) => $query->where('bac_standard_id', (int) $request->input('bac_standard_id')))
                    ->ignore($criterion?->id),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'required_evidence' => ['nullable', 'string'],
            'responsible_role' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
