@extends('layouts/layoutMaster')

@section('title', __('Edit CLO'))

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('Edit course learning outcome') }}</h5>
    </div>
    <div class="card-body">
        <form method="POST"
            action="{{ route('clos.update', $clo) }}"
            id="clo-edit-form"
            data-async-no-submit
            data-clo-courses-url-pattern="{{ $coursesAjaxUrlPattern }}"
            autocomplete="off"
            onsubmit="return false;">
            @csrf
            @method('PUT')
            <div class="alert alert-danger d-none mb-3" role="alert" data-ajax-errors></div>
            @include('content.clos._form', ['clo' => $clo])
            <div class="mt-3 d-flex gap-2">
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 obe-ajax-primary obe-async-save">
                    <span class="obe-btn-label">{{ __('Update') }}</span>
                    <span class="spinner-border spinner-border-sm d-none obe-btn-spinner" role="status" aria-hidden="true"></span>
                </button>
                <a href="{{ route('clos.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
<script src="{{ asset('assets/js/clo-program-courses.js') }}"></script>
@endsection
