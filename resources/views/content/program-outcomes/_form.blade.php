@php
    $po = isset($programOutcome) ? $programOutcome : null;
    $row = optional($po);
    $outcomeType = old('outcome_type', $row->outcome_type ?? 'PO');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="program_outcome_program_id">{{ __('Program') }} <span class="text-danger">*</span></label>
        <select name="program_id" id="program_outcome_program_id" class="form-select" required>
            <option value="">{{ __('Select program') }}</option>
            @foreach ($programs as $pr)
                <option value="{{ $pr->id }}" @selected(old('program_id', $row->program_id) == $pr->id)>
                    {{ $pr->program_name }} @if ($pr->program_code)({{ $pr->program_code }})@endif
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="program_outcome_type">{{ __('Outcome type') }} <span class="text-danger">*</span></label>
        <select name="outcome_type" id="program_outcome_type" class="form-select" required>
            @foreach (['PO' => 'PO', 'PLO' => 'PLO'] as $val => $lbl)
                <option value="{{ $val }}" @selected($outcomeType === $val)>{{ __($lbl) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="program_outcome_code">{{ __('Outcome code') }} <span class="text-danger">*</span></label>
        <input type="text" name="outcome_code" id="program_outcome_code" class="form-control" required
            maxlength="50" placeholder="PO1"
            value="{{ old('outcome_code', $row->outcome_code) }}">
    </div>

    <div class="col-12">
        <label class="form-label" for="program_outcome_title">{{ __('Title') }}</label>
        <input type="text" name="title" id="program_outcome_title" class="form-control"
            maxlength="255" value="{{ old('title', $row->title) }}">
    </div>

    <div class="col-12">
        <label class="form-label" for="program_outcome_description">{{ __('Description') }} <span class="text-danger">*</span></label>
        <textarea name="description" id="program_outcome_description" class="form-control" rows="4" required>{{ old('description', $row->description) }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="program_outcome_category">{{ __('Category') }}</label>
        <select name="category" id="program_outcome_category" class="form-select">
            <option value="">{{ __('—') }}</option>
            @foreach (\App\Http\Requests\StoreProgramOutcomeRequest::categoryValues() as $cat)
                <option value="{{ $cat }}" @selected(old('category', $row->category) === $cat)>{{ __($cat) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="program_outcome_status">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="status" id="program_outcome_status" class="form-select" required>
            @foreach (['Active' => __('Active'), 'Inactive' => __('Inactive')] as $sv => $sl)
                <option value="{{ $sv }}" @selected(old('status', $row->status ?: 'Active') === $sv)>{{ $sl }}</option>
            @endforeach
        </select>
    </div>
</div>
