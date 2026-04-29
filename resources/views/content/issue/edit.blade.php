@extends('layouts/layoutMaster')

@section('title', 'Edit Issue')

<!-- Page Scripts -->
@section('page-script')
<script>
    window.issueUrls = {
        update: '{{ route('app-issue.update', $issue->id) }}',
        view: '{{ route('app-issue-view') }}',
        productRow: '{{ route('app-issue-product-row') }}'
    };
    console.log('Issue URLs:', window.issueUrls);
</script>
<style>
    /* Disable form inputs during submission */
    .form-loading input,
    .form-loading select,
    .form-loading textarea,
    .form-loading button:not([type="submit"]) {
        pointer-events: none;
        opacity: 0.6;
    }
</style>
<script src="{{ asset('assets/js/issue-edit.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Edit Issue</h5>
    </div>
    <div class="card-body">
        <form class="row g-2" id="issue-form" onsubmit="return false">
            <input type="hidden" name="issue_id" value="{{ $issue->id }}">
            
            <div class="col-sm-3">
                <label class="form-label" for="issue_number">Issue Number *</label>
                <div class="input-group input-group-merge">
                    <span id="issue_number2" class="input-group-text"><i class="ti ti-hash"></i></span>
                    <input readonly value="{{ $issue->issue_number }}" type="text" id="issue_number" class="form-control" name="issue_number"
                        placeholder="Enter Issue Number" aria-label="Enter Issue Number"
                        aria-describedby="issue_number2" />
                </div>
            </div>
            
            <div class="col-sm-3">
                <label class="form-label" for="employee_id">Employee *</label>
                <select id="employee_id" class="form-select select2" name="employee_id">
                    <option value="">Select Employee</option>
                    @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $employee->id == $issue->employee_id ? 'selected' : '' }}>
                        {{ $employee->employee_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-3">
                <label class="form-label" for="date">Date *</label>
                <div class="input-group input-group-merge">
                    <span id="date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                    <input value="{{ $issue->date->format('Y-m-d') }}" type="date" id="date" class="form-control" name="date"
                        aria-label="Enter Date" aria-describedby="date2" />
                </div>
            </div>

            <div class="col-sm-3">
                <label class="form-label" for="remarks">Remarks</label>
                <input type="text" id="remarks" class="form-control" name="remarks" 
                    value="{{ $issue->remarks }}" placeholder="Enter Remarks" />
            </div>

            <!-- Issue Items Section -->
            <div class="col-sm-12">
                <div class="card mb-3" style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
                    <div class="card-header bg-primary text-white">Issue Items</div>
                    <div class="card-body mt-3" id="issue-items-container">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-item-btn">
                                            <i class="ti ti-plus"></i> Add Item
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($issue->issueItems as $index => $issueItem)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <select class="form-select item-select select2" name="items[{{ $index }}][item_id]">
                                            <option value="">Select Item</option>
                                            @foreach ($items as $item)
                                            <option value="{{ $item->id }}" {{ $item->id == $issueItem->item_id ? 'selected' : '' }}>
                                                {{ $item->item_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select unit-select select2" name="items[{{ $index }}][unit_id]">
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}" {{ $unit->id == $issueItem->unit_id ? 'selected' : '' }}>
                                                {{ $unit->unit_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control quantity-input" name="items[{{ $index }}][quantity]"
                                            step="0.01" min="0.01" value="{{ $issueItem->quantity }}" placeholder="Quantity" />
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary me-sm-4 me-1">Update Issue</button>
                <button type="reset" class="btn btn-outline-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection
