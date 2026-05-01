@extends('layouts/layoutMaster')

@section('title', __('Vision'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ __('Vision') }}</h5>
        <div class="d-flex gap-1">
            <a href="{{ route('visions.edit', $vision) }}" class="btn btn-sm btn-warning">{{ __('Edit') }}</a>
            <a href="{{ route('visions.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Type') }}</dt><dd class="col-sm-9">{{ $vision->type }}</dd>
            <dt class="col-sm-3">{{ __('University') }}</dt><dd class="col-sm-9">{{ $vision->university->name ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Department') }}</dt><dd class="col-sm-9">{{ $vision->department->name ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Title') }}</dt><dd class="col-sm-9">{{ $vision->title ?: '—' }}</dd>
            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9">
                <span class="badge bg-label-primary">{{ $vision->status->status_name ?? '—' }}</span>
            </dd>
            <dt class="col-sm-3">{{ __('Description') }}</dt>
            <dd class="col-sm-9"><pre class="mb-0 text-wrap" style="white-space: pre-wrap;">{{ $vision->description }}</pre></dd>
        </dl>
    </div>
</div>
@endsection
