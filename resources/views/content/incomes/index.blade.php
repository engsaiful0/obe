@extends('layouts/layoutMaster')

@section('title', 'Income Management')

<!-- Page Scripts -->
@section('page-script')
<script>
    window.incomeUrls = {
        destroy: '{{ url('app/incomes') }}/',
        exportExcel: '{{ route('app-incomes.export-excel') }}',
        exportPdf: '{{ route('app-incomes.export-pdf') }}'
    };
</script>
<script src="{{ asset('assets/js/income-index.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Filter Incomes</h5>
            <div>
                <a href="{{ route('app-incomes.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Add New Income
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('app-incomes') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="income_head_id">Income Head</label>
                        <select class="form-select" id="income_head_id" name="income_head_id">
                            <option value="">All Income Heads</option>
                            @foreach($incomeHeads as $incomeHead)
                            <option value="{{ $incomeHead->id }}" {{ request('income_head_id') == $incomeHead->id ? 'selected' : '' }}>
                                {{ $incomeHead->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label" for="employee_id">Concerned Employee</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->employee_name }} ({{ $employee->employee_unique_id }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                   
                    <div class="col-md-2">
                        <label class="form-label" for="date_from">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="date_to">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('app-incomes') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i> Clear Filters
                        </a>
                        <button type="button" class="btn btn-success" id="export-excel-btn">
                            <i class="ti ti-file-excel me-1"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-danger" id="export-pdf-btn">
                            <i class="ti ti-file-pdf me-1"></i> Export PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Incomes Table Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Incomes</h5>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="incomes-spinner" class="text-center d-none py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <!-- Table Container -->
            <div id="incomes-table-container">
                @include('content.incomes.partials.table', ['incomes' => $incomes])
            </div>
        </div>
    </div>
</div>
@endsection
