@extends('layouts/layoutMaster')

@section('title', __('Add question–CLO mapping'))

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('New question–CLO mapping') }}</h5>
        <div class="form-text">{{ __('Define main questions and parts; totals are validated against each main budget and the component cap.') }}</div>
    </div>
    <div class="card-body">
        @include('content.question-clo-mappings._create_wizard')
        <div class="mt-3 d-flex gap-2">
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" id="qcm-wizard-save">
                <span class="obe-btn-label">{{ __('Save') }}</span>
                <span class="spinner-border spinner-border-sm d-none obe-btn-spinner" role="status" aria-hidden="true"></span>
            </button>
            <a href="{{ route('question-clo-mappings.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/question-clo-mapping-create-wizard.js') }}"></script>
@endsection
