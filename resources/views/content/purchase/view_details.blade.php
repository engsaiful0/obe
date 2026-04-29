@extends('layouts/layoutMaster')

@section('title', 'Purchase Details - ' . $purchase->purchase_number)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Purchase Details - {{ $purchase->purchase_number }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('app-purchase-print', $purchase->id) }}" class="btn btn-primary">
                <i class="ti ti-printer me-1"></i> Print Invoice
            </a>
            <a href="{{ route('app-purchase-edit', $purchase->id) }}" class="btn btn-warning">
                <i class="ti ti-pencil me-1"></i> Edit Purchase
            </a>
            <a href="{{ route('app-purchase-view') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to Purchases
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Purchase Information Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Purchase Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Purchase Number:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $purchase->purchase_number }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Date:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Payment Method:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $purchase->paymentMethod->payment_method_name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Net Total:</strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="fw-bold text-success">৳{{ number_format($purchase->net_total, 2) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Paid Amount:</strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="text-primary">৳{{ number_format($purchase->paid, 2) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Due Amount:</strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="text-warning">৳{{ number_format($purchase->due, 2) }}</span>
                        </div>
                    </div>
                    @if($purchase->remarks)
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Remarks:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $purchase->remarks }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Supplier Information Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Supplier Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Name:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $purchase->supplier->supplier_name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Mobile:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $purchase->supplier->mobile ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Email:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $purchase->supplier->email ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Address:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $purchase->supplier->address ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Items Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Purchase Items ({{ $purchase->purchaseItems->count() }} items)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->purchaseItems as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->item->item_name ?? 'N/A' }}</td>
                                    <td>{{ $item->unit->unit_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($item->quantity, 2) }}</td>
                                    <td>৳{{ number_format($item->unit_price, 2) }}</td>
                                    <td><strong>৳{{ number_format($item->total_price, 2) }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="5" class="text-end">Net Total:</th>
                                    <th class="text-success">৳{{ number_format($purchase->net_total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="{{ route('app-purchase-print', $purchase->id) }}" class="btn btn-primary">
                    <i class="ti ti-printer me-1"></i> Print Invoice
                </a>
                <a href="{{ route('app-purchase-edit', $purchase->id) }}" class="btn btn-warning">
                    <i class="ti ti-pencil me-1"></i> Edit Purchase
                </a>
                <a href="{{ route('app-purchase-view') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Purchases
                </a>
            </div>
        </div>
    </div>
@endsection
