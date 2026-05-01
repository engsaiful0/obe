@php
    $m = $mapping;
    $multiRaw = old('has_multiple_questions', $m->has_multiple_questions ?? false);
    $multiYes = filter_var($multiRaw, FILTER_VALIDATE_BOOLEAN);
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

<div class="row g-2 mb-3 small text-muted">
    <div class="col-md-6">
        {{ __('Program') }}: <span class="text-dark">{{ $m->program->program_code ?? '—' }} — {{ $m->program->program_name ?? '' }}</span>
    </div>
    <div class="col-md-6">
        {{ __('Course') }}: <span class="text-dark">{{ $m->course->course_code ?? '—' }} — {{ \Illuminate\Support\Str::limit($m->course->course_title ?? '', 42) }}</span>
    </div>
    <div class="col-md-6">
        {{ __('Assessment component') }}: <span class="text-dark">{{ $m->assessmentComponent->component_name ?? '—' }}</span>
    </div>
    <div class="col-md-6">
        {{ __('Main question no.') }}: <span class="text-dark fw-medium">{{ $m->main_question_no }}</span>
    </div>
</div>

<input type="hidden" name="_context" value="edit">

<div class="row g-3 qcm-edit-grid">
    <div class="col-md-4 col-lg-3">
        <label class="form-label" for="qcm_main_question_marks">{{ __('Main question marks') }} <span class="text-danger">*</span></label>
        <input type="number" name="main_question_marks" id="qcm_main_question_marks" class="form-control" required
            step="0.01" min="0.01" max="100"
            value="{{ old('main_question_marks', $m->main_question_marks) }}">
        <div class="form-text">{{ __('Cap for all parts under this main question.') }}</div>
    </div>
    <div class="col-12">
        <label class="form-label d-block">{{ __('Multiple questions (parts)') }} <span class="text-danger">*</span></label>
        <div class="d-flex flex-wrap gap-3">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="has_multiple_questions" id="qcm_has_multi_yes" value="1"
                    data-qcm-multi-toggle @checked($multiYes) required>
                <label class="form-check-label" for="qcm_has_multi_yes">{{ __('Yes') }}</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="has_multiple_questions" id="qcm_has_multi_no" value="0"
                    data-qcm-multi-toggle @checked(! $multiYes) required>
                <label class="form-check-label" for="qcm_has_multi_no">{{ __('No') }}</label>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="qcm_question_part">{{ __('Question part') }}</label>
        <select name="question_part" id="qcm_question_part" class="form-select" data-qcm-part-select>
            <option value="">—</option>
            @foreach (['a', 'b', 'c', 'd'] as $lp)
                <option value="{{ $lp }}" @selected(old('question_part', $m->question_part) === $lp)>{{ $lp }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5 col-lg-4">
        <label class="form-label" for="qcm_question_label">{{ __('Question label') }} <span class="text-danger">*</span></label>
        <input type="text" name="question_label" id="qcm_question_label" class="form-control" maxlength="50" required
            value="{{ old('question_label', $m->question_label) }}">
    </div>
    <div class="col-md-4 col-lg-3">
        <label class="form-label" for="qcm_marks">{{ __('Part marks') }} <span class="text-danger">*</span></label>
        <input type="number" name="marks" id="qcm_marks" class="form-control" required step="0.01" min="0.01" max="100"
            value="{{ old('marks', $m->marks) }}">
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_question_title">{{ __('Question title') }}</label>
        <input type="text" name="question_title" id="qcm_question_title" class="form-control" maxlength="255"
            value="{{ old('question_title', $m->question_title ?? '') }}">
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_clo_id">{{ __('CLO') }} <span class="text-danger">*</span></label>
        <select name="clo_id" id="qcm_clo_id" class="form-select" required data-qcm-edit-clo
            data-url-bloom="{{ $cascadeUrls['bloomByClo'] }}">
            @foreach ($clos ?? [] as $clo)
                <option value="{{ $clo->id }}" data-bloom-id="{{ $clo->bloom_id }}" @selected((string) old('clo_id', $m->clo_id) === (string) $clo->id)>
                    {{ $clo->clo_code }}{{ $clo->title ? ' — ' . \Illuminate\Support\Str::limit($clo->title, 40) : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_bloom_id">{{ __('Bloom') }}</label>
        <select name="bloom_id" id="qcm_bloom_id" class="form-select" data-qcm-edit-bloom>
            <option value="">{{ __('— Optional / CLO default —') }}</option>
            @foreach ($blooms as $bl)
                <option value="{{ $bl->id }}" @selected((string) old('bloom_id', $m->bloom_id ?? '') === (string) $bl->id)>
                    {{ $bl->level_order }}. {{ $bl->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-4">
        <label class="form-label" for="qcm_status_id">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="status_id" id="qcm_status_id" class="form-select" required>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected((string) old('status_id', $m->status_id) === (string) $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="qcm_question_description">{{ __('Question description') }}</label>
        <textarea name="question_description" id="qcm_question_description" class="form-control" rows="2">{{ old('question_description', $m->question_description ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="qcm_remarks">{{ __('Remarks') }}</label>
        <textarea name="remarks" id="qcm_remarks" class="form-control" rows="2">{{ old('remarks', $m->remarks ?? '') }}</textarea>
    </div>
</div>
