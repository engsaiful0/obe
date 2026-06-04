<form id="cf-cqi-form">
    @foreach ([
        'strengths' => __('Strengths'),
        'weaknesses' => __('Weaknesses'),
        'problems' => __('Problems Faced'),
        'improvements' => __('Proposed Improvements'),
        'recommendations' => __('Future Recommendations'),
    ] as $field => $label)
        <div class="mb-3">
            <label class="form-label">{{ $label }}</label>
            <textarea name="{{ $field }}" class="form-control" rows="3" @disabled(! $canManage)>{{ old($field, $cqi?->$field) }}</textarea>
        </div>
    @endforeach
    @if ($canManage)
        <button type="submit" class="btn btn-primary btn-sm">{{ __('Save CQI') }}</button>
    @else
        <p class="small text-muted">{{ __('Read-only view.') }}</p>
    @endif
</form>
