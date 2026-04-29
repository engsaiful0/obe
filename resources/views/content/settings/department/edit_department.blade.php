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
                    <div class="col-md-6">
                        <label class="form-label" for="name">Department Name <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="name2" class="input-group-text"><i class="ti ti-building"></i></span>
                            <select id="name" class="form-select @error('name') is-invalid @enderror" name="name" aria-label="Select Department" aria-describedby="name2" required>
                                <option value="">Select Department</option>
                                @foreach($allowedDepartments as $dept)
                                <option value="{{ $dept }}" {{ old('name', $department->name) == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="description">Description</label>
                        <div class="input-group input-group-merge">
                            <span id="description2" class="input-group-text"><i class="ti ti-file-text"></i></span>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror" 
                                      name="description" rows="3" 
                                      placeholder="Enter description (optional)" 
                                      aria-label="Enter description" aria-describedby="description2">{{ old('description', $department->description) }}</textarea>
                        </div>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mt-4">
                        <button type="submit" id="submit-btn" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                            <i class="ti ti-check me-1"></i> Update
                        </button>
                        <a href="{{ route('app-settings-department') }}" class="btn btn-label-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

