@extends('layouts/layoutMaster')

@section('title', __('Question–CLO matrix'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('Question mapping report') }}</h5>
        <a href="{{ route('question-clo-mappings.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('List view') }}</a>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('question-clo-mappings.matrix') }}" id="qcm-matrix-form"
            data-url-courses="{{ $cascadeUrls['courses'] }}"
            data-url-components="{{ $cascadeUrls['assessmentComponents'] }}">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label small mb-0" for="mx_program">{{ __('Program') }}</label>
                    <select name="program_id" id="mx_program" class="form-select" data-mx-program>
                        <option value="">{{ __('Select program') }}</option>
                        @foreach ($programs as $p)
                            <option value="{{ $p->id }}" @selected((int) $programId === (int) $p->id)>{{ $p->program_code }} — {{ $p->program_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-0" for="mx_course">{{ __('Course') }}</label>
                    <select name="course_id" id="mx_course" class="form-select" required data-mx-course
                        data-ph-need-program="{{ __('Select program first') }}"
                        data-ph-loading="{{ __('Loading…') }}"
                        data-ph-select="{{ __('Select course') }}"
                        data-ph-error="{{ __('Could not load courses') }}">
                        <option value="">{{ __('Select program first') }}</option>
                        @foreach ($coursesForMatrix as $cr)
                            <option value="{{ $cr->id }}" @selected((int) $courseId === (int) $cr->id)>{{ $cr->course_code }} — {{ $cr->course_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-0" for="mx_component">{{ __('Assessment component') }}</label>
                    <select name="assessment_component_id" id="mx_component" class="form-select" data-mx-component
                        data-ph-need-course="{{ __('Select course first') }}"
                        data-ph-loading="{{ __('Loading…') }}"
                        data-ph-select="{{ __('All components') }}">
                        <option value="">{{ __('All components') }}</option>
                        @foreach ($componentsForMatrix as $ac)
                            <option value="{{ $ac->id }}" @selected((int) $assessmentComponentId === (int) $ac->id)>{{ $ac->component_name }} ({{ $ac->marks }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                </div>
            </div>
        </form>

        @if ($programId && $courseId && $rows->isNotEmpty())
            @if ($selectedComponent && $componentCap !== null)
                <div class="alert alert-info small py-2">
                    {{ __('Component cap') }}: <strong>{{ $componentCap }}</strong> |
                    {{ __('Mapped total') }}: <strong>{{ rtrim(rtrim(number_format((float) $mappedTotal, 2, '.', ''), '0'), '.') }}</strong> |
                    {{ __('Remaining') }}: <strong>{{ rtrim(rtrim(number_format((float) $remaining, 2, '.', ''), '0'), '.') }}</strong>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Main Q no') }}</th>
                            <th>{{ __('Part') }}</th>
                            <th>{{ __('Question label') }}</th>
                            <th>{{ __('Component') }}</th>
                            <th>{{ __('Marks') }}</th>
                            <th>{{ __('CLO') }}</th>
                            <th>{{ __("Bloom") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td>{{ $r->main_question_no ?? '—' }}</td>
                                <td>{{ $r->question_part ?? '—' }}</td>
                                <td class="fw-medium">{{ $r->question_label }}</td>
                                <td>{{ $r->assessmentComponent->component_name ?? '—' }}</td>
                                <td>{{ $r->marks }}</td>
                                <td>
                                    {{ $r->clo->clo_code ?? '—' }}
                                    @if ($r->clo && $r->clo->title)
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($r->clo->title, 60) }}</div>
                                    @endif
                                </td>
                                <td>{{ $r->bloom->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($programId && $courseId)
            <p class="text-muted mb-0">{{ __('No question mappings match the filters.') }}</p>
        @else
            <p class="text-muted mb-0">{{ __('Select program and course, optionally a component, then apply.') }}</p>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/question-clo-matrix.js') }}"></script>
@endsection
