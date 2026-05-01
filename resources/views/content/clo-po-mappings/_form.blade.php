@php
    $m = $mapping ?? null;
    $progId = old('program_id', $m->program_id ?? '');
    $courseId = old('course_id', $m->course_id ?? '');
    $cloId = old('clo_id', $m->clo_id ?? '');
    $poId = old('program_outcome_id', $m->program_outcome_id ?? '');
    $lvl = old('mapping_level', $m->mapping_level ?? '');
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

<div class="row g-3 cpm-form-grid"
    data-url-courses="{{ $cascadeUrls['courses'] }}"
    data-url-outcomes="{{ $cascadeUrls['programOutcomes'] }}"
    data-url-clos="{{ $cascadeUrls['clos'] }}">

    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="cpm_program_id">{{ __('Program') }} <span class="text-danger">*</span></label>
        <select name="program_id" id="cpm_program_id" class="form-select" required data-cpm-program>
            <option value="">{{ __('Select program') }}</option>
            @foreach ($programs as $p)
                <option value="{{ $p->id }}" @selected((string) $progId === (string) $p->id)>
                    {{ $p->program_code }} — {{ $p->program_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="cpm_course_id">{{ __('Course') }} <span class="text-danger">*</span></label>
        <select name="course_id" id="cpm_course_id" class="form-select" required data-cpm-course
            data-ph-need-program="{{ __('Select program first') }}"
            data-ph-loading="{{ __('Loading…') }}"
            data-ph-error="{{ __('Could not load courses or PO/PLO list') }}"
            data-ph-select="{{ __('Select course') }}">
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
        <label class="form-label" for="cpm_clo_id">{{ __('CLO') }} <span class="text-danger">*</span></label>
        <select name="clo_id" id="cpm_clo_id" class="form-select" required data-cpm-clo
            data-ph-need-course="{{ __('Select course first') }}"
            data-ph-loading="{{ __('Loading…') }}">
            @forelse ($clos ?? [] as $clo)
                <option value="{{ $clo->id }}" @selected((string) $cloId === (string) $clo->id)>
                    {{ $clo->clo_code }}{{ $clo->title ? ' — ' . \Illuminate\Support\Str::limit($clo->title, 40) : '' }}
                </option>
            @empty
                <option value="">{{ __('Select course first') }}</option>
            @endforelse
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="cpm_program_outcome_id">{{ __('PO/PLO') }} <span class="text-danger">*</span></label>
        <select name="program_outcome_id" id="cpm_program_outcome_id" class="form-select" required data-cpm-outcome
            data-ph-loading="{{ __('Loading…') }}"
            data-ph-select="{{ __('Select PO/PLO') }}">
            @forelse ($programOutcomes ?? [] as $po)
                <option value="{{ $po->id }}" @selected((string) $poId === (string) $po->id)>
                    {{ $po->outcome_code }}{{ $po->title ? ' — ' . \Illuminate\Support\Str::limit($po->title, 36) : '' }}
                </option>
            @empty
                <option value="">{{ __('Select program first') }}</option>
            @endforelse
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="cpm_mapping_level">{{ __('Mapping level') }} <span class="text-danger">*</span></label>
        <select name="mapping_level" id="cpm_mapping_level" class="form-select" required>
            <option value="">{{ __('Select level') }}</option>
            @foreach ([1 => __('Low'), 2 => __('Medium'), 3 => __('High')] as $val => $label)
                <option value="{{ $val }}" @selected((string) $lvl === (string) $val)>{{ $val }} — {{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="cpm_status_id">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="status_id" id="cpm_status_id" class="form-select" required>
            <option value="">{{ __('Select status') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected((string) old('status_id', $m->status_id ?? '') === (string) $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="cpm_remarks">{{ __('Remarks') }}</label>
        <textarea name="remarks" id="cpm_remarks" class="form-control" rows="3">{{ old('remarks', $m->remarks ?? '') }}</textarea>
    </div>
</div>
