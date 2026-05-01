@extends('layouts/layoutMaster')

@section('title', __('Add vision'))

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ __('New vision statement') }}</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('visions.store') }}">
            @csrf
            @include('content.visions._form', ['vision' => null])
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('visions.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
