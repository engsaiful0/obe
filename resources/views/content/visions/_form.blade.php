@php
    $vision = $vision ?? null;
    $v = optional($vision);
    $scopeType = old('type', $v->type ?? '');
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
    <div class="col-12 col-md-6 col-lg-4">
        <label class="form-label" for="vision_type">{{ __('Scope') }} <span class="text-danger">*</span></label>
        <select name="type" id="vision_type" class="form-select" required>
            <option value="">{{ __('Select') }}</option>
            @foreach (['University' => __('University'), 'Department' => __('Department')] as $val => $label)
                <option value="{{ $val }}" @selected(old('type', $v->type) === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-12 col-md-8 col-lg-6 {{ $scopeType === 'University' ? '' : 'd-none' }}" data-obe-field="university">
        <label class="form-label" for="vision_university_id">{{ __('University') }} <span class="text-danger">*</span></label>
        <select name="university_id" id="vision_university_id" class="form-select">
            <option value="">{{ __('Select university') }}</option>
            @foreach ($universities as $u)
                <option value="{{ $u->id }}" @selected(old('university_id', $v->university_id) == $u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('Choose the university this vision belongs to.') }}</div>
    </div>
    <div class="col-12 col-md-8 col-lg-6 {{ $scopeType === 'Department' ? '' : 'd-none' }}" data-obe-field="department">
        <label class="form-label" for="vision_department_id">{{ __('Department') }} <span class="text-danger">*</span></label>
        <select name="department_id" id="vision_department_id" class="form-select">
            <option value="">{{ __('Select department') }}</option>
            @foreach ($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $v->department_id) == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('Choose the department this vision belongs to.') }}</div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <label class="form-label" for="vision_title">{{ __('Title') }}</label>
        <input type="text" name="title" id="vision_title" class="form-control" value="{{ old('title', $v->title) }}" maxlength="255">
    </div>
    <div class="col-12">
        <label class="form-label" for="vision_description">{{ __('Description') }} <span class="text-danger">*</span></label>
        <textarea name="description" id="vision_description" class="form-control" rows="5" required>{{ old('description', $v->description) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="vision_status_id">{{ __('Status (OBE)') }} <span class="text-danger">*</span></label>
        <select name="status_id" id="vision_status_id" class="form-select" required>
            <option value="">{{ __('Select status') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected(old('status_id', $v->status_id) == $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@include('partials.obe_scope_selects_toggle')
