@extends('layouts/layoutMaster')

@section('title', $bloom->name)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __("Bloom level") }}</h5>
        <div class="d-flex gap-1">
            <a href="{{ route('blooms.edit', $bloom) }}" class="btn btn-sm btn-warning">{{ __('Edit') }}</a>
            <a href="{{ route('blooms.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Level order') }}</dt>
            <dd class="col-sm-9">{{ $bloom->level_order }}</dd>
            <dt class="col-sm-3">{{ __('Name') }}</dt>
            <dd class="col-sm-9">{{ $bloom->name }}</dd>
            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9">
                @php $sn = strtolower($bloom->status->status_name ?? ''); @endphp
                <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                    {{ $bloom->status->status_name ?? '—' }}
                </span>
            </dd>
            <dt class="col-sm-3">{{ __('Description') }}</dt>
            <dd class="col-sm-9">
                <pre class="mb-0 text-wrap" style="white-space: pre-wrap;">{{ $bloom->description ?: '—' }}</pre>
            </dd>
        </dl>
    </div>
</div>
@endsection
