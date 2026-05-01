@extends('layouts/layoutMaster')

@section('title', __('Edit mission'))

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ __('Edit mission') }}</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('missions.update', $mission) }}">
            @csrf
            @method('PUT')
            @include('content.missions._form', ['mission' => $mission])
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                <a href="{{ route('missions.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
