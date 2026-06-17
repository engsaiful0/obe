@php
    $requirement = $requirement ?? null;
    $row = optional($requirement);
    $criterionValue = old('bac_criterion_id', $row->bac_criterion_id ?? $selectedCriterionId ?? null);
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="bac_criterion_id">{{ __('BAC criterion') }} <span class="text-danger">*</span></label>
        <select name="bac_criterion_id" id="bac_criterion_id" class="form-select" required>
            <option value="">{{ __('Select criterion') }}</option>
            @foreach ($criteria as $criterion)
                <option value="{{ $criterion->id }}" @selected((string) $criterionValue === (string) $criterion->id)>
                    {{ $criterion->standard?->standard_no }} / {{ $criterion->criterion_no }} - {{ \Illuminate\Support\Str::limit($criterion->title ?: $criterion->description, 90) }}
                    @unless ($criterion->is_active) ({{ __('Inactive') }}) @endunless
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="evidence_type">{{ __('Evidence type') }}</label>
        <input type="text" name="evidence_type" id="evidence_type" class="form-control" maxlength="255"
            value="{{ old('evidence_type', $row->evidence_type) }}" placeholder="{{ __('Document') }}">
    </div>
    <div class="col-md-8">
        <label class="form-label" for="title">{{ __('Title') }} <span class="text-danger">*</span></label>
        <input type="text" name="title" id="title" class="form-control" required maxlength="255"
            value="{{ old('title', $row->title) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="sort_order">{{ __('Sort order') }}</label>
        <input type="number" min="0" name="sort_order" id="sort_order" class="form-control"
            value="{{ old('sort_order', $row->sort_order) }}">
    </div>
    <div class="col-12">
        <label class="form-label" for="description">{{ __('Description') }}</label>
        <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $row->description) }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="is_required">{{ __('Requirement') }} <span class="text-danger">*</span></label>
        <select name="is_required" id="is_required" class="form-select" required>
            <option value="1" @selected((string) old('is_required', $requirement === null ? '1' : (string) (int) $requirement->is_required) === '1')>{{ __('Required') }}</option>
            <option value="0" @selected((string) old('is_required', $requirement === null ? '1' : (string) (int) $requirement->is_required) === '0')>{{ __('Optional') }}</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="is_active">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="is_active" id="is_active" class="form-select" required>
            <option value="1" @selected((string) old('is_active', $requirement === null ? '1' : (string) (int) $requirement->is_active) === '1')>{{ __('Active') }}</option>
            <option value="0" @selected((string) old('is_active', $requirement === null ? '1' : (string) (int) $requirement->is_active) === '0')>{{ __('Inactive') }}</option>
        </select>
    </div>
</div>
