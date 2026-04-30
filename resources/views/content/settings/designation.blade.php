@extends('layouts/layoutMaster')

@section('title', 'Designation Settings')


@section('page-script')
    <script>
        window.designationUrls = AppUtils.buildApiUrls('app/settings/designation');
    </script>
    <script src="{{ asset('assets/js/designation-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Designation Name</th>
                        <th>Designation Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Designation</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="designation_name">Designation Name</label>
                    <div class="input-group input-group-merge">
                        <span id="designation_name2" class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" id="designation_name" class="form-control dt-full-name"
                            name="designation_name" placeholder="Enter designation Name"
                            aria-label="Enter designation Name" aria-describedby="designation_name2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="designation_type">Designation Type</label>
                    <div class="input-group input-group-merge">
                        <span id="designation_type2" class="input-group-text"><i class="ti ti-briefcase"></i></span>
                        <select id="designation_type" name="designation_type" class="form-select dt-designation-type">
                            <option value="">Select Type</option>
                            <option value="Teacher">Teacher</option>
                            <option value="Employee">Employee</option>
                            <option value="Management">Management</option>
                        </select>
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
