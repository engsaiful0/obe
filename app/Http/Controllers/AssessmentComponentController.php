<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonForAjax;
use App\Http\Requests\StoreAssessmentComponentRequest;
use App\Http\Requests\UpdateAssessmentComponentRequest;
use App\Models\AssessmentComponent;
use App\Models\Course;
use App\Models\Program;
use App\Models\RelatedTo;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentComponentController extends Controller
{
    use RespondsWithJsonForAjax;

    protected function cascadeUrlPatterns(): array
    {
        return [
            'courses' => url('/ajax/assessment-components/program/__PROGRAM_ID__/courses'),
        ];
    }

    protected function obeStatuses(): \Illuminate\Support\Collection
    {
        return Status::query()
            ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);
    }

    protected function filteredQuery(Request $request)
    {
        $query = AssessmentComponent::query()->with([
            'program:id,program_name,program_code',
            'course:id,course_code,course_title,program_id',
            'status:id,status_name',
        ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->where('assessment_components.component_name', 'like', $term)
                    ->orWhere('assessment_components.component_type', 'like', $term)
                    ->orWhereHas('course', function ($cq) use ($term) {
                        $cq->where('course_code', 'like', $term)
                            ->orWhere('course_title', 'like', $term);
                    });
            });
        }

        foreach (['program_id', 'course_id', 'component_type', 'status_id'] as $field) {
            if ($request->filled($field)) {
                $value = $field === 'component_type'
                    ? $request->input($field)
                    : (int) $request->input($field);
                $query->where('assessment_components.'.$field, $value);
            }
        }

        return $query->latest('assessment_components.id');
    }

    public function coursesByProgram(Program $program): JsonResponse
    {
        $items = Course::query()
            ->where('program_id', $program->getKey())
            ->where('status', 'Active')
            ->orderBy('course_code')
            ->get(['id', 'course_code', 'course_title']);

        if ($items->isEmpty()) {
            $items = Course::query()
                ->where('program_id', $program->getKey())
                ->orderBy('course_code')
                ->get(['id', 'course_code', 'course_title']);
        }

        return response()->json($items->map(fn (Course $c) => [
            'id' => $c->id,
            'course_code' => $c->course_code,
            'course_title' => $c->course_title,
        ]));
    }

    public function index(Request $request): View
    {
        $components = $this->filteredQuery($request)->paginate(15)->withQueryString();

        $totalActiveMarksForCourse = null;
        if ($request->filled('course_id')) {
            $totalActiveMarksForCourse = AssessmentComponent::sumActiveMarksForCourse(
                (int) $request->input('course_id'),
                null
            );
        }

        return view('content.assessment-components.index', [
            'components' => $components,
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => Course::query()->orderBy('course_code')->get(['id', 'course_code', 'course_title', 'program_id']),
            'obeStatuses' => $this->obeStatuses(),
            'componentTypes' => AssessmentComponent::componentTypeOptions(),
            'totalActiveMarksForCourse' => $totalActiveMarksForCourse,
        ]);
    }

    protected function formLookups(?AssessmentComponent $component): array
    {
        $programId = $component?->program_id ?? (old('program_id') !== null && old('program_id') !== ''
            ? (int) old('program_id')
            : null);
        $courses = collect();
        if ($programId) {
            $q = Course::query()->where('program_id', $programId)->orderBy('course_code');
            $active = (clone $q)->where('status', 'Active')->get();
            $courses = $active->isNotEmpty() ? $active : $q->get();
        }

        return array_merge([
            'programs' => Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']),
            'courses' => $courses,
            'statuses' => $this->obeStatuses(),
            'componentTypes' => AssessmentComponent::componentTypeOptions(),
            'cascadeUrls' => $this->cascadeUrlPatterns(),
        ], $component ? ['component' => $component] : []);
    }

    public function create(): View
    {
        return view('content.assessment-components.create', $this->formLookups(null));
    }

    public function store(StoreAssessmentComponentRequest $request): JsonResponse|RedirectResponse
    {
        AssessmentComponent::create($request->validated());

        return $this->respondSaved($request, __('Assessment component saved successfully.'), 'assessment-components.index');
    }

    public function show(AssessmentComponent $assessment_component): View
    {
        $assessment_component->load(['program', 'course', 'status']);

        return view('content.assessment-components.show', ['component' => $assessment_component]);
    }

    public function edit(AssessmentComponent $assessment_component): View
    {
        return view('content.assessment-components.edit', $this->formLookups($assessment_component));
    }

    public function update(UpdateAssessmentComponentRequest $request, AssessmentComponent $assessment_component): JsonResponse|RedirectResponse
    {
        $assessment_component->update($request->validated());

        return $this->respondSaved($request, __('Assessment component updated successfully.'), 'assessment-components.index');
    }

    public function destroy(Request $request, AssessmentComponent $assessment_component): JsonResponse|RedirectResponse
    {
        $assessment_component->delete();

        return $this->respondDeleted($request, __('Assessment component deleted successfully.'), 'assessment-components.index');
    }
}
