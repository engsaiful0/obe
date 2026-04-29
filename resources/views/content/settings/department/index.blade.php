@extends('layouts/layoutMaster')

@section('title', 'Department Settings')

@section('page-script')
<script>
    window.departmentUrls = {
        destroy: '{{ url('app/settings/department') }}/',
        index: '{{ route('app-settings-department') }}'
    };
</script>
<script src="{{ asset('assets/js/department-index.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Departments</h5>
            <a href="{{ route('app-settings-department.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add New Department
            </a>
        </div>
        <div class="card-body">
            <div id="departments-spinner" class="text-center d-none py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div id="departments-table-container">
                @include('content.settings.department.partials.table', ['departments' => $departments])
            </div>
        </div>
    </div>
</div>
@endsection

