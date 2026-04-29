@extends('layouts/layoutMaster')

@section('title', 'Add Purchase')


<!-- Page Scripts -->
@section('page-script')
<script>
    window.purchaseUrls = AppUtils.buildApiUrls('app/purchase');
    console.log('Purchase URLs:', window.purchaseUrls);
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
<script src="{{ asset('assets/js/purchase-add.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Add New Purchase</h5>
    </div>
    <div class="card-body">
        <form class="row g-2" id="purchase-form" onsubmit="return false">
            <div class="col-sm-3">
                <label class="form-label" for="purchase_number">Purchase Number *</label>
                <div class="input-group input-group-merge">
                    <span id="purchase_number2" class="input-group-text"><i class="ti ti-hash"></i></span>
                    <input readonly value="{{ $purchase_unique_id }}" type="text" id="purchase_number" class="form-control" name="purchase_number"
                        placeholder="Enter Purchase Number" aria-label="Enter Purchase Number"
                        aria-describedby="purchase_number2" />
                       
                </div>
            </div>
            <input type="hidden" name="serial" value="{{ $serial }}">
            <div class="col-sm-3">
                <label class="form-label" for="supplier_id">Supplier *</label>
                <select id="supplier_id" class="form-select select2" name="supplier_id">
                    <option value="">Select Supplier</option>
                    @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
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
                <label class="form-label" for="payment_method">Payment Method *</label>
                <select id="payment_method_id" class="form-select select2" name="payment_method_id">
                    <option value="">Select Payment Method</option>
                    @foreach ($payment_methods as $payment_method)
                    <option value="{{ $payment_method->id }}">{{ $payment_method->payment_method_name }}</option>
                    @endforeach

                </select>
            </div>
            <div class="col-sm-12">
                <label class="form-label" for="payment_method">Remarks</label>
                <textarea id="remarks" class="form-control" name="remarks" placeholder="Enter Remarks"></textarea>
            </div>

            <!-- Purchase Items Section -->
            <div class="col-sm-12">
                <div class="card mb-3" style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
                    <div class="card-header bg-primary text-white">Purchase Items</div>
                    <div class="card-body mt-3" id="purchase-items-container">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
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
                                            step="1" min="1" placeholder="Quantity" />
                                    </td>
                                    <td>
                                        <input type="number" class="form-control unit-price-input" name="items[0][unit_price]"
                                            step="1" min="1" placeholder="Unit Price" />
                                    </td>
                                    <td>
                                        <input type="number" class="form-control total-price-input" name="items[0][total_price]"
                                            readonly placeholder="Total Price" />
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
            <div class="col-sm-4">
                <label class="form-label" for="net_total">Net Total *</label>
                <div class="input-group input-group-merge">
                    <span id="net_total2" class="input-group-text"><i class="ti ti-currency-taka"></i></span>
                    <input type="number" id="net_total" class="form-control" name="net_total"
                        step="0.01" min="0" placeholder="0.00" aria-label="Enter Net Total"
                        aria-describedby="net_total2" readonly />
                </div>
            </div>
            <div class="col-sm-4">
                <label class="form-label" for="paid">Paid Amount *</label>
                <div class="input-group input-group-merge">
                    <span id="paid2" class="input-group-text"><i class="ti ti-currency-taka"></i></span>
                    <input type="number" id="paid" class="form-control" name="paid"
                        step="0.01" min="0" placeholder="0.00" aria-label="Enter Paid Amount"
                        aria-describedby="paid2" />
                </div>
            </div>

            <div class="col-sm-4">
                <label class="form-label" for="due">Due Amount *</label>
                <div class="input-group input-group-merge">
                    <span id="due2" class="input-group-text"><i class="ti ti-currency-taka"></i></span>
                    <input type="number" id="due" class="form-control" name="due"
                        step="0.01" min="0" placeholder="0.00" aria-label="Enter Due Amount"
                        aria-describedby="due2" />
                </div>
            </div>



            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary me-sm-4 me-1">Save Purchase</button>
                <button type="reset" class="btn btn-outline-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection