@php $r = isset($bloom) ? $bloom : null; @endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="bloom_name">{{ __('Name') }} <span class="text-danger">*</span></label>
        <input type="text" name="name" id="bloom_name" class="form-control" required maxlength="100"
            value="{{ old('name', $r?->name) }}" placeholder="{{ __('e.g. Remember') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="bloom_level_order">{{ __('Level order') }} <span class="text-danger">*</span></label>
        <input type="number" name="level_order" id="bloom_level_order" class="form-control" required min="1" max="10"
            value="{{ old('level_order', $r?->level_order) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="bloom_status_id">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="status_id" id="bloom_status_id" class="form-select" required>
            <option value="">{{ __('Select status') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st->id }}" @selected(old('status_id', $r?->status_id) == $st->id)>
                    {{ $st->status_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="bloom_description">{{ __('Description') }}</label>
        <textarea name="description" id="bloom_description" class="form-control" rows="4">{{ old('description', $r?->description) }}</textarea>
    </div>
</div>
