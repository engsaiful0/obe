@php
    $peo = $peo ?? null;
    $p = optional($peo);
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
    <div class="col-md-6">
        <label class="form-label">{{ __('Program') }} <span class="text-danger">*</span></label>
        <select name="program_id" class="form-select" required>
            <option value="">{{ __('Select program') }}</option>
            @foreach ($programs as $pr)
                <option value="{{ $pr->id }}" @selected(old('program_id', $p->program_id) == $pr->id)>
                    {{ $pr->program_name }} @if($pr->program_code) ({{ $pr->program_code }}) @endif
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('PEO code') }} <span class="text-danger">*</span></label>
        <input type="text" name="peo_code" class="form-control" value="{{ old('peo_code', $p->peo_code) }}" required maxlength="32" placeholder="PEO1">
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Status (OBE)') }} <span class="text-danger">*</span></label>
        <select name="status_id" class="form-select" required>
            <option value="">{{ __('Select status') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected(old('status_id', $p->status_id) == $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('PEO title') }}</label>
        <input type="text" name="peo_title" class="form-control" value="{{ old('peo_title', $p->peo_title) }}" maxlength="255">
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="5" required>{{ old('description', $p->description) }}</textarea>
    </div>
</div>
