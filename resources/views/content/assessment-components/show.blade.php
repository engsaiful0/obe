@extends('layouts/layoutMaster')

@section('title', $component->component_name)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ $component->component_name }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('assessment-components.edit', $component) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
            <a href="{{ route('assessment-components.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Program') }}</dt>
            <dd class="col-sm-9">{{ $component->program->program_name ?? '—' }} ({{ $component->program->program_code ?? '' }})</dd>

            <dt class="col-sm-3">{{ __('Course') }}</dt>
            <dd class="col-sm-9">{{ $component->course->course_code ?? '—' }} — {{ $component->course->course_title ?? '' }}</dd>

            <dt class="col-sm-3">{{ __('Type') }}</dt>
            <dd class="col-sm-9">{{ $component->component_type }}</dd>

            <dt class="col-sm-3">{{ __('Marks') }}</dt>
            <dd class="col-sm-9">{{ $component->marks }}</dd>

            <dt class="col-sm-3">{{ __('Weight (%)') }}</dt>
            <dd class="col-sm-9">{{ $component->weight_percentage !== null ? $component->weight_percentage : '—' }}</dd>

            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9">
                @php $sn = strtolower($component->status->status_name ?? ''); @endphp
                <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                    {{ $component->status->status_name ?? '—' }}
                </span>
            </dd>

            <dt class="col-sm-3">{{ __('Remarks') }}</dt>
            <dd class="col-sm-9">{{ $component->remarks ?: '—' }}</dd>
        </dl>
    </div>
</div>
@endsection
