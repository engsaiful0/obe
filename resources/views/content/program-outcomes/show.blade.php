@extends('layouts/layoutMaster')

@section('title', __('Program outcome'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ $programOutcome->outcome_code }} — {{ __('Program outcome') }}</h5>
        <div class="d-flex gap-1">
            <a href="{{ route('program-outcomes.edit', $programOutcome) }}" class="btn btn-sm btn-warning">{{ __('Edit') }}</a>
            <a href="{{ route('program-outcomes.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Program') }}</dt>
            <dd class="col-sm-9">
                {{ optional($programOutcome->program)->program_name ?? '—' }}
                @if(optional($programOutcome->program)->program_code) ({{ $programOutcome->program->program_code }}) @endif
            </dd>
            <dt class="col-sm-3">{{ __('Department') }}</dt>
            <dd class="col-sm-9">{{ optional(optional($programOutcome->program)->department)->name ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Outcome type') }}</dt>
            <dd class="col-sm-9">{{ $programOutcome->outcome_type }}</dd>
            <dt class="col-sm-3">{{ __('Outcome code') }}</dt>
            <dd class="col-sm-9"><span class="fw-medium">{{ $programOutcome->outcome_code }}</span></dd>
            <dt class="col-sm-3">{{ __('Title') }}</dt>
            <dd class="col-sm-9">{{ $programOutcome->title ?: '—' }}</dd>
            <dt class="col-sm-3">{{ __('Category') }}</dt>
            <dd class="col-sm-9">{{ $programOutcome->category ?: '—' }}</dd>
            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9">
                @php $active = strtolower($programOutcome->status ?? '') === 'active'; @endphp
                <span class="badge {{ $active ? 'bg-success' : 'bg-secondary' }}">{{ __($programOutcome->status) }}</span>
            </dd>
            <dt class="col-sm-3">{{ __('Description') }}</dt>
            <dd class="col-sm-9"><pre class="mb-0 text-wrap" style="white-space: pre-wrap;">{{ $programOutcome->description }}</pre></dd>
        </dl>
    </div>
</div>
@endsection
