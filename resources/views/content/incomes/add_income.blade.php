@extends('layouts/layoutMaster')

@section('title', 'Add Income')

@section('page-script')
<script>
    window.incomeUrls = {
        store: '{{ route('app-incomes.store') }}',
        index: '{{ route('app-incomes') }}'
    };
</script>
<script src="{{ asset('assets/js/income-add.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Add New Income</h5>
            <a href="{{ route('app-incomes') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <form id="income-form" method="POST" action="{{ route('app-incomes.store') }}">
                @csrf
                
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
                        <label class="form-label" for="income_head_id">Income Head <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="income_head_id2" class="input-group-text"><i class="ti ti-category"></i></span>
                            <select id="income_head_id" class="form-select @error('income_head_id') is-invalid @enderror" name="income_head_id" aria-label="Select Income Head" aria-describedby="income_head_id2" required>
                                <option value="">Select Income Head</option>
                                @foreach($incomeHeads as $incomeHead)
                                <option value="{{ $incomeHead->id }}" {{ old('income_head_id') == $incomeHead->id ? 'selected' : '' }}>
                                    {{ $incomeHead->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('income_head_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="amount2" class="input-group-text"><i class="ti ti-currency-taka"></i></span>
                            <input type="number" id="amount" class="form-control @error('amount') is-invalid @enderror" name="amount" 
                                   placeholder="Enter Amount" step="0.01" min="0" value="{{ old('amount') }}" 
                                   aria-label="Enter Amount" aria-describedby="amount2" required />
                        </div>
                        @error('amount')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="income_date">Date <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="income_date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                            <input type="date" id="income_date" class="form-control @error('income_date') is-invalid @enderror" 
                                   name="income_date" value="{{ old('income_date', date('Y-m-d')) }}" 
                                   aria-label="Select Date" aria-describedby="income_date2" required />
                        </div>
                        @error('income_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="employee_id">Concerned Employee</label>
                        <div class="input-group input-group-merge">
                            <span id="employee_id2" class="input-group-text"><i class="ti ti-user"></i></span>
                            <select id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" 
                                    name="employee_id" aria-label="Select Employee" aria-describedby="employee_id2">
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_name }} ({{ $employee->employee_unique_id }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('employee_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="remarks">Remarks</label>
                        <div class="input-group input-group-merge">
                            <span id="remarks2" class="input-group-text"><i class="ti ti-note"></i></span>
                            <textarea id="remarks" class="form-control @error('remarks') is-invalid @enderror" 
                                      name="remarks" rows="3" placeholder="Enter Remarks" 
                                      aria-label="Enter Remarks" aria-describedby="remarks2">{{ old('remarks') }}</textarea>
                        </div>
                        @error('remarks')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('app-incomes') }}" class="btn btn-label-secondary">
                                <i class="ti ti-x me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                <i class="ti ti-check me-1"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

