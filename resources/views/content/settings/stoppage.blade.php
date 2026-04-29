@extends('layouts/layoutMaster')

@section('title', 'Stoppage Settings')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.stoppageUrls = AppUtils.buildApiUrls('app/settings/stoppage');
        console.log('Stoppage URLs:', window.stoppageUrls);
    </script>
    <script src="{{ asset('assets/js/stoppage-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Stoppage Name</th>
                        <th>Distance (KM)</th>
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
            <h5 class="offcanvas-title" id="exampleModalLabel">New Stoppage</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="stoppage_name">Stoppage Name</label>
                    <div class="input-group input-group-merge">
                        <span id="stoppage_name2" class="input-group-text"><i class="ti ti-map-pin"></i></span>
                        <input type="text" id="stoppage_name" class="form-control dt-full-name" name="stoppage_name"
                            placeholder="Enter Stoppage Name" aria-label="Enter Stoppage Name"
                            aria-describedby="stoppage_name2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="distance">Distance (KM)</label>
                    <div class="input-group input-group-merge">
                        <span id="distance2" class="input-group-text"><i class="ti ti-ruler"></i></span>
                        <input type="number" id="distance" class="form-control dt-distance" name="distance"
                            placeholder="Enter Distance in KM" aria-label="Enter Distance in KM"
                            aria-describedby="distance2" step="0.01" min="0" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" class="form-select dt-status" name="status" aria-label="Select Status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
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
