@extends('layouts/layoutMaster')

@section('title', 'Edit Damage')

@section('page-script')
<script>
    window.damageUrls = {
        update: '{{ route('app-damage.update', $damage->id) }}',
        view: '{{ route('app-damage-view') }}',
        productRow: '{{ route('app-damage-product-row') }}'
    };
</script>
<style>
    .form-loading {
        pointer-events: none;
        opacity: 0.7;
    }
</style>
<script src="{{ asset('assets/js/damage-edit.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Edit Damage</h5>
    </div>
    <div class="card-body">
        <form class="row g-2" id="damage-form" onsubmit="return false">
            <input type="hidden" name="damage_id" value="{{ $damage->id }}">
            
            <div class="col-md-4">
                <label class="form-label" for="warehouse_id">Warehouse *</label>
                <select id="warehouse_id" class="form-select select2" name="warehouse_id">
                    <option value="">Select Warehouse</option>
                    @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ $warehouse->id == $damage->warehouse_id ? 'selected' : '' }}>
                        {{ $warehouse->warehouse_name }} ({{ $warehouse->warehouse_number }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="date">Date *</label>
                <div class="input-group input-group-merge">
                    <span id="date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                    <input value="{{ $damage->date->format('Y-m-d') }}" type="date" id="date" class="form-control" name="date"
                        aria-label="Enter Date" aria-describedby="date2" />
                </div>
            </div>

            <div class="col-md-5">
                <label class="form-label" for="remarks">Remarks</label>
                <input type="text" id="remarks" class="form-control" name="remarks" 
                    value="{{ $damage->remarks }}" placeholder="Enter Remarks" />
            </div>

            <!-- Damage Items Section -->
            <div class="col-sm-12">
                <div class="card mb-3" style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
                    <div class="card-header bg-primary text-white">Damage Items</div>
                    <div class="card-body mt-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Reason</th>
                                        <th>Approximate</th>
                                        <th>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-item-btn">
                                                <i class="ti ti-plus"></i> Add Item
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="damage-items-container">
                                    @foreach($damage->damageItems as $index => $damageItem)
                                    <tr class="damage-item-row">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <select class="form-select item-select select2" name="items[{{ $index }}][item_id]">
                                                <option value="">Select Item</option>
                                                @foreach ($items as $item)
                                                <option value="{{ $item->id }}" {{ $item->id == $damageItem->item_id ? 'selected' : '' }}>
                                                    {{ $item->item_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control quantity-input" name="items[{{ $index }}][quantity]"
                                                step="0.01" min="0.01" value="{{ $damageItem->quantity }}" placeholder="Quantity" />
                                        </td>
                                        <td>
                                            <input type="text" class="form-control reason-input" name="items[{{ $index }}][reason]"
                                                value="{{ $damageItem->reason }}" placeholder="Reason (optional)" />
                                        </td>
                                        <td>
                                            <input type="number" class="form-control approximate-input" name="items[{{ $index }}][approximate]"
                                                step="0.01" min="0" value="{{ $damageItem->approximate }}" placeholder="0.00" />
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
            </div>

            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary me-sm-4 me-1">Update Damage</button>
                <a href="{{ route('app-damage-view') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

