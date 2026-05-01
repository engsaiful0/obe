@extends('layouts/layoutMaster')

@section('title', __('Edit vision'))

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ __('Edit vision') }}</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('visions.update', $vision) }}">
            @csrf
            @method('PUT')
            @include('content.visions._form', ['vision' => $vision])
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                <a href="{{ route('visions.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
