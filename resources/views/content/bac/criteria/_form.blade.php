@php
    $criterion = $criterion ?? null;
    $row = optional($criterion);
    $standardValue = old('bac_standard_id', $row->bac_standard_id ?? $selectedStandardId ?? null);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="bac_standard_id">{{ __('BAC standard') }} <span class="text-danger">*</span></label>
        <select name="bac_standard_id" id="bac_standard_id" class="form-select" required>
            <option value="">{{ __('Select standard') }}</option>
            @foreach ($standards as $standard)
                <option value="{{ $standard->id }}" @selected((string) $standardValue === (string) $standard->id)>
                    {{ $standard->standard_no }} - {{ $standard->title }}
                    @unless ($standard->is_active) ({{ __('Inactive') }}) @endunless
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="criterion_no">{{ __('Criterion no') }} <span class="text-danger">*</span></label>
        <input type="text" name="criterion_no" id="criterion_no" class="form-control" required maxlength="50"
            value="{{ old('criterion_no', $row->criterion_no) }}" placeholder="1.1">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="sort_order">{{ __('Sort order') }}</label>
        <input type="number" min="0" name="sort_order" id="sort_order" class="form-control"
            value="{{ old('sort_order', $row->sort_order) }}">
    </div>
    <div class="col-md-8">
        <label class="form-label" for="title">{{ __('Title') }}</label>
        <input type="text" name="title" id="title" class="form-control" maxlength="255"
            value="{{ old('title', $row->title) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="responsible_role">{{ __('Responsible role') }}</label>
        <input type="text" name="responsible_role" id="responsible_role" class="form-control" maxlength="255"
            value="{{ old('responsible_role', $row->responsible_role) }}" placeholder="{{ __('Program coordinator') }}">
    </div>
    <div class="col-12">
        <label class="form-label" for="description">{{ __('Description') }} <span class="text-danger">*</span></label>
        <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description', $row->description) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="required_evidence">{{ __('Required evidence') }}</label>
        <textarea name="required_evidence" id="required_evidence" class="form-control" rows="3">{{ old('required_evidence', $row->required_evidence) }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="is_active">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="is_active" id="is_active" class="form-select" required>
            <option value="1" @selected((string) old('is_active', $criterion === null ? '1' : (string) (int) $criterion->is_active) === '1')>{{ __('Active') }}</option>
            <option value="0" @selected((string) old('is_active', $criterion === null ? '1' : (string) (int) $criterion->is_active) === '0')>{{ __('Inactive') }}</option>
        </select>
    </div>
</div>
