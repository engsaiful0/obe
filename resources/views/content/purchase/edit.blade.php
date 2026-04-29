@extends('layouts/layoutMaster')

@section('title', 'Edit Purchase')

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
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Edit Purchase</h5>
                            <p class="mb-4">Update purchase information and items.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Purchase - {{ $purchase->purchase_number }}</h5>
                </div>
                <div class="card-body">
                    <form id="purchase-form" class="row g-3" data-purchase-id="{{ $purchase->id }}">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                        <input type="hidden" name="serial" value="{{ $serial }}">

                        <div class="col-sm-3">
                            <label class="form-label" for="purchase_number">Purchase Number *</label>
                            <div class="input-group input-group-merge">
                                <span id="purchase_number2" class="input-group-text"><i class="ti ti-hash"></i></span>
                                <input readonly value="{{ $purchase->purchase_number }}" type="text" id="purchase_number" class="form-control" name="purchase_number"
                                    placeholder="Enter Purchase Number" aria-label="Enter Purchase Number"
                                    aria-describedby="purchase_number2" />
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <label class="form-label" for="supplier_id">Supplier *</label>
                            <select id="supplier_id" class="form-select select2" name="supplier_id">
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-3">
                            <label class="form-label" for="date">Date *</label>
                            <div class="input-group input-group-merge">
                                <span id="date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                                <input value="{{ $purchase->date->format('Y-m-d') }}" type="date" id="date" class="form-control" name="date"
                                    aria-label="Enter Date" aria-describedby="date2" />
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <label class="form-label" for="payment_method">Payment Method *</label>
                            <select id="payment_method_id" class="form-select select2" name="payment_method_id">
                                <option value="">Select Payment Method</option>
                                @foreach ($payment_methods as $payment_method)
                                <option value="{{ $payment_method->id }}" {{ $purchase->payment_method_id == $payment_method->id ? 'selected' : '' }}>{{ $payment_method->payment_method_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-12">
                            <label class="form-label" for="payment_method">Remarks</label>
                            <textarea id="remarks" class="form-control" name="remarks" placeholder="Enter Remarks">{{ $purchase->remarks }}</textarea>
                        </div>

                        <!-- Purchase Items Section -->
                        <div class="col-sm-12">
                            <h6 class="mb-3">Purchase Items</h6>
                            <div id="purchase-items-container">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
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
                                        @foreach($purchase->purchaseItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <select class="form-select item-select select2" name="items[{{ $index }}][item_id]">
                                                    <option value="">Select Item</option>
                                                    @foreach ($items as $itemOption)
                                                    <option value="{{ $itemOption->id }}" {{ $item->item_id == $itemOption->id ? 'selected' : '' }}>{{ $itemOption->item_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select unit-select select2" name="items[{{ $index }}][unit_id]">
                                                    <option value="">Select Unit</option>
                                                    @foreach ($units as $unit)
                                                    <option value="{{ $unit->id }}" {{ $item->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control quantity-input" name="items[{{ $index }}][quantity]"
                                                    value="{{ $item->quantity }}" step="0.01" min="0.01" placeholder="0.00" />
                                            </td>
                                            <td>
                                                <input type="number" class="form-control unit-price-input" name="items[{{ $index }}][unit_price]"
                                                    value="{{ $item->unit_price }}" step="0.01" min="0" placeholder="0.00" />
                                            </td>
                                            <td>
                                                <input type="number" class="form-control total-price-input" name="items[{{ $index }}][total_price]"
                                                    value="{{ $item->total_price }}" readonly placeholder="0.00" />
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

                        <div class="col-sm-4">
                            <label class="form-label" for="paid">Paid Amount *</label>
                            <div class="input-group input-group-merge">
                                <span id="paid2" class="input-group-text"><i class="ti ti-currency-taka"></i></span>
                                <input type="number" id="paid" class="form-control" name="paid" value="{{ $purchase->paid }}"
                                    step="0.01" min="0" placeholder="0.00" aria-label="Enter Paid Amount"
                                    aria-describedby="paid2" />
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="due">Due Amount *</label>
                            <div class="input-group input-group-merge">
                                <span id="due2" class="input-group-text"><i class="ti ti-currency-taka"></i></span>
                                <input type="number" id="due" class="form-control" name="due" value="{{ $purchase->due }}"
                                    step="0.01" min="0" placeholder="0.00" aria-label="Enter Due Amount"
                                    aria-describedby="due2" />
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="net_total">Net Total *</label>
                            <div class="input-group input-group-merge">
                                <span id="net_total2" class="input-group-text"><i class="ti ti-currency-taka"></i></span>
                                <input type="number" id="net_total" class="form-control" name="net_total" value="{{ $purchase->net_total }}"
                                    step="0.01" min="0" placeholder="0.00" aria-label="Enter Net Total"
                                    aria-describedby="net_total2" readonly />
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary me-sm-4 me-1">Update Purchase</button>
                            <a href="{{ route('app-purchase-view') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection