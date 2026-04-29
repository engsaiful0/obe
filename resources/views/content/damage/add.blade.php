@extends('layouts/layoutMaster')

@section('title', 'Add Damage')

@section('page-script')
<script type="application/json" id="damage-urls">@json(['store' => route('app-damage.store'), 'productRow' => route('app-damage-product-row'), 'view' => route('app-damage-view')])</script>
<style>
    .form-loading {
        pointer-events: none;
        opacity: 0.7;
    }
</style>
<script src="{{ asset('assets/js/damage-add.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="card">
  <div class="card-header"><h5 class="mb-0">Damage Entry</h5></div>
  <div class="card-body">
    <form id="damage-form">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label" for="warehouse_id">Warehouse</label>
          <select class="form-select" id="warehouse_id" name="warehouse_id">
            <option value="">Select Warehouse</option>
            @foreach ($warehouses as $w)
              <option value="{{ $w->id }}">{{ $w->warehouse_name }} ({{ $w->warehouse_number }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="date">Date</label>
          <input type="date" class="form-control" id="date" name="date" value="{{ $date }}">
        </div>
        <div class="col-md-5">
          <label class="form-label" for="remarks">Remarks</label>
          <input type="text" class="form-control" id="remarks" name="remarks" placeholder="Optional remarks">
        </div>
      </div>

      <hr>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Damage Items</h6>
        <button type="button" class="btn btn-sm btn-primary" id="add-row-btn"><i class="ti ti-plus"></i> Add Row</button>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered" id="damage-items-table">
          <thead>
            <tr>
              <th style="width: 50px">#</th>
              <th>Item</th>
              <th style="width: 140px">Quantity</th>
              <th>Reason</th>
              <th style="width: 140px">Approximate</th>
              <th style="width: 90px">Action</th>
            </tr>
          </thead>
          <tbody id="damage-items-container">
            @include('content.damage.product_row', ['items' => $items, 'rowIndex' => 0])
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary" id="save-damage-btn">Save Damage</button>
        <a href="javascript:history.back()" class="btn btn-label-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection


