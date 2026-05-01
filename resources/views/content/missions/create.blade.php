@extends('layouts/layoutMaster')

@section('title', __('Add mission'))

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ __('New mission statement') }}</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('missions.store') }}" data-ajax-submit>
            @csrf
            <div class="alert alert-danger d-none mb-3" role="alert" data-ajax-errors></div>
            @include('content.missions._form', ['mission' => null])
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 obe-ajax-primary">
                    <span class="obe-btn-label">{{ __('Save') }}</span>
                    <span class="spinner-border spinner-border-sm d-none obe-btn-spinner" role="status" aria-hidden="true"></span>
                </button>
                <a href="{{ route('missions.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
