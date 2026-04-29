@extends('layouts/layoutMaster')

@section('title', 'View Income')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Income Details - ID: {{ $income->id }}</h5>
            <div>
                <a href="{{ route('app-incomes.edit', $income->id) }}" class="btn btn-primary me-2">
                    <i class="ti ti-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('app-incomes') }}" class="btn btn-label-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Income Head</label>
                        <p class="mb-0">{{ $income->incomeHead->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Amount</label>
                        <p class="mb-0">৳{{ number_format($income->amount, 2) }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Date</label>
                        <p class="mb-0">{{ $income->income_date ? $income->income_date->format('F d, Y') : 'N/A' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Concerned Employee</label>
                        <p class="mb-0">
                            @if($income->employee)
                                {{ $income->employee->employee_name }} ({{ $income->employee->employee_unique_id }})
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Remarks</label>
                        <p class="mb-0">{{ $income->remarks ?? '<span class="text-muted">-</span>' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Created At</label>
                        <p class="mb-0">{{ $income->created_at ? $income->created_at->format('F d, Y h:i A') : 'N/A' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Updated At</label>
                        <p class="mb-0">{{ $income->updated_at ? $income->updated_at->format('F d, Y h:i A') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

