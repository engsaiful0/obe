@php
    $c = $clo ?? null;
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

<div class="row g-3">
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="clo_program_id">{{ __('Program') }} <span class="text-danger">*</span></label>
        <select name="program_id" id="clo_program_id" class="form-select" required data-clo-program-select>
            <option value="">{{ __('Select program') }}</option>
            @foreach ($programs as $p)
                <option value="{{ $p->id }}" @selected((string) $progId === (string) $p->id)>
                    {{ $p->program_code }} — {{ $p->program_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="clo_course_id">{{ __('Course') }} <span class="text-danger">*</span></label>
        <select name="course_id" id="clo_course_id" class="form-select" required data-clo-course-select
            data-initial-course-id="{{ $courseId }}"
            data-placeholder-need-program="{{ __('Select program first') }}"
            data-placeholder-loading="{{ __('Loading courses…') }}"
            data-placeholder-error="{{ __('Could not load courses') }}"
            data-placeholder-empty="{{ __('Select course') }}">
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
        <label class="form-label" for="clo_bloom_id">{{ __("Bloom level") }} <span class="text-danger">*</span></label>
        <select name="bloom_id" id="clo_bloom_id" class="form-select" required>
            <option value="">{{ __('Select Bloom level') }}</option>
            @foreach ($blooms as $bl)
                <option value="{{ $bl->id }}" @selected((string) old('bloom_id', $c->bloom_id ?? '') === (string) $bl->id)>
                    {{ $bl->level_order }}. {{ $bl->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="clo_code">{{ __('CLO code') }} <span class="text-danger">*</span></label>
        <input type="text" name="clo_code" id="clo_code" class="form-control" maxlength="50" required
            value="{{ old('clo_code', $c->clo_code ?? '') }}" placeholder="{{ __('e.g. CLO1') }}">
    </div>
    <div class="col-md-6 col-lg-8">
        <label class="form-label" for="clo_title">{{ __('Title') }}</label>
        <input type="text" name="title" id="clo_title" class="form-control" maxlength="255"
            value="{{ old('title', $c->title ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label" for="clo_description">{{ __('Description') }} <span class="text-danger">*</span></label>
        <textarea name="description" id="clo_description" class="form-control" rows="5" required>{{ old('description', $c->description ?? '') }}</textarea>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="clo_status_id">{{ __('Status (OBE)') }} <span class="text-danger">*</span></label>
        <select name="status_id" id="clo_status_id" class="form-select" required>
            <option value="">{{ __('Select status') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected((string) old('status_id', $c->status_id ?? '') === (string) $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
