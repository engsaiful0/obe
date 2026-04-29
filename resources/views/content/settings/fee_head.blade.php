@extends('layouts/layoutMaster')

@section('title', 'Fee Head Settings')

{{-- Page Script --}}
@section('page-script')
    <script>
        window.feeHeadUrls = AppUtils.buildApiUrls('app/settings/fee-head');
        window.semesterUrls = AppUtils.buildApiUrls('app/settings/semester');
        window.monthUrls = AppUtils.buildApiUrls('app/settings/month');
        console.log('Fee Head URLs:', window.feeHeadUrls);
        console.log('Semester URLs:', window.semesterUrls);
    </script>
    <script src="{{ asset('assets/js/fee-head-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <div class="card-header flex-column flex-md-row">
                <div class="dt-action-buttons text-end pt-3 pt-md-0">
                    <div class="dt-buttons btn-group flex-wrap">
                        <button class="btn btn-primary create-new waves-effect waves-light" tabindex="0"
                            aria-controls="DataTables_Table_0" type="button">
                            <span><i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New
                                    Record</span></span>
                        </button>
                    </div>
                </div>
            </div>
            <table class="datatables-fee-heads table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fee Head</th>
                        <th>Fee Type</th>
                        <th>Month</th>
                        <th>Amount</th>
                        <th>Discountable</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="offcanvasAddFeeHead">
        <div class="offcanvas-header">
            <h5 id="offcanvasAddFeeHeadLabel" class="offcanvas-title">Add Fee Head</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0">
            <form class="add-new-fee-head pt-0" id="addNewFeeHeadForm">
                <div class="mb-3">
                    <label class="form-label" for="name">Fee Head Name</label>
                    <input type="text" class="form-control" id="name" placeholder="Enter Fee Head Name"
                        name="name" aria-label="Fee Head Name" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="fee_type">Fee Type</label>
                    <select class="form-control" id="fee_type" name="fee_type" aria-label="Fee Type">
                        <option value="Regular">Regular</option>
                        <option value="Monthly">Monthly</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="month_id">Month</label>
                    <select id="month_id" name="month_id" class="form-select month-select">
                        <option value="" disabled selected>Select a month</option> <!-- Optional fallback -->
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="semester_id">Semester</label>
                    <select id="semester_id" name="semester_id" class="form-select semester-select"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="amount">Amount</label>
                    <input type="text" class="form-control" id="amount" placeholder="Enter Amount" name="amount"
                        aria-label="Amount" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="is_discountable">Discountable</label>
                    <select id="is_discountable" name="is_discountable" class="form-select">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </form>
        </div>
    </div>
    </div>


@endsection
