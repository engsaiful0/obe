@php
    $m = $mapping ?? null;
    $progId = old('program_id', $m->program_id ?? '');
    $courseId = old('course_id', $m->course_id ?? '');
    $acId = old('assessment_component_id', $m->assessment_component_id ?? '');
    $cloId = old('clo_id', $m->clo_id ?? '');
    $bloomId = old('bloom_id', $m->bloom_id ?? '');
    $mainQ = old('main_question_no', $m->main_question_no ?? '');
    $qPart = old('question_part', $m->question_part ?? '');
    $qLabel = old('question_label', $m->question_label ?? '');
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

<div class="row g-3 qcm-form-grid"
    data-url-courses="{{ $cascadeUrls['courses'] }}"
    data-url-assessment-components="{{ $cascadeUrls['assessmentComponents'] }}"
    data-url-clos="{{ $cascadeUrls['clos'] }}"
    data-url-bloom-clo="{{ $cascadeUrls['bloomByClo'] }}">
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_program_id">{{ __('Program') }} <span class="text-danger">*</span></label>
        <select name="program_id" id="qcm_program_id" class="form-select" required data-qcm-program>
            <option value="">{{ __('Select program') }}</option>
            @foreach ($programs as $p)
                <option value="{{ $p->id }}" @selected((string) $progId === (string) $p->id)>
                    {{ $p->program_code }} — {{ $p->program_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_course_id">{{ __('Course') }} <span class="text-danger">*</span></label>
        <select name="course_id" id="qcm_course_id" class="form-select" required data-qcm-course
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
        <label class="form-label" for="qcm_ac_id">{{ __('Assessment component') }} <span class="text-danger">*</span></label>
        <select name="assessment_component_id" id="qcm_ac_id" class="form-select" required data-qcm-component
            data-ph-need-course="{{ __('Select course first') }}"
            data-ph-loading="{{ __('Loading…') }}"
            data-ph-select="{{ __('Select component') }}"
            data-ph-error="{{ __('Could not load components') }}">
            @forelse ($assessmentComponents ?? [] as $ac)
                <option value="{{ $ac->id }}" @selected((string) $acId === (string) $ac->id)>
                    {{ $ac->component_name }} ({{ __('max') }} {{ $ac->marks }})
                </option>
            @empty
                <option value="">{{ __('Select course first') }}</option>
            @endforelse
        </select>
    </div>
    <div class="col-md-4 col-lg-3">
        <label class="form-label" for="qcm_main_question_no">{{ __('Main question no') }}</label>
        <input type="text" name="main_question_no" id="qcm_main_question_no" class="form-control" maxlength="20"
            value="{{ $mainQ }}" placeholder="{{ __('e.g. Q1 or 1') }}" data-qcm-main>
    </div>
    <div class="col-md-4 col-lg-3">
        <label class="form-label" for="qcm_question_part">{{ __('Question part') }}</label>
        <input type="text" name="question_part" id="qcm_question_part" class="form-control" maxlength="20"
            value="{{ $qPart }}" placeholder="{{ __('e.g. a, b, c, d') }}" data-qcm-part>
    </div>
    <div class="col-md-4 col-lg-4">
        <label class="form-label" for="qcm_question_label">{{ __('Question label') }} <span class="text-danger">*</span></label>
        <input type="text" name="question_label" id="qcm_question_label" @class(['form-control', 'qcm-label-auto' => ! $m]) maxlength="50" required
            value="{{ $qLabel }}" placeholder="{{ __('e.g. 1a, Q1a, Q1(a)') }}" data-qcm-label autocomplete="off">
        <div class="form-text">{{ __('Displayed in reports and marks entry. Edit freely after auto-suggestion.') }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="qcm_marks">{{ __('Marks') }} <span class="text-danger">*</span></label>
        <input type="number" name="marks" id="qcm_marks" class="form-control" required step="0.01" min="0.01" max="100"
            value="{{ old('marks', $m->marks ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="qcm_question_title">{{ __('Question title') }}</label>
        <input type="text" name="question_title" id="qcm_question_title" class="form-control" maxlength="255"
            value="{{ old('question_title', $m->question_title ?? '') }}">
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_clo_id">{{ __('CLO') }} <span class="text-danger">*</span></label>
        <select name="clo_id" id="qcm_clo_id" class="form-select" required data-qcm-clo
            data-ph-need-course="{{ __('Select course first') }}"
            data-ph-loading="{{ __('Loading…') }}"
            data-ph-select="{{ __('Select CLO') }}"
            data-ph-error="{{ __('Could not load CLOs') }}">
            @forelse ($clos ?? [] as $clo)
                <option value="{{ $clo->id }}" data-bloom-id="{{ $clo->bloom_id }}" @selected((string) $cloId === (string) $clo->id)>
                    {{ $clo->clo_code }}{{ $clo->title ? ' — ' . \Illuminate\Support\Str::limit($clo->title, 40) : '' }}
                </option>
            @empty
                <option value="">{{ __('Select course first') }}</option>
            @endforelse
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_bloom_id">{{ __("Bloom level") }}</label>
        <select name="bloom_id" id="qcm_bloom_id" class="form-select" data-qcm-bloom>
            <option value="">{{ __('— Optional / auto from CLO —') }}</option>
            @foreach ($blooms as $bl)
                <option value="{{ $bl->id }}" @selected((string) $bloomId === (string) $bl->id)>
                    {{ $bl->level_order }}. {{ $bl->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">{{ __('Matches the taxonomy level mapped to the selected CLO.') }}</div>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_status_id">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="status_id" id="qcm_status_id" class="form-select" required>
            <option value="">{{ __('Select status') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected((string) old('status_id', $m->status_id ?? '') === (string) $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="qcm_question_description">{{ __('Question description') }}</label>
        <textarea name="question_description" id="qcm_question_description" class="form-control" rows="3">{{ old('question_description', $m->question_description ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="qcm_remarks">{{ __('Remarks') }}</label>
        <textarea name="remarks" id="qcm_remarks" class="form-control" rows="2">{{ old('remarks', $m->remarks ?? '') }}</textarea>
    </div>
</div>
