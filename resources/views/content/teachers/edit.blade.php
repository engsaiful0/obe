@extends('layouts/layoutMaster')

@section('title', 'Edit Teacher')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Edit Teacher</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('teachers.update', $teacher->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('content.teachers._form')
            <div class="mt-3">
                <button class="btn btn-primary">Update Teacher</button>
                <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
