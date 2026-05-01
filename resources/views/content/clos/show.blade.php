@extends('layouts/layoutMaster')

@section('title', __('CLO') . ' ' . $clo->clo_code)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ $clo->clo_code }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('clos.edit', $clo) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
            <a href="{{ route('clos.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Program') }}</dt>
            <dd class="col-sm-9">{{ $clo->program->program_name ?? '—' }} ({{ $clo->program->program_code ?? '—' }})</dd>

            <dt class="col-sm-3">{{ __('Course') }}</dt>
            <dd class="col-sm-9">{{ $clo->course->course_code ?? '—' }} — {{ $clo->course->course_title ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __("Bloom level") }}</dt>
            <dd class="col-sm-9">{{ $clo->bloom->name ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __('Title') }}</dt>
            <dd class="col-sm-9">{{ $clo->title ?: '—' }}</dd>

            <dt class="col-sm-3">{{ __('Description') }}</dt>
            <dd class="col-sm-9">{{ $clo->description }}</dd>

            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9">
                @php $sn = strtolower($clo->status->status_name ?? ''); @endphp
                <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                    {{ $clo->status->status_name ?? '—' }}
                </span>
            </dd>
        </dl>
    </div>
</div>
@endsection
