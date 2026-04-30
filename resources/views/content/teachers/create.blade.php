@extends('layouts/layoutMaster')

@section('title', 'Create Teacher')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Create Teacher</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('teachers.store') }}" enctype="multipart/form-data">
            @csrf
            @include('content.teachers._form')
            <div class="mt-3">
                <button class="btn btn-primary">Save Teacher</button>
                <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
