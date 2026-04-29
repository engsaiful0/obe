@extends('layouts/layoutMaster')

@section('title', 'Add Issue')

<!-- Page Scripts -->
@section('page-script')
<script>
    window.issueUrls = {
        store: '{{ route('app-issue.store') }}',
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
    
    /* Disabled remove button styles */
    .remove-item-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: #fff !important;
    }
    
    .remove-item-btn.disabled:hover {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: #fff !important;
    }
    
    /* Duplicate item styling */
    .item-select.is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    /* Total quantity section styling */
    #total-quantity-display {
        font-weight: bold;
        text-align: center;
    }
    
    /* Individual quantity display styling */
    .quantity-display {
        font-size: 0.9em;
        min-width: 50px;
        text-align: center;
        display: inline-block;
    }
    
    .quantity-display.badge {
        padding: 0.5em 0.75em;
    }
</style>
<script src="{{ asset('assets/js/issue-add.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Add New Issue</h5>
    </div>
    <div class="card-body">
        <form class="row g-2" id="issue-form" onsubmit="return false">
            <div class="col-sm-3">
                <label class="form-label" for="issue_number">Issue Number *</label>
                <div class="input-group input-group-merge">
                    <span id="issue_number2" class="input-group-text"><i class="ti ti-hash"></i></span>
                    <input readonly value="{{ $issue_unique_id }}" type="text" id="issue_number" class="form-control" name="issue_number"
                        placeholder="Enter Issue Number" aria-label="Enter Issue Number"
                        aria-describedby="issue_number2" />
                </div>
            </div>
            <input type="hidden" name="serial" value="{{ $serial }}">
            
            <div class="col-sm-3">
                <label class="form-label" for="employee_id">Employee *</label>
                <select id="employee_id" class="form-select select2" name="employee_id">
                    <option value="">Select Employee</option>
                    @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->employee_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-3">
                <label class="form-label" for="date">Date *</label>
                <div class="input-group input-group-merge">
                    <span id="date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                    <input value="{{ date('Y-m-d') }}" type="date" id="date" class="form-control" name="date"
                        aria-label="Enter Date" aria-describedby="date2" />
                </div>
            </div>

            <div class="col-sm-3">
                <label class="form-label" for="remarks">Remarks</label>
                <input type="text" id="remarks" class="form-control" name="remarks" placeholder="Enter Remarks" />
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
                                    <th>Display Qty</th>
                                    <th>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-item-btn">
                                            <i class="ti ti-plus"></i> Add Item
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <select class="form-select item-select select2" name="items[0][item_id]">
                                            <option value="">Select Item</option>
                                            @foreach ($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select unit-select select2" name="items[0][unit_id]">
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control quantity-input" name="items[0][quantity]"
                                            step="0.01" min="0.01" placeholder="Quantity" />
                                    </td>
                                    <td>
                                        <span class="quantity-display badge bg-info">0.00</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                  
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary me-sm-4 me-1">Save Issue</button>
                <button type="reset" class="btn btn-outline-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection
