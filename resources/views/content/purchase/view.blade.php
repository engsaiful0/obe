@extends('layouts/layoutMaster')

@section('title', 'View Purchase')

<!-- Page Scripts -->
@section('page-script')
<script>
    window.purchaseUrls = AppUtils.buildApiUrls('app/purchase');
    console.log('Purchase URLs:', window.purchaseUrls);
</script>
<script src="{{ asset('assets/js/purchase-view.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/purchase-view.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filter Purchases</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('app-purchase-view') }}" id="filter-form">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="purchase_number">Purchase Number</label>
                    <input type="text" class="form-control" id="purchase_number" name="purchase_number"
                        value="{{ request('purchase_number') }}" placeholder="Enter purchase number">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="supplier_id">Supplier</label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }}
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
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search"></i> Filter
                        </button>
                        <a href="{{ route('app-purchase-view') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-refresh"></i> Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Purchases Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Purchase Records</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('app-purchase-print-list') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
               target="_blank" 
               class="btn btn-outline-primary">
                <i class="ti ti-printer"></i> Print List
            </a>
            <a href="{{ route('app-purchase-export-pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                class="btn btn-outline-danger">
                <i class="ti ti-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('app-purchase-add') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Add Purchase
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($purchases->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Purchase Number</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Net Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Payment Method</th>
                        <th>Items</th>
                        @if(auth()->user()->hasPermissionTo('purchase-view'))
                        <th>View</th>
                        @endif
                        <th>Print</th>
                        @if(auth()->user()->hasPermissionTo('purchase-edit'))
                        <th>Edit</th>
                        @endif
                        @if(auth()->user()->hasPermissionTo('purchase-delete'))
                        <th>Delete</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $index => $purchase)
                    <tr>
                        <td>{{ $purchases->firstItem() + $index }}</td>
                        <td>
                            <span class="fw-medium">{{ $purchase->purchase_number }}</span>
                        </td>
                        <td>{{ $purchase->supplier->supplier_name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</td>
                        <td>
                            <span class="fw-medium text-success">৳{{ number_format($purchase->net_total, 2) }}</span>
                        </td>
                        <td>
                            <span class="text-primary">৳{{ number_format($purchase->paid, 2) }}</span>
                        </td>
                        <td>
                            <span class="text-warning">৳{{ number_format($purchase->due, 2) }}</span>
                        </td>
                        <td>
                            {{ $purchase->paymentMethod->payment_method_name ?? 'N/A' }}
                        </td>
                        <td>
                            <span class="badge bg-label-info">{{ $purchase->purchaseItems->count() }} items</span>
                        </td>
                        @if(auth()->user()->hasPermissionTo('purchase-view'))
                        <td>
                            <a href="{{ route('app-purchase-view-details', $purchase->id) }}" data-purchase-id="{{ $purchase->id }}" data-action="view">
                                <i class="ti ti-eye me-1"></i>
                            </a>
                        </td>
                        @endif
                        <td>
                            <a href="{{ route('app-purchase-print', $purchase->id) }}" target="_blank" class="text-primary" data-purchase-id="{{ $purchase->id }}" data-action="print" title="Print Invoice">
                                <i class="ti ti-printer me-1"></i>
                            </a>
                        </td>
                        @if(auth()->user()->hasPermissionTo('purchase-edit'))
                        <td>
                            <a href="{{ route('app-purchase-edit', $purchase->id) }}" data-purchase-id="{{ $purchase->id }}" data-action="edit">
                                <i class="ti ti-pencil me-1"></i>
                            </a>
                        </td>
                        @endif
                        @if(auth()->user()->hasPermissionTo('purchase-delete'))
                        <td>
                            <a class=" text-danger" href="javascript:void(0);" data-purchase-id="{{ $purchase->id }}" data-action="delete">
                                <i class="ti ti-trash me-1"></i>
                            </a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Showing {{ $purchases->firstItem() }} to {{ $purchases->lastItem() }} of {{ $purchases->total() }} results
            </div>
            <div>
                {{ $purchases->appends(request()->query())->links() }}
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="ti ti-shopping-cart" style="font-size: 3rem; color: #ccc;"></i>
            </div>
            <h5 class="text-muted">No purchases found</h5>
            <p class="text-muted">Start by adding your first purchase.</p>
            <a href="{{ route('app-purchase-add') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Add Purchase
            </a>
        </div>
        @endif
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
                <select id="payment_method" class="form-select" name="payment_method">
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