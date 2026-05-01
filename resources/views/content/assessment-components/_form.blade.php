@php
    $c = $component ?? null;
    $progId = old('program_id', $c->program_id ?? '');
    $courseId = old('course_id', $c->course_id ?? '');
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3 ac-course-grid"
    data-url-courses="{{ $cascadeUrls['courses'] }}">
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="ac_program_id">{{ __('Program') }} <span class="text-danger">*</span></label>
        <select name="program_id" id="ac_program_id" class="form-select" required data-ac-program>
            <option value="">{{ __('Select program') }}</option>
            @foreach ($programs as $p)
                <option value="{{ $p->id }}" @selected((string) $progId === (string) $p->id)>
                    {{ $p->program_code }} — {{ $p->program_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="ac_course_id">{{ __('Course') }} <span class="text-danger">*</span></label>
        <select name="course_id" id="ac_course_id" class="form-select" required data-ac-course
            data-ph-need-program="{{ __('Select program first') }}"
            data-ph-loading="{{ __('Loading…') }}"
            data-ph-select="{{ __('Select course') }}"
            data-ph-error="{{ __('Could not load courses') }}">
            @forelse ($courses ?? [] as $cr)
                <option value="{{ $cr->id }}" @selected((string) $courseId === (string) $cr->id)>
                    {{ $cr->course_code }} — {{ $cr->course_title }}
                </option>
            @empty
                <option value="">{{ __('Select program first') }}</option>
            @endforelse
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="ac_component_name">{{ __('Component name') }} <span class="text-danger">*</span></label>
        <input type="text" name="component_name" id="ac_component_name" class="form-control" maxlength="150" required
            value="{{ old('component_name', $c->component_name ?? '') }}"
            placeholder="{{ __('e.g. Midterm exam') }}">
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="ac_component_type">{{ __('Component type') }} <span class="text-danger">*</span></label>
        <select name="component_type" id="ac_component_type" class="form-select" required>
            <option value="">{{ __('Select type') }}</option>
            @foreach ($componentTypes as $typeKey => $typeLabel)
                <option value="{{ $typeKey }}" @selected(old('component_type', $c->component_type ?? '') === $typeKey)>{{ $typeLabel }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 col-lg-3">
        <label class="form-label" for="ac_marks">{{ __('Marks') }} <span class="text-danger">*</span></label>
        <input type="number" name="marks" id="ac_marks" class="form-control" required min="0" max="100" step="0.01"
            value="{{ old('marks', $c->marks ?? '') }}">
        <div class="form-text">{{ __('Maximum 100 total marks for active components per course.') }}</div>
    </div>
    <div class="col-md-4 col-lg-3">
        <label class="form-label" for="ac_weight">{{ __('Weight (%)') }}</label>
        <input type="number" name="weight_percentage" id="ac_weight" class="form-control" min="0" max="100" step="0.01"
            value="{{ old('weight_percentage', $c->weight_percentage ?? '') }}">
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="ac_status_id">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="status_id" id="ac_status_id" class="form-select" required>
            <option value="">{{ __('Select status') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected((string) old('status_id', $c->status_id ?? '') === (string) $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="ac_remarks">{{ __('Remarks') }}</label>
        <textarea name="remarks" id="ac_remarks" class="form-control" rows="2">{{ old('remarks', $c->remarks ?? '') }}</textarea>
    </div>
</div>
