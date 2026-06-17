<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Models\BacStandard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BacStandardController extends Controller
{
    use RespondsWithJsonForAjax;

    public function index(Request $request): View
    {
        $query = BacStandard::query()->withCount('criteria');

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('standard_no', 'like', $term)
                    ->orWhere('title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) (int) $request->input('is_active'));
        }

        $standards = $query
            ->orderByRaw('sort_order IS NULL')
            ->orderBy('sort_order')
            ->orderBy('standard_no')
            ->paginate(15)
            ->withQueryString();

        return view('content.bac.standards.index', compact('standards'));
    }

    public function create(): View
    {
        return view('content.bac.standards.create', ['standard' => null]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        BacStandard::create($this->validatedData($request));

        return $this->respondSaved($request, __('BAC standard saved successfully.'), 'bac-standards.index');
    }

    public function edit(BacStandard $bac_standard): View
    {
        return view('content.bac.standards.edit', ['standard' => $bac_standard]);
    }

    public function update(Request $request, BacStandard $bac_standard): JsonResponse|RedirectResponse
    {
        $bac_standard->update($this->validatedData($request, $bac_standard));

        return $this->respondSaved($request, __('BAC standard updated successfully.'), 'bac-standards.index');
    }

    public function destroy(Request $request, BacStandard $bac_standard): JsonResponse|RedirectResponse
    {
        if ($bac_standard->criteria()->exists()) {
            $bac_standard->update(['is_active' => false]);

            return $this->respondDeleted($request, __('BAC standard has linked criteria, so it was deactivated.'), 'bac-standards.index');
        }

        $bac_standard->delete();

        return $this->respondDeleted($request, __('BAC standard deleted successfully.'), 'bac-standards.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request, ?BacStandard $standard = null): array
    {
        return $request->validate([
            'standard_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bac_standards', 'standard_no')->ignore($standard?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
