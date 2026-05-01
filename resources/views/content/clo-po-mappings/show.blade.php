@extends('layouts/layoutMaster')

@section('title', __('CLO–PO mapping'))

@php
    $levelBadges = [
        1 => ['secondary', __('Low')],
        2 => ['info', __('Medium')],
        3 => ['success', __('High')],
    ];
    $lvl = (int) $mapping->mapping_level;
    $lb = $levelBadges[$lvl] ?? ['secondary', '?'];
@endphp

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('Mapping detail') }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('clo-po-mappings.edit', $mapping) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
            <a href="{{ route('clo-po-mappings.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Program') }}</dt>
            <dd class="col-sm-9">{{ $mapping->program->program_name ?? '—' }} ({{ $mapping->program->program_code ?? '' }})</dd>

            <dt class="col-sm-3">{{ __('Course') }}</dt>
            <dd class="col-sm-9">{{ $mapping->course->course_code ?? '—' }} — {{ $mapping->course->course_title ?? '' }}</dd>

            <dt class="col-sm-3">{{ __('CLO') }}</dt>
            <dd class="col-sm-9">{{ $mapping->clo->clo_code ?? '—' }}{{ $mapping->clo->title ? ' — '.$mapping->clo->title : '' }}</dd>

            <dt class="col-sm-3">{{ __('PO/PLO') }}</dt>
            <dd class="col-sm-9">
                {{ $mapping->programOutcome->outcome_code ?? '—' }}
                @if ($mapping->programOutcome && $mapping->programOutcome->outcome_type)
                    ({{ $mapping->programOutcome->outcome_type }})
                @endif
                {{ $mapping->programOutcome && $mapping->programOutcome->title ? ' — '.$mapping->programOutcome->title : '' }}
            </dd>

            <dt class="col-sm-3">{{ __('Mapping level') }}</dt>
            <dd class="col-sm-9"><span class="badge bg-{{ $lb[0] }}">{{ $lvl }} {{ $lb[1] }}</span></dd>

            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9">
                @php $sn = strtolower($mapping->status->status_name ?? ''); @endphp
                <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                    {{ $mapping->status->status_name ?? '—' }}
                </span>
            </dd>

            <dt class="col-sm-3">{{ __('Remarks') }}</dt>
            <dd class="col-sm-9">{{ $mapping->remarks ?: '—' }}</dd>
        </dl>
    </div>
</div>
@endsection
