@extends('layouts/layoutMaster')

@section('title', 'Fee Settings')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.feeSettingsUrls = AppUtils.buildApiUrls('app/settings/fee-settings');
        console.log('Fee Settings URLs:', window.feeSettingsUrls);
    </script>
    <script src="{{ asset('assets/js/fee-settings-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Fine Type</th>
                        <th>Payment Deadline</th>
                        <th>Grace Period</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Fee Settings</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="name">Settings Name</label>
                    <div class="input-group input-group-merge">
                        <span id="name2" class="input-group-text"><i class="ti ti-tag"></i></span>
                        <input type="text" id="name" class="form-control dt-full-name" name="name"
                            placeholder="Enter Fee Settings Name" aria-label="Enter Fee Settings Name"
                            aria-describedby="name2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="amount">Fee Amount</label>
                    <div class="input-group input-group-merge">
                        <span id="amount2" class="input-group-text">৳</span>
                        <input type="number" id="amount" class="form-control dt-amount" name="amount"
                            placeholder="0.00" aria-label="Enter Fee Amount" step="0.01" min="0"
                            aria-describedby="amount2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="payment_deadline_day">Payment Deadline (Day of Month)</label>
                    <select class="form-select" id="payment_deadline_day" name="payment_deadline_day">
                        @for($day = 1; $day <= 31; $day++)
                            <option value="{{ $day }}" {{ $day == 10 ? 'selected' : '' }}>
                                {{ $day }}{{ $day == 1 ? 'st' : ($day == 2 ? 'nd' : ($day == 3 ? 'rd' : 'th')) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-sm-12">
                    <label class="form-label">Fine Type</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="fine_type" id="fineTypeFixed" value="fixed" checked>
                        <label class="form-check-label" for="fineTypeFixed">
                            Fixed Amount per Day
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="fine_type" id="fineTypePercentage" value="percentage">
                        <label class="form-check-label" for="fineTypePercentage">
                            Percentage of Fee Amount
                        </label>
                    </div>
                </div>
                <div class="col-sm-12" id="fixedFineSection">
                    <label class="form-label" for="fine_amount_per_day">Fine Amount per Day</label>
                    <div class="input-group input-group-merge">
                        <span id="fine_amount_per_day2" class="input-group-text">৳</span>
                        <input type="number" id="fine_amount_per_day" class="form-control" name="fine_amount_per_day"
                            placeholder="0.00" aria-label="Enter Fine Amount" step="0.01" min="0"
                            aria-describedby="fine_amount_per_day2" />
                    </div>
                </div>
                <div class="col-sm-12" id="percentageFineSection" style="display: none;">
                    <label class="form-label" for="fine_percentage">Fine Percentage per Day</label>
                    <div class="input-group input-group-merge">
                        <input type="number" id="fine_percentage" class="form-control" name="fine_percentage"
                            placeholder="0.00" aria-label="Enter Fine Percentage" step="0.01" min="0" max="100" />
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="maximum_fine_amount">Maximum Fine Amount (Optional)</label>
                    <div class="input-group input-group-merge">
                        <span id="maximum_fine_amount2" class="input-group-text">৳</span>
                        <input type="number" id="maximum_fine_amount" class="form-control" name="maximum_fine_amount"
                            placeholder="No limit" aria-label="Enter Maximum Fine Amount" step="0.01" min="0"
                            aria-describedby="maximum_fine_amount2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="grace_period_days">Grace Period (Days)</label>
                    <input type="number" id="grace_period_days" class="form-control" name="grace_period_days"
                        placeholder="0" aria-label="Enter Grace Period Days" min="0" max="365" value="0" />
                    <div class="form-text">No fine for this many days after deadline</div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="notes">Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                              placeholder="Any additional notes about fee settings"></textarea>
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
