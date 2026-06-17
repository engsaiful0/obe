<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Models\BacCriterion;
use App\Models\BacEvidenceRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BacEvidenceRequirementController extends Controller
{
    use RespondsWithJsonForAjax;

    public function index(Request $request): View
    {
        $query = BacEvidenceRequirement::query()
            ->with('criterion.standard');

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('evidence_type', 'like', $term);
            });
        }

        if ($request->filled('bac_criterion_id')) {
            $query->where('bac_criterion_id', (int) $request->input('bac_criterion_id'));
        }

        if ($request->filled('is_required')) {
            $query->where('is_required', (bool) (int) $request->input('is_required'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) (int) $request->input('is_active'));
        }

        $requirements = $query
            ->join('bac_criteria', 'bac_criteria.id', '=', 'bac_evidence_requirements.bac_criterion_id')
            ->join('bac_standards', 'bac_standards.id', '=', 'bac_criteria.bac_standard_id')
            ->orderByRaw('bac_standards.sort_order IS NULL')
            ->orderBy('bac_standards.sort_order')
            ->orderBy('bac_standards.standard_no')
            ->orderByRaw('bac_criteria.sort_order IS NULL')
            ->orderBy('bac_criteria.sort_order')
            ->orderBy('bac_criteria.criterion_no')
            ->orderByRaw('bac_evidence_requirements.sort_order IS NULL')
            ->orderBy('bac_evidence_requirements.sort_order')
            ->orderBy('bac_evidence_requirements.title')
            ->select('bac_evidence_requirements.*')
            ->withCount('evidenceLinks')
            ->paginate(15)
            ->withQueryString();

        return view('content.bac.evidence-requirements.index', [
            'requirements' => $requirements,
            'criteria' => $this->criteriaForLookup(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('content.bac.evidence-requirements.create', [
            'requirement' => null,
            'criteria' => $this->criteriaForLookup(),
            'selectedCriterionId' => $request->integer('bac_criterion_id') ?: null,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        BacEvidenceRequirement::create($this->validatedData($request));

        return $this->respondSaved($request, __('BAC evidence requirement saved successfully.'), 'bac-evidence-requirements.index');
    }

    public function edit(BacEvidenceRequirement $bac_evidence_requirement): View
    {
        return view('content.bac.evidence-requirements.edit', [
            'requirement' => $bac_evidence_requirement,
            'criteria' => $this->criteriaForLookup(),
            'selectedCriterionId' => null,
        ]);
    }

    public function update(Request $request, BacEvidenceRequirement $bac_evidence_requirement): JsonResponse|RedirectResponse
    {
        $bac_evidence_requirement->update($this->validatedData($request));

        return $this->respondSaved($request, __('BAC evidence requirement updated successfully.'), 'bac-evidence-requirements.index');
    }

    public function destroy(Request $request, BacEvidenceRequirement $bac_evidence_requirement): JsonResponse|RedirectResponse
    {
        if ($bac_evidence_requirement->evidenceLinks()->exists()) {
            $bac_evidence_requirement->update(['is_active' => false]);

            return $this->respondDeleted($request, __('BAC evidence requirement has evidence links, so it was deactivated.'), 'bac-evidence-requirements.index');
        }

        $bac_evidence_requirement->delete();

        return $this->respondDeleted($request, __('BAC evidence requirement deleted successfully.'), 'bac-evidence-requirements.index');
    }

    protected function criteriaForLookup()
    {
        return BacCriterion::query()
            ->with('standard:id,standard_no,title')
            ->join('bac_standards', 'bac_standards.id', '=', 'bac_criteria.bac_standard_id')
            ->orderByRaw('bac_standards.sort_order IS NULL')
            ->orderBy('bac_standards.sort_order')
            ->orderBy('bac_standards.standard_no')
            ->orderByRaw('bac_criteria.sort_order IS NULL')
            ->orderBy('bac_criteria.sort_order')
            ->orderBy('bac_criteria.criterion_no')
            ->select('bac_criteria.*')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'bac_criterion_id' => ['required', 'integer', Rule::exists('bac_criteria', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'evidence_type' => ['nullable', 'string', 'max:255'],
            'is_required' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
