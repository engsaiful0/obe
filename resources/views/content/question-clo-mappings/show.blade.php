@extends('layouts/layoutMaster')

@section('title', $mapping->question_label)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ $mapping->question_label }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('question-clo-mappings.edit', $mapping) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
            <a href="{{ route('question-clo-mappings.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Course') }}</dt>
            <dd class="col-sm-9">{{ $mapping->course->course_code ?? '—' }} — {{ $mapping->course->course_title ?? '' }}</dd>

            <dt class="col-sm-3">{{ __('Assessment component') }}</dt>
            <dd class="col-sm-9">{{ $mapping->assessmentComponent->component_name ?? '—' }} ({{ __('max marks') }} {{ $mapping->assessmentComponent->marks ?? '—' }})</dd>

            <dt class="col-sm-3">{{ __('Main question no') }}</dt>
            <dd class="col-sm-9">{{ $mapping->main_question_no ?: '—' }}</dd>

            <dt class="col-sm-3">{{ __('Main question marks (cap)') }}</dt>
            <dd class="col-sm-9">{{ $mapping->main_question_marks }}</dd>

            <dt class="col-sm-3">{{ __('Multiple parts') }}</dt>
            <dd class="col-sm-9">{{ $mapping->has_multiple_questions ? __('Yes') : __('No') }}</dd>

            <dt class="col-sm-3">{{ __('Question part') }}</dt>
            <dd class="col-sm-9">{{ $mapping->question_part ?: '—' }}</dd>

            <dt class="col-sm-3">{{ __('Question label') }}</dt>
            <dd class="col-sm-9">{{ $mapping->question_label }}</dd>

            <dt class="col-sm-3">{{ __('Marks') }}</dt>
            <dd class="col-sm-9">{{ $mapping->marks }}</dd>

            <dt class="col-sm-3">{{ __('CLO') }}</dt>
            <dd class="col-sm-9">{{ $mapping->clo->clo_code ?? '—' }}{{ $mapping->clo->title ? ' — '.$mapping->clo->title : '' }}</dd>

            <dt class="col-sm-3">{{ __("Bloom") }}</dt>
            <dd class="col-sm-9">{{ $mapping->bloom->name ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __('Title') }}</dt>
            <dd class="col-sm-9">{{ $mapping->question_title ?: '—' }}</dd>

            <dt class="col-sm-3">{{ __('Description') }}</dt>
            <dd class="col-sm-9">{{ $mapping->question_description ?: '—' }}</dd>

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
