@extends('layouts/layoutMaster')

@section('title', 'Expense Management')

<!-- Page Scripts -->
@section('page-script')
<script>
    $(document).ready(function() {
        // Bus filtering is handled in expense-index.js
    });
</script>
<script>
    window.expenseUrls = {
        store: '{{ route('expenses.store') }}',
        update: '{{ url('app/expenses') }}/:id',
        destroy: '{{ url('app/expenses') }}/:id',
        exportExcel: '{{ route('expenses.export-excel') }}',
        exportPdf: '{{ route('expenses.export-pdf') }}',
        getExpenseHeads: '{{ route('app-settings-get-expense-head') }}',
        getBusTypes: '{{ route('app-settings-get-bus-type') }}',
        getBusSubTypes: '{{ route('app-settings-get-bus-sub-type') }}',
        getBuses: '{{ route('buses.get-buses-names-by-type-and-subtype') }}',
        getBusesBySubType: '{{ route('buses.get-buses-by-subtype') }}',
        getEmployees: '{{ route('employees.get-data') }}',
        getBusHelpers: '{{ route('bus-helpers.get-data') }}'
    };
    console.log('Expense URLs:', window.expenseUrls);
</script>
<script src="{{ asset('assets/js/expense-index.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filter Expenses</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('expenses.index') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label" for="expense_head_id">Expense Head</label>
                        <select class="form-select" id="expense_head_id" name="expense_head_id">
                            <option value="">All Expense Heads</option>
                            @foreach($expenseHeads as $expenseHead)
                            <option value="{{ $expenseHead->id }}" {{ request('expense_head_id') == $expenseHead->id ? 'selected' : '' }}>
                                {{ $expenseHead->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="supplier_id">Supplier</label>
                        <select class="form-select" id="supplier_id" name="supplier_id">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
                            @endforeach
                        </select>
                    </div>
                 
                    <div class="col-md-2">
                        <label class="form-label" for="bus_sub_type_id">Bus Sub Type</label>
                        <select class="form-select" id="bus_sub_type_id" name="bus_sub_type_id">
                            <option value="">All Bus Sub Types</option>
                            @foreach($busSubTypes as $busSubType)
                            <option value="{{ $busSubType->id }}" {{ request('bus_sub_type_id') == $busSubType->id ? 'selected' : '' }}>{{ $busSubType->sub_type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="bus_id">Bus</label>
                        <select class="form-select" id="bus_id" name="bus_id">
                            <option value="">All Buses</option>
                            @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>{{ $bus->bus_number ?? ($bus->model_name . ' (' . $bus->registration_number . ')') }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label" for="employee_id">Employee</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                             @foreach($employees as $employee)
                             <option value="{{ $employee->id }}">{{ $employee->employee_name }}</option>
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
                               value="{{ request('search') }}" placeholder="Search remarks, amount...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="per_page">Per Page</label>
                        <select class="form-select" id="per_page" name="per_page">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ti ti-search me-1"></i> Filter
                        </button>
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="clearFilters()">
                            <i class="ti ti-refresh me-1"></i> Clear
                        </button>
                        <button type="button" class="btn btn-success me-2" onclick="exportToExcel()">
                            <i class="ti ti-file-excel me-1"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="exportToPdf()">
                            <i class="ti ti-file-pdf me-1"></i> Export PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card position-relative">
        <!-- Spinner Overlay -->
        <div id="expenses-spinner" class="d-none position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: rgba(255, 255, 255, 0.8); z-index: 1000;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">
                    <strong>Loading Expenses...</strong>
                </div>
            </div>
        </div>
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Expenses List</h5>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add New Expense
            </a>
        </div>
        <div class="card-body" id="expenses-table-container">
            @include('content.expenses.partials.table')
        </div>
    </div>
</div>

@endsection