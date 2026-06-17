@php
    $standard = $standard ?? null;
    $row = optional($standard);
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="standard_no">{{ __('Standard no') }} <span class="text-danger">*</span></label>
        <input type="text" name="standard_no" id="standard_no" class="form-control" required maxlength="50"
            value="{{ old('standard_no', $row->standard_no) }}" placeholder="BAC 1">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="title">{{ __('Title') }} <span class="text-danger">*</span></label>
        <input type="text" name="title" id="title" class="form-control" required maxlength="255"
            value="{{ old('title', $row->title) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="sort_order">{{ __('Sort order') }}</label>
        <input type="number" min="0" name="sort_order" id="sort_order" class="form-control"
            value="{{ old('sort_order', $row->sort_order) }}">
    </div>
    <div class="col-12">
        <label class="form-label" for="description">{{ __('Description') }}</label>
        <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $row->description) }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="is_active">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="is_active" id="is_active" class="form-select" required>
            <option value="1" @selected((string) old('is_active', $standard === null ? '1' : (string) (int) $standard->is_active) === '1')>{{ __('Active') }}</option>
            <option value="0" @selected((string) old('is_active', $standard === null ? '1' : (string) (int) $standard->is_active) === '0')>{{ __('Inactive') }}</option>
        </select>
    </div>
</div>
