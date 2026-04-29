@extends('layouts/layoutMaster')

@section('title', 'Purchase Management')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.purchaseUrls = AppUtils.buildApiUrls('app/purchase');
        console.log('Purchase URLs:', window.purchaseUrls);
    </script>
    <script src="{{ asset('assets/js/purchase-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Purchase Number</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Net Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Payment Method</th>
                        
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Purchase</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-6">
                    <label class="form-label" for="purchase_number">Purchase Number *</label>
                    <div class="input-group input-group-merge">
                        <span id="purchase_number2" class="input-group-text"><i class="ti ti-hash"></i></span>
                        <input type="text" id="purchase_number" class="form-control dt-full-name" name="purchase_number"
                            placeholder="Enter Purchase Number" aria-label="Enter Purchase Number"
                            aria-describedby="purchase_number2" />
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="supplier_id">Supplier *</label>
                    <select id="supplier_id" class="form-select" name="supplier_id">
                        <option value="">Select Supplier</option>
                    </select>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="date">Date *</label>
                    <div class="input-group input-group-merge">
                        <span id="date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="date" id="date" class="form-control" name="date"
                            aria-label="Enter Date" aria-describedby="date2" />
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="payment_method">Payment Method *</label>
                    <select id="payment_method" class="form-select" name="payment_method_id">
                        <option value="">Select Payment Method</option>
                        @foreach ($payment_methods as $payment_method)
                        <option value="{{ $payment_method->id }}">{{ $payment_method->payment_method_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Purchase Items Section -->
                <div class="col-sm-12">
                    <h6 class="mb-3">Purchase Items</h6>
                    <div id="purchase-items-container">
                        <div class="purchase-item-row row g-2 mb-3">
                            <div class="col-sm-4">
                                <label class="form-label">Item *</label>
                                <select class="form-select item-select" name="items[0][item_id]">
                                    <option value="">Select Item</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label">Quantity *</label>
                                <input type="number" class="form-control quantity-input" name="items[0][quantity]" 
                                    step="0.01" min="0.01" placeholder="0.00" />
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label">Unit Price *</label>
                                <input type="number" class="form-control unit-price-input" name="items[0][unit_price]" 
                                    step="0.01" min="0" placeholder="0.00" />
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label">Total Price</label>
                                <input type="number" class="form-control total-price-input" name="items[0][total_price]" 
                                    readonly placeholder="0.00" />
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-item-btn">
                        <i class="ti ti-plus"></i> Add Item
                    </button>
                </div>
                
                <div class="col-sm-4">
                    <label class="form-label" for="paid">Paid Amount *</label>
                    <div class="input-group input-group-merge">
                        <span id="paid2" class="input-group-text"><i class="ti ti-currency-dollar"></i></span>
                        <input type="number" id="paid" class="form-control" name="paid"
                            step="0.01" min="0" placeholder="0.00" aria-label="Enter Paid Amount"
                            aria-describedby="paid2" />
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <label class="form-label" for="due">Due Amount *</label>
                    <div class="input-group input-group-merge">
                        <span id="due2" class="input-group-text"><i class="ti ti-currency-dollar"></i></span>
                        <input type="number" id="due" class="form-control" name="due"
                            step="0.01" min="0" placeholder="0.00" aria-label="Enter Due Amount"
                            aria-describedby="due2" />
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <label class="form-label" for="net_total">Net Total *</label>
                    <div class="input-group input-group-merge">
                        <span id="net_total2" class="input-group-text"><i class="ti ti-currency-dollar"></i></span>
                        <input type="number" id="net_total" class="form-control" name="net_total"
                            step="0.01" min="0" placeholder="0.00" aria-label="Enter Net Total"
                            aria-describedby="net_total2" readonly />
                    </div>
                </div>
                
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!--/ DataTable with Buttons -->
@endsection
