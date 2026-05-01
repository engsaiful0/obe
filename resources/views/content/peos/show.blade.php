@extends('layouts/layoutMaster')

@section('title', __('PEO'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ $peo->peo_code }}</h5>
        <div class="d-flex gap-1">
            <a href="{{ route('peos.edit', $peo) }}" class="btn btn-sm btn-warning">{{ __('Edit') }}</a>
            <a href="{{ route('peos.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Program') }}</dt>
            <dd class="col-sm-9">
                {{ optional($peo->program)->program_name ?? '—' }}
                @if(optional($peo->program)->program_code)
                    ({{ $peo->program->program_code }})
                @endif
            </dd>
            <dt class="col-sm-3">{{ __('Department') }}</dt>
            <dd class="col-sm-9">{{ optional(optional($peo->program)->department)->name ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('PEO title') }}</dt><dd class="col-sm-9">{{ $peo->peo_title ?: '—' }}</dd>
            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9"><span class="badge bg-label-primary">{{ $peo->status->status_name ?? '—' }}</span></dd>
            <dt class="col-sm-3">{{ __('Description') }}</dt>
            <dd class="col-sm-9"><pre class="mb-0 text-wrap" style="white-space: pre-wrap;">{{ $peo->description }}</pre></dd>
        </dl>
    </div>
</div>
@endsection
