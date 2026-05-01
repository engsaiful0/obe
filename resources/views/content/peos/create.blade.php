@extends('layouts/layoutMaster')

@section('title', __('Add PEO'))

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ __('New PEO') }}</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('peos.store') }}">
            @csrf
            @include('content.peos._form', ['peo' => null])
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('peos.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
