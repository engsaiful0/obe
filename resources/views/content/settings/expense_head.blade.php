@extends('layouts/layoutMaster')

@section('title', 'Expense Head Settings')
<!-- Page Scripts -->
@section('page-script')
<script>
        window.expenseHeadUrls = AppUtils.buildApiUrls('app/settings/expense-head');
        console.log('Expense Head URLs:', window.expenseHeadUrls);
    </script>
<script src="{{ asset('assets/js/expense-head-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<!-- DataTable with Buttons -->
<div class="card">
  <div class="card-datatable table-responsive pt-0">
    <table class="datatables-basic table">
      <thead>
        <tr>
          <th>Id</th>
          <th>Expense Head Name</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!-- Modal to add new record -->
<div class="offcanvas offcanvas-end" id="add-new-record">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="exampleModalLabel">New Expense Head</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-1">
    <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
      <div class="col-sm-12">
        <label class="form-label" for="name">Expense Head Name</label>
        <div class="input-group input-group-merge">
          <span id="name2" class="input-group-text"><i class="ti ti-receipt"></i></span>
          <input type="text" id="name" class="form-control dt-full-name" name="name" placeholder="Enter Expense Head Name" aria-label="Enter Expense Head Name" aria-describedby="name2" />
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
