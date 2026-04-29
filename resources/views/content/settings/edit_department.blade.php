@extends('layouts/layoutMaster')

@section('title', 'Edit Department')

@section('page-script')
<script>
    window.departmentUrls = {
        update: '{{ route('app-settings-department.update', $department->id) }}',
        index: '{{ route('app-settings-department') }}'
    };
</script>
<script src="{{ asset('assets/js/department-edit.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Edit Department</h5>
            <a href="{{ route('app-settings-department') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <form id="department-form" method="POST" action="{{ route('app-settings-department.update', $department->id) }}">
                @csrf
                @method('PUT')
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label" for="department_name">Department Name <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="department_name2" class="input-group-text"><i class="ti ti-building"></i></span>
                            <input type="text" id="department_name" class="form-control @error('department_name') is-invalid @enderror" 
                                name="department_name" placeholder="Enter Department Name" 
                                aria-label="Enter Department Name" aria-describedby="department_name2" 
                                value="{{ old('department_name', $department->name) }}" required>
                        </div>
                        @error('department_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" id="submit-btn" class="btn btn-primary me-2">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Update Department</span>
                        </button>
                        <a href="{{ route('app-settings-department') }}" class="btn btn-label-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

