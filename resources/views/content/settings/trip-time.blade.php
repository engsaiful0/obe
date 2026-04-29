@extends('layouts/layoutMaster')

@section('title', 'Trip Time Settings')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.tripTimeUrls = AppUtils.buildApiUrls('app/settings/trip-time');
        console.log('Trip Time URLs:', window.tripTimeUrls);
    </script>
    <script src="{{ asset('assets/js/trip-time-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Trip Name</th>
                        <th>Time</th>
                        <th>Period</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Trip Time</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="time_name">Trip Name</label>
                    <div class="input-group input-group-merge">
                        <span id="time_name2" class="input-group-text"><i class="ti ti-clock"></i></span>
                        <input type="text" id="time_name" class="form-control dt-full-name" name="time_name"
                            placeholder="Enter Trip Name (e.g., Morning Trip)" aria-label="Enter Trip Name"
                            aria-describedby="time_name2" />
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="time_value">Time</label>
                    <div class="input-group input-group-merge">
                        <span id="time_value2" class="input-group-text"><i class="ti ti-clock"></i></span>
                        <input type="time" id="time_value" class="form-control" name="time_value"
                            placeholder="Select Time" aria-label="Select Time"
                            aria-describedby="time_value2" />
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="time_period">Period</label>
                    <div class="input-group input-group-merge">
                        <span id="time_period2" class="input-group-text"><i class="ti ti-sun"></i></span>
                        <select id="time_period" class="form-select" name="time_period" aria-label="Select Period" aria-describedby="time_period2">
                            <option value="">Select Period</option>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="description">Description</label>
                    <div class="input-group input-group-merge">
                        <span id="description2" class="input-group-text"><i class="ti ti-file-text"></i></span>
                        <textarea id="description" class="form-control" name="description" rows="3"
                            placeholder="Enter description (optional)" aria-label="Enter Description"
                            aria-describedby="description2"></textarea>
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
