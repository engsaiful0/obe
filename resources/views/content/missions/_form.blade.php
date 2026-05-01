@php
    $mission = $mission ?? null;
    $m = optional($mission);
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
    <div class="col-md-4">
        <label class="form-label">{{ __('Scope') }} <span class="text-danger">*</span></label>
        <select name="type" class="form-select" required>
            <option value="">{{ __('Select') }}</option>
            @foreach (['University' => __('University'), 'Department' => __('Department')] as $val => $label)
                <option value="{{ $val }}" @selected(old('type', $m->type) === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('University') }}</label>
        <select name="university_id" class="form-select">
            <option value="">{{ __('—') }}</option>
            @foreach ($universities as $u)
                <option value="{{ $u->id }}" @selected(old('university_id', $m->university_id) == $u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('Required when scope is University.') }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('Department') }}</label>
        <select name="department_id" class="form-select">
            <option value="">{{ __('—') }}</option>
            @foreach ($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $m->department_id) == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('Required when scope is Department.') }}</div>
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('Title') }}</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $m->title) }}" maxlength="255">
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="5" required>{{ old('description', $m->description) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('Status (OBE)') }} <span class="text-danger">*</span></label>
        <select name="status_id" class="form-select" required>
            <option value="">{{ __('Select status') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected(old('status_id', $m->status_id) == $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
